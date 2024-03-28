<?php
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file
include_once (dirname(__DIR__) . '/util/validate.php');

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
    exit();
}

ob_start(); // start output buffering

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);

    // add developer-------------------------------------------------------------------------------

    // validate all inputs
    validate_inputs($_POST);

    $developerName = trim($_POST['developer']);

    // sanitize input
    $developerName = htmlspecialchars($developerName);


    // check if developer exists in database
    $dev = validate_dev_exists($developerName);

    if ($dev == null) {
        // insert new developer
        $stmt = $PDO->prepare("INSERT INTO developers (devName) VALUES (:devName)");
        $stmt->execute(['devName' => $developerName]);
        $devID = $PDO->lastInsertId();
    } else {
        $devID = $dev;
    }

    // add game-----------------------------------------------------------------------------------
    $gameName = trim($_POST['title']);
    $gameRelease = trim($_POST['release']);
    $imageLink = trim($_POST['imageLink']);

    // sanitize input
    $gameRelease = htmlspecialchars($gameRelease);
    $imageLink = filter_var($imageLink, FILTER_SANITIZE_URL);


    $stmt = $PDO->prepare("INSERT INTO games (gameName, gameRelease, devID, imageLink) VALUES (:gameName, :gameRelease, :devID, :imageLink)");
    $stmt->execute([
        'gameName' => $gameName,
        'gameRelease' => $gameRelease,
        'devID' => $devID,
        'imageLink' => $imageLink // TODO: handle as link in frontend & database
    ]);
    $gameID = $PDO->lastInsertId();

    // connect game to platforms-------------------------------------------------------------------
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selectedPlatforms = []; // array to hold IDs of selected platforms and their details

    foreach ($_POST as $key => $value) {
        // check if the form field name indicates a platform selection
        if (str_starts_with($key, 'platform')) {
            $platformID = (int)$value;

            $platformID = filter_var($platformID, FILTER_SANITIZE_NUMBER_INT);

            // initialize array to store platform details
            $platformDetails = [
                'id' => $platformID,
                'store_link' => null,
                'release_date' => null,
                'store_id' => null,
            ];

            // capture platform-specific details if they exist
            if (isset($_POST["store_link_$platformID"])) {
                $storeLink = htmlspecialchars(trim($_POST["store_link_$platformID"]));
                $platformDetails['store_link'] = empty($storeLink) ? null : $storeLink;
            }
            if (isset($_POST["release_plat_$platformID"])) {
                $releaseDate = htmlspecialchars(trim($_POST["release_plat_$platformID"]));
                $platformDetails['release_date'] = empty($releaseDate) ? null : $releaseDate;
            }
            if (isset($_POST["game_id_$platformID"])) {
                $storeID = htmlspecialchars(trim($_POST["game_id_$platformID"]));
                $platformDetails['store_id'] = empty($storeID) ? null : $storeID;
            }

            // add to selected platforms array
            $selectedPlatforms[] = $platformDetails;

            /* array looks like this:
            Array
            (
                [0]
                    (
                        [id] => 1
                        [store_link] => https://store.steampowered.com/app/123456/Example_Game/
                        [release_date] => 2022-12-31
                        [store_id] => 123456
                    )

                [1]
                    (
                        [id] => 2
                        [store_link] =>
                        [release_date] => 2022-12-31
                        [store_id] => 123456
                    )

            )
            */
        }
    }


    // get all modeShort from database table playermodes
    $stmt = $PDO->prepare("SELECT modeShort FROM playermodes");
    $stmt->execute();
    $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    // for use to check if a mode is selected in following loop


    // loop through the $selectedPlatforms array and insert each platform array into the game_platform_link table
    foreach ($selectedPlatforms as $platformDetails) {
        /*
        echo "<br>";
        echo "Debugging SQL Statement:\n";
        echo "Game ID: $gameID\n";
        echo "Platform ID: {$platformDetails['id']}\n";
        echo "Release Date: {$platformDetails['release_date']}\n";
        echo "Store Link: {$platformDetails['store_link']}\n";
        echo "Store ID: {$platformDetails['store_id']}\n";
        */


        $stmt = $PDO->prepare("INSERT INTO game_platform_link (gameID, platformID, releaseDate, storeLink, storeID) VALUES (:gameID, :platformID, :releaseDate, :storeLink , :storeID)");
        $stmt->execute([
            ':gameID' => $gameID, // use the game ID from the previous insert
            ':platformID' => $platformDetails['id'],
            ':releaseDate' => $platformDetails['release_date'],
            ':storeLink' => $platformDetails['store_link'],
            ':storeID' => $platformDetails['store_id'],
        ]);


        // connect game_platform to multiplayer modes---------------------------------------------------

        // get the last inserted ID for game_platform_link to use as game_platformID
        $gamePlatformID = $PDO->lastInsertId();

        // iterate over multiplayer modes submitted for this platform
        foreach ($_POST as $key => $value) {
            // check if the key starts with the multiplayer mode prefix and is related to the current platform

            foreach ($modes as $modeShort) {
                // following regex pattern checks if the key starts with the modeShort and platform ID, but not followed by a digit
                $pattern = sprintf('/^%s_%s(?!\d)/', $modeShort, $platformDetails['id']);

                if (preg_match($pattern, $key)) {
                    echo "Key: $key\n";

                    $key = htmlspecialchars($key);

                    // find the last underscore which separates modeShort (and potential suffix) from platform ID
                    $lastUnderscorePos = strrpos($key, '_');
                    if ($lastUnderscorePos !== false) {
                        // extract everything before the last underscore
                        $modeWithSuffix = substr($key, 0, $lastUnderscorePos);

                        echo "Mode with Suffix: $modeWithSuffix\n";

                        // check if there's a suffix (_min or _max) and remove it
                        foreach (['_min', '_max'] as $suffix) {
                            if (str_ends_with($modeWithSuffix, $suffix)) {
                                $modeShort = substr($modeWithSuffix, 0, -strlen($suffix));
                                break;
                            } else {
                                $modeShort = $modeWithSuffix; // if no suffix, don't change modeShort
                            }
                        }

                        $modeID = getModeIDFromModeName($PDO, $modeShort); // retrieve modeID based on modeShort
                        echo "Mode ID: $modeID\n";

                        if ($modeID !== null) {
                            // get min and max players, else default to 0
                            $minPlayersKey = "{$modeShort}_min_{$platformDetails['id']}";
                            $maxPlayersKey = "{$modeShort}_max_{$platformDetails['id']}";
                            $minPlayers = isset($_POST[$minPlayersKey]) ? (int)htmlspecialchars($_POST[$minPlayersKey]) : 0;
                            $maxPlayers = isset($_POST[$maxPlayersKey]) ? (int)htmlspecialchars($_POST[$maxPlayersKey]) : 0;

                            $minPlayers = filter_var($minPlayers, FILTER_SANITIZE_NUMBER_INT);
                            $maxPlayers = filter_var($maxPlayers, FILTER_SANITIZE_NUMBER_INT);

                            echo "<br>";
                            echo "Debugging SQL Statement:\n";
                            echo "Game Platform ID: $gamePlatformID\n";
                            echo "Mode ID: $modeID\n";
                            echo "Min Players: $minPlayers\n";
                            echo "Max Players: $maxPlayers\n";

                            // insert into game_platform_player_link
                            $stmt = $PDO->prepare("INSERT INTO game_platform_player_link (game_platformID, modeID, minPlayers, maxPlayers) VALUES (:gamePlatformID, :modeID, :minPlayers, :maxPlayers)");
                            $stmt->execute([
                                ':gamePlatformID' => $gamePlatformID,
                                ':modeID' => $modeID,
                                ':minPlayers' => $minPlayers,
                                ':maxPlayers' => $maxPlayers,
                            ]);
                        }
                    }
                }
            }
        }
    }

    // TODO: success message after redirect

    //-------------------- TitleDB mode --------------------

    // check if url contains titledb mode (?mode=ns...) & game index (?index=0)
    $titleDBMode = isset($_GET['mode']) ? $_GET['mode'] : '';
    $gameIndex = isset($_GET['index']) ? $_GET['index'] : '';

    //filter mode to only allow a - z & game index to only allow numbers
    $titleDBMode = preg_replace("/[^a-z]/", "", $titleDBMode);
    $gameIndex = (int)preg_replace("/[^0-9]/", "", $gameIndex);

    $titleDBenabled = false;

    if ($titleDBMode === 'nsall' || $titleDBMode === 'nsfp') {
        $titleDBenabled = true;

        // if game index is empty, set it to 0
        if ($gameIndex === '') {
            $gameIndex = 0;
        }
    }

    //---------------- TitleDB mode end --------------------

    if ($titleDBenabled) {
        header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/add_game.php?mode=$titleDBMode&index=$gameIndex");
    } else {
        header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/list_games.php?gameID=$gameID");
    }


    // echo '<a href="../list_games.php?gameID=' . $gameID . '">View Game</a>';
    ob_end_flush(); // end output buffering
    exit();

} else {
    // redirect to previous page
    ob_end_flush(); // end output buffering
    redirectToPreviousPage("400");
}

function getModeIDFromModeName($PDO, $modeShort) {
    $stmt = $PDO->prepare("SELECT modeID FROM playermodes WHERE modeShort = :modeShort");
    $stmt->execute([':modeShort' => $modeShort]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['modeID'] : null;
}