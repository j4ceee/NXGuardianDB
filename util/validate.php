<?php
use JetBrains\PhpStorm\NoReturn;

$errorDict = [
    "400" => "Error! Inputs were not submitted correctly.",
    "404" => "Error! Missing required field: ",
    "405" => "Error! No platforms selected.",
    "406" => "Error! No multiplayer modes selected.",
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

    validate_platforms($post);

    validate_modes($post);
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
            $msg = "404/$field";
            redirectToPreviousPage($msg);
        }
    }
}

function validate_platforms($post): void
{
    foreach ($post as $key => $value) {
        if (str_starts_with($key, 'platform')) {
            return;
        }
    }

    redirectToPreviousPage("405");
}

function validate_modes($post): void
{
    // get modeShort from database
    $sql = "SELECT modeShort FROM playermodes";
    $stmt = $GLOBALS['PDO']->prepare($sql);
    $stmt->execute();

    $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);

    $selectedModes = [];

    // the modes in $post look like this local_mp_14, single_05, online_mmo_13, ...
    // we only need the part before the last underscore
    foreach ($post as $key => $value) {
        foreach ($modes as $mode) {
            // check if the post key starts with the current mode & does not contain min or max
            if (str_starts_with($key, $mode) && !str_contains($key, 'min') && !str_contains($key, 'max')) {
                $selectedModes[] = $mode;
            }
        }
    }

    if (empty($selectedModes)) {
        redirectToPreviousPage("406");
    } else {
        validate_players($post, $selectedModes);
    }
}

function validate_players($post, $modes): void
{
    foreach ($post as $key => $value) {
        foreach ($modes as $mode) {
            if (str_starts_with($key, $mode) && (str_contains($key, 'min') || str_contains($key, 'max'))) {
                if (empty($value)) {
                    redirectToPreviousPage("407");
                }
            }
        }
    }
}