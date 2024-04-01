<?php
require_once(dirname(__DIR__) . "/libs/json-machine/json_loader.php");
use \JsonMachine\Items;

// ------------------- LOGIN CHECK -------------------
session_set_cookie_params([
    'lifetime' => 0, // cookie expires at end of session
    'path' => '/', // cookie available within entire domain
    'domain' => 'localhost', // cookie domain
    'secure' => true, // cookie only sent over secure HTTPS connections
    'httponly' => true, // cookie only accessible via HTTP protocol, not by JS
    'samesite' => 'Strict' // cookie SameSite attribute: Lax (= some cross-site requests allowed) or Strict (= no cross-site requests allowed)
]);

session_start(); // start a session - preserves account data across pages // start a session - preserves account data across pages

session_regenerate_id(true); // regenerate session id for security

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // if user is not logged in, redirect to home page
    header('Location: https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/index.php?status=334');
    exit();
}
// ----------------- LOGIN CHECK END -------------------

//-------------------- TitleDB mode --------------------

// check if url contains titledb mode (?mode=ns...) & game index (?index=0)
$titleDBMode = isset($_GET['mode']) ? $_GET['mode'] : '';

//filter mode to only allow a - z & game index to only allow numbers
$titleDBMode = preg_replace("/[^a-z]/", "", $titleDBMode);

$titleDBenabled = false;

if ($titleDBMode === 'nsall' || $titleDBMode === 'nsfp') {
    $titleDBenabled = true;

    $gameIndex = 0; // start with the first game
}

//---------------- TitleDB mode end --------------------

$titleDBurl = 'https://raw.githubusercontent.com/blawar/titledb/master/GB.en.json';

if ($titleDBenabled) {
    // check if titledb json files exist
    if (!file_exists(dirname(__DIR__) . '/titledb/nx_titledb_all.json') || !file_exists(dirname(__DIR__) . '/titledb/nx_titledb_fp.json')) {
        // if json files don't exist, fetch new data
        fetchTitleDB($titleDBurl);
    } else {
        // if json files exist, check last modified date of json ALL -> if older than 1 week, fetch new data
        $fileCreationTime = filemtime(dirname(__DIR__) . '/titledb/nx_titledb_all.json');
        $currentTime = time();
        $timeDiff = $currentTime - $fileCreationTime;

        if ($timeDiff > 604800) { // 1 week = 604800 seconds
            fetchTitleDB($titleDBurl);
        }
    }
    header('Location: https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/manage_game.php?mode=' . $titleDBMode . '&index=' . $gameIndex);
    exit();
} else {
    // redirect to index.php
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
    exit();
}

function fetchTitleDB(string $titleDBurl): void
{
    $titleDB = '';

    // init json file placeholders
    $title_json_all = false;
    $title_json_fp = false;

    $removeString = array('®', '℠', '©', '™');
    // $removeString = array('\u00AE', '\u2120', '\u00A9', '\u2122', '\u2013');

    // these game entries will be ignored
    $blacklist = array(
        // non-full games
        'Demo',
        'Trial Edition',
        'Trial Version',
        'Free Trial',
        'Beta',
        'DLC',
        'Expansion',
        'Add-On',
        '(?<=\b.)Pack', // ignore 'Pack' if it's not at the beginning of the string
        'Early Release',
        'Additional',
        'Tool',
        'Membership',
        'Collection Bundle',
        'Game Bundle',
        'Play Test',
        'World Premiere',
        'costume for',
        'in-game',
        'Bonus Items',
        'exclusive gear',
        'Fighter Costume',
        'Special Episode',
        'Recipe',
        'Parts Set',
        'hairstyle set',
        'Uniform Set',
        'Emotes',
        'Collaboration Set',
        'Decals',


        // all kinds of passes
        'Season Pass',
        'Season Pass',
        'Season (One|Two|Three|Four|Five)? Pass',
        'Season \d+ Pass',
        'BROTHERS_SEASON PASS',
        'Pigeon Pass',
        'Year \d+ Pass',
        'Year Pass',
        '\d+ Month(s)? Pass',
        'Month(s)? \d+ ',
        'Character(s)? Pass',
        'Pass Vol',
        "Friend's Pass",
        'Course Pass',
        'Frontier Pass',
        'Backstage Pass',
        'Hero Pass',
        'Premium Pass',
        'Mission Pass',
        'Drive Pass',
        'Leader Pass',
        'Song Pass',
        'Stage Pass',
        'Super Pass',
        'Battle Pass',
        'Extra Pass',
        'FighterZ - ',
        'Fighter(s|z)? Pass',

        // editions
        'Retail',
        'Preorder',
        'Steelbook',
    );

    $first_all = true;
    $first_fp = true;

    try {
        // fetch titleDB json with curl
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $titleDBurl); // set url
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); // return the transfer as a string
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 10); // set connection timeout
        curl_setopt($ch, CURLOPT_TIMEOUT, 15); // set function timeout
        $titleDB = curl_exec($ch); // execute curl
        curl_close($ch); // close curl

        if ($titleDB === FALSE) {
            throw new Exception('Curl error: ' . curl_error($ch));
        }

        // convert json string to object
        $titleDB = Items::fromString($titleDB);

        $title_json_all = fopen(dirname(__DIR__) . '/titledb/nx_titledb_all.json', 'w');
        $title_json_fp = fopen(dirname(__DIR__) . '/titledb/nx_titledb_fp.json', 'w');

        if ($title_json_all === FALSE || $title_json_fp === FALSE) {
            $error = error_get_last();
            echo "fopen failed: " . $error['message'];
        }

        fwrite($title_json_all, '[');
        fwrite($title_json_fp, '[');

    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";
        // redirect to index.php
        header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
        exit();
    }

    if ($titleDB === '') {
        echo 'Error: TitleDB is empty';
    }

    foreach ($titleDB as $id => $data) {
        // to new json, include only the following fields
        /*
         * - iconUrl
         * - id
         * - name
         * - numberOfPlayers
         * - publisher
         * - releaseDate
         */

        // ----------------- Blacklist -----------------

        // ignore blacklisted entries
        $blacklisted = false;
        foreach ($blacklist as $blacklistItem) {
            if (preg_match('/\b' . $blacklistItem . '\b/i', $data->name)) {
                $blacklisted = true;
                break;
            }
        }

        // if title contains "Edition" & has no image, skip it
        if (preg_match('/\bEdition\b/i', $data->name) && $data->iconUrl === null) {
            $blacklisted = true;
        }

        // if it has no player number & image, but an id -> likely DLC -> skip it
        if ($data->numberOfPlayers === null && $data->iconUrl === null && $data->id !== null) {
            $blacklisted = true;
        }

        // if title contains ABC + XYZ or ABC: XYZ and has no image, storeID and numberOfPlayers
        if (preg_match('/[A-Z0-9](\s?\+|:)\s?[A-Z0-9]/i', $data->name) && ($data->iconUrl === null && $data->id === null && $data->numberOfPlayers === null)) {
            $blacklisted = true;
        }

        if ($blacklisted) {
            /*
            // write only title of Nintendo games to debug_blacklist.json
            if ($data->publisher === 'Nintendo') {
                file_put_contents(dirname(__DIR__) . '/titledb/debug_blacklist.txt', $data->name . ",\n", FILE_APPEND);
            }
            */
            continue; // skip this entry
        }

        // -------------- Blacklist end -----------------

        // remove strings from title
        $gameName = str_replace($removeString, '', $data->name);

        // remove strings from developer
        $developer = str_replace($removeString, '', $data->publisher);

        // format date: 20200320 -> 2020-03-20
        $releaseDate = $data->releaseDate;
        if ($releaseDate !== null && $releaseDate !== '') {
            $releaseDate = substr($releaseDate, 0, 4) . '-' . substr($releaseDate, 4, 2) . '-' . substr($releaseDate, 6, 2);
        } // if releaseDate has 4 characters -> only year + 01-01
        else if (strlen($releaseDate) === 4) {
            $releaseDate = $releaseDate . '-01-01';
        } else {
            $releaseDate = null;
        }

        $newData = array(
            'imageLink' => $data->iconUrl,
            'storeID' => $data->id,
            'title' => $gameName,
            'numberOfPlayers' => $data->numberOfPlayers,
            'publisher' => $developer,
            'releaseDate' => $releaseDate
        );

        try {
            // write to json ALL file
            if (!$first_all) {
                fwrite($title_json_all, ',');
            } else {
                $first_all = false;
            }
            fwrite($title_json_all, json_encode($newData, JSON_PRETTY_PRINT));

            // if publisher is Nintendo, write to json FP file as well
            if ($newData['publisher'] === 'Nintendo') {
                if (!$first_fp) {
                    fwrite($title_json_fp, ',');
                } else {
                    $first_fp = false;
                }
                fwrite($title_json_fp, json_encode($newData, JSON_PRETTY_PRINT));
            }
        } catch (Exception $e) {
            echo 'Caught exception: ', $e->getMessage(), "\n";

            // redirect to index.php
            header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
            exit();
        }
    }

    try {
        fwrite($title_json_all, ']');
        fclose($title_json_all);

        fwrite($title_json_fp, ']');
        fclose($title_json_fp);
    } catch (Exception $e) {
        echo 'Caught exception: ', $e->getMessage(), "\n";

        // redirect to index.php
        header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
        exit();
    }

    $titleDB = null; // free memory
}
