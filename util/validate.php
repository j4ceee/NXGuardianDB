<?php

use JetBrains\PhpStorm\NoReturn;


/*
 * ERROR MESSAGES
 */


$errorDict = [
    // missing inputs errors
    "400" => "Error! Inputs were not submitted correctly.",
    "404" => "Error! Missing required field: ",
    "405" => "Error! No platforms selected.",
    "406" => "Error! No multiplayer modes selected for a platform.",
    "407" => "Error! Missing player count for one of the selected multiplayer modes.",

    // login errors
    "300" => "Error! Invalid ",
    "301" => "Error! Missing ",
    "302" => "Error! Too long: ",
    "303" => "Error! Too short: ",
    "333" => "Error! Login failed. Please check your credentials and try again.",
    "334" => "Error! You need to be logged in to access this page.",

    // input format errors
    "500" => "Error! Invalid input format for field: ",
    "501" => "Error! Input too long for field: ",
    "502" => "Error! Game ID already exists for the selected platform: ",
    "503" => "Error! Game already exists in the database: ",
    "504" => "Error! The image link is not a valid image file.",
    "505" => "Error! The image is not square."
];

function getErrorMsg(bool $alertError = true): string
{
    $message = "";

    if (isset($_GET['status'])) {
        $status = (string)$_GET['status'];

        // sanitize status (remove all characters except numbers & unicode letters)
        $status = preg_replace('/[^a-zA-Z0-9éÉ :-]/', '', $status); // TODO: better sanitization
        // echo "<p>Status: $status</p>";

        // first 3 characters of status are the error code
        $code = substr($status, 0, 3);

        // everything after / is the error message
        $info = substr($status, 3);

        // separate camelCase words with spaces
        $info = preg_replace('/(?<! )[A-Z]/', ' $0', $info);

        // capitalize first letter
        $info = ucfirst($info);

        // if the error code is in the error dictionary, return the corresponding error message
        if (array_key_exists($code, $GLOBALS['errorDict'])) {
            $message = $GLOBALS['errorDict'][$code] . $info;
        } else {
            $message = "Error! Unknown error occurred.";
        }

        if ($alertError) {
            echo "<script>alert('". htmlspecialchars($message) ."');</script>";
        }
    }

    return $message;
}

/*
 * VALIDATION FUNCTIONS - ADD / EDIT GAME INPUTS
 *
 * validate_inputs() is the main function that calls all other validation functions
 * -> it is called when a game is added or updated
 */

function validate_inputs($post): void
{
    // the following fields are required:
    /*
     * - title
     * - developer
     * - release
     * - imageLink
     *
     * - at least one platform
     * - at least one multiplayer mode
     * - min and max player count for each selected multiplayer mode
     */

    validate_basics($post);

    validate_plat_input($post);

    $selectedPlatforms = validate_platforms($post);

    validate_modes($post, $selectedPlatforms);
}

#[NoReturn] function redirectToPreviousPage($msg): void
{
    if(isset($_SERVER['HTTP_REFERER'])) {
        // get the previous page URL
        $url = $_SERVER['HTTP_REFERER'];
    } else { // if there is no previous page
        $url = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php";
    }

    $strippedURL = strtok($url, '?'); // remove query string from URL

    $urlParams = parse_url($url, PHP_URL_QUERY); // get the query string from the URL
    parse_str($urlParams, $urlParams); // convert the query string to an associative array

    $urlParams['status'] = $msg; // add the error message to the URL parameters / overwrite it if it already exists

    $url = $strippedURL . '?' . http_build_query($urlParams); // rebuild the URL with the new parameters

    // redirect to the previous page
    header('Location: ' . $url);

    //echo "Redirecting to: $url";
    exit();
}

function validate_basics($post): void
{
    // check if required fields are set
    $requiredFields = ['title', 'developer', 'release', 'imageLink'];
    foreach ($requiredFields as $field) {
        if (empty($post[$field])) {
            redirectToPreviousPage("404/$field");
        }
    }

    // validate input format
    validate_title($post['title']);
    validate_developer($post['developer']);
    validate_release($post['release']);
    validate_url($post['imageLink']);
    validate_image($post['imageLink']);

    // when updating a game, the gameID is sent with the POST request in a hidden input field
    $gameID = $post['gameID'] ?? null;

    // check if the game already exists in the database
    $duplicate = check_duplicate_game_entry($post['title'], $post['developer'], $post['release'], $gameID);
    
    if ($duplicate !== false) {
        redirectToPreviousPage($duplicate);
    }
}

function validate_platforms($post): array
{
    $selectedPlatforms = [];

    foreach ($post as $key => $value) {
        if (str_starts_with($key, 'platform')) {
            $selectedPlatforms[] = $value; //platform checkboxes are only in §_POST if they are checked, value is the platformID
        }
    }

    if (empty($selectedPlatforms)) {
        redirectToPreviousPage("405");
    }
    return $selectedPlatforms;
}

function validate_modes($post, array $selectedPlatforms): void
{
    // get modeShort from database
    $sql = "SELECT modeShort FROM playermodes";
    $stmt = $GLOBALS['PDO']->prepare($sql);
    $stmt->execute();

    $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $selectedPlatformModes = [];

    // print selectedPlatforms
    /*
    echo "<pre>";
    print_r($selectedPlatforms);
    echo "</pre>";
    */

    // for each selected platform, check if at least one mode is selected
    foreach ($selectedPlatforms as $platform) {
        $selectedPlatformModes[$platform] = []; // assign an empty array to each platform key
        foreach ($modes as $mode) {
            foreach ($post as $key => $value) {
                if ($key === $mode . "_" . $platform) {
                    // check if the mode is already in the array
                    if (!in_array($mode, $selectedPlatformModes[$platform])) {
                        $selectedPlatformModes[$platform][] = $mode;
                    }
                }
            }
        }
    }
    /*
    echo "<p>Selected platform modes:</p>";
    echo "<pre>";
    print_r($selectedPlatformModes);
    echo "</pre>";
    */

    // if no mode is selected, redirect to previous page
    foreach ($selectedPlatformModes as $selectedModes) {
        if (empty($selectedModes)) {
            redirectToPreviousPage("406");
        }
    }

    validate_players($post, $selectedPlatformModes);
}

function validate_players($post, array $platformModes): void
{
    // platformModes is an associative array with platformID as key and an array of selected modes as value
    // for each platformID, check if a min and max player count is set for each mode
    foreach ($platformModes as $platform => $modes) {
        foreach ($modes as $mode) {
            $minString = $mode . "_min_" . $platform;
            $maxString = $mode . "_max_" . $platform;

            $maxStringFound = false;
            $minStringFound = false;

            foreach ($post as $key => $value) {
                if ($key === $minString || $key === $maxString) {
                    if ($key === $minString) {
                        $minStringFound = true;
                    }
                    if ($key === $maxString) {
                        $maxStringFound = true;
                    }

                    // check if the player count is set
                    if (empty($value)) {
                        redirectToPreviousPage("407");
                    }

/*
* VALIDATION FUNCTIONS - INPUT FORMAT
*/

                    /*
                     * check if:
                     * - the player count is a number
                     * - the player count is not negative
                     * - the max player count is not smaller than the min player count
                     */
                    else if (!is_numeric($value) || $value < 0) { // if the player count is not a number
                        redirectToPreviousPage("500/playerCount");
                    }
                    else if ($key === $maxString) {
                        $min = $post[$minString];
                        if ($value < $min) {
                            redirectToPreviousPage("500/minPlayerCount");
                        }
                    }
                }
            }
            if ($mode != "single" && (!$minStringFound || !$maxStringFound)) {
                redirectToPreviousPage("404/playerCount");
            }
        }
    }
}

function validate_plat_input($post): void
{
    // validate platform specific game info
    foreach ($post as $key => $value) {
        if (!empty($value) || $value != "") { // only validate non-empty values
            // validate store links
            if (str_starts_with($key, 'store_link_')) {
                validate_url($value);
            }
            // validate release dates
            if (str_starts_with($key, 'release_plat_')) {
                validate_release($value);
            }
            // validate game ids
            if (str_starts_with($key, 'game_id_')) {
                // get the platformID from the key
                $platformID = explode("_", $key)[2]; // key is in the format "game_id_platformID"

                $gameID = $post['gameID'] ?? null;

                validate_store_id($value, $platformID, $gameID);
            }
        }
    }
}

function validate_title($title): void
{
    if (strlen($title) > 176) {
        redirectToPreviousPage("501/title");
    }
}

function validate_developer($developer): void
{
    if (strlen($developer) > 30) {
        redirectToPreviousPage("501/developer");
    }
}

function validate_release($release): void
{
    echo "<p>Validating release date: $release<br></p>";

    if (!preg_match("/^\d{4}-\d{2}-\d{2}$/", $release)) {
        redirectToPreviousPage("500/release");
    }
}

function validate_url($url): void
{
    echo "<p>Validating URL: $url<br></p>";

    // check URL length
    if (strlen($url) > 150) {
        redirectToPreviousPage("501/url");
    }
    // check URL format
    if (!filter_var($url, FILTER_VALIDATE_URL)) {
        redirectToPreviousPage("500/url");
    }
}

function validate_image($url): void
{
    $context = stream_context_create(
        array(
            'http' => array(
                'timeout' => 5  // timeout in seconds
            )
        )
    );

    // check if it's a valid link to an image - https://stackoverflow.com/a/40694740
    $headers = get_headers($url, 1, $context); // get the headers of the URL
    if (str_contains($headers['Content-Type'], 'image/')) {
        list($width, $height) = getimagesize($url);
        // check if the image is a square
        if ($width != $height) {
            redirectToPreviousPage("505");
        }
    } else {
        redirectToPreviousPage("504");
    }
}

function validate_store_id($storeID, $platformID, $gameID): void
{
    echo "<p>Validating game ID: $storeID for platform $platformID<br></p>";

    // check gameID length
    if (strlen($storeID) > 30) {
        redirectToPreviousPage("501/gameID");
    }
    // check gameID format
    if (!preg_match("/^[a-zA-Z0-9]+$/", $storeID)) { // only allow alphanumeric characters
        redirectToPreviousPage("500/gameID");
    }

    // check if store id / game id is already in the database for the selected platform
    $sql = "SELECT 
                *
            FROM 
                game_platform_link 
            WHERE 
                storeID = :storeID AND 
                platformID = :platformID";
    $stmt = $GLOBALS['PDO']->prepare($sql);
    $stmt->bindParam(':storeID', $storeID);
    $stmt->bindParam(':platformID', $platformID);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (!empty($result)) {
        foreach ($result as $row) {
            if ($row['gameID'] != $gameID) {
                redirectToPreviousPage("502/$storeID");
            }
        }
    }
}

/*
 * VALIDATION FUNCTIONS - GENERAL
 *
 * modular -> can be used outside validate_inputs() context
*/

// check if the game already exists in the database
function check_duplicate_game_entry($title, $developer, $release, int $gameID = null): string|bool
{
    // echo "<p>Checking for duplicate game entry: $title by $developer, released on $release<br></p>";

    // get all games with the same title and developer
    $sql = "SELECT 
                *
            FROM 
                games
            JOIN 
                developers ON games.devID = developers.devID
            WHERE 
                gameName = :title AND 
                devName = :developer";
    $stmt = $GLOBALS['PDO']->prepare($sql);
    $stmt->bindParam(':title', $title);
    $stmt->bindParam(':developer', $developer);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // if there are games with the same title and developer...
    if (!empty($result)) {
        foreach ($result as $row) {
            // ...check if the gameID is different
            // if same gameID -> the game is being updated, no need to check for duplicates
            // if yes / null -> check if the release year is the same
            if ($gameID != $row['gameID']) {
                if (date('Y', strtotime($row['gameRelease'])) === date('Y', strtotime($release))) {
                    // if the release year is the same, return an error
                    // if not -> the game is not a duplicate -> e.g. Need for Speed: Most Wanted (2005) and Need for Speed: Most Wanted (2012)
                    return "503/$title";
                }
            }
        }
    }
    // if the game is not a duplicate, return false
    return false;
}


// check if developer exists in the database
function validate_dev_exists(string $devName): int|null
{
    $sql = "SELECT 
                devID 
            FROM 
                developers 
            WHERE 
                devName = :devName";
    $stmt = $GLOBALS['PDO']->prepare($sql);
    $stmt->bindParam(':devName', $devName);
    $stmt->execute();
    $result = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // return developer ID if it exists, else return null
    if (empty($result)) {
        return null;
    } else {
        return $result[0]['devID'];
    }
}

/*
 * VALIDATION FUNCTIONS - AUTHENTICATION
 */

function validate_login($username, $email, $password): void
{
    // check if required fields are set
    $requiredFields = [
        'username' => $username,
        'email' => $email,
        'password' => $password
    ];

    foreach ($requiredFields as $field => $value) {
        if (empty($value)) {
            redirectToPreviousPage("301/$field");
        }
    }

    // validate input format
    validate_username($username);
    validate_password($password);
    validate_email($email);
}

function validate_username($username): void
{
    if (strlen($username) > 20) {
        redirectToPreviousPage("302/username");
    }
}

function validate_password($password): void
{
    if (strlen($password) < 8) {
        redirectToPreviousPage("303/password");
    }
}

function validate_email($email): void
{
    if (strlen($email) > 50) {
        redirectToPreviousPage("302/email");
    }
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        redirectToPreviousPage("300/email");
    }
}