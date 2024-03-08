<?php
use JetBrains\PhpStorm\NoReturn;

$errorDict = [
    "400" => "Error! Inputs were not submitted correctly.",
    "404" => "Error! Missing required field: ",
    "405" => "Error! No platforms selected.",
    "406" => "Error! No multiplayer modes selected for a platform.",
    "407" => "Error! Missing player count for one of the selected multiplayer modes."
];

function getErrorMsg(): void
{
    if (isset($_GET['status'])) {
        $status = $_GET['status'];

        // first 3 characters of status are the error code
        $code = substr($status, 0, 3);

        // everything after / is the error message
        $info = substr($status, 4);

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

        echo "<script>alert('$message');</script>";
    }
}


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

    $selectedPlatforms = validate_platforms($post);

    validate_modes($post, $selectedPlatforms);
}

#[NoReturn] function redirectToPreviousPage($msg): void
{
    if(isset($_SERVER['HTTP_REFERER'])) {
        // get the previous page URL
        $url = $_SERVER['HTTP_REFERER'];
        $strippedURL = strtok($_SERVER['HTTP_REFERER'], '?'); // remove query string from URL

        $urlParams = parse_url($url, PHP_URL_QUERY); // get the query string from the URL
        parse_str($urlParams, $urlParams); // convert the query string to an associative array

        $urlParams['status'] = $msg; // add the error message to the URL parameters / overwrite it if it already exists

        $url = $strippedURL . '?' . http_build_query($urlParams); // rebuild the URL with the new parameters

        // redirect to the previous page
        header('Location: ' . $url);

        //echo "Redirecting to: $url";
    } else { // if there is no previous page
        // redirect to a default page
        header('Location: index.php');
    }
    exit;
}

function validate_basics($post): void
{
    $requiredFields = ['title', 'developer', 'release', 'imageLink'];
    foreach ($requiredFields as $field) {
        if (empty($post[$field])) {
            redirectToPreviousPage("404/$field");
        }
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
    foreach ($selectedPlatformModes as $platform => $selectedModes) {
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

            foreach ($post as $key => $value) {
                if ($key === $minString || $key === $maxString) {
                    echo "Key: $key, Value: $value<br>";
                    if (empty($value)) {
                        // echo "Missing player count for $mode on platform $platform";
                        redirectToPreviousPage("407");
                    }
                }
            }
        }
    }
}