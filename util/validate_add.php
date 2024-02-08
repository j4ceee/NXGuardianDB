<?php
include_once './util/conn_db.php'; // include database connection file

$PDO = getPDO(); // get PDO connection

if (!checkDBExists()) {
    header("Location: ./index.php");
    exit();
}

useDB();

error_reporting(E_ALL);
ini_set('display_errors', 1);

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    //var_dump($_POST);
    // TODO: validate input

    // add developer-------------------------------------------------------------------------------
    $developerName = trim($_POST['developer']);
    // check if developer exists
    $stmt = $PDO->prepare("SELECT devID FROM developers WHERE devName = :devName");
    $stmt->execute(['devName' => $developerName]);
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dev) {
        // insert new developer
        $stmt = $PDO->prepare("INSERT INTO developers (devName) VALUES (:devName)");
        $stmt->execute(['devName' => $developerName]);
        $devID = $PDO->lastInsertId();
    } else {
        $devID = $dev['devID'];
    }

    // add game-----------------------------------------------------------------------------------
    $stmt = $PDO->prepare("INSERT INTO games (gameName, gameRelease, devID, steamgridID, steamgridImageID) VALUES (:gameName, :gameRelease, :devID, :steamgridID, :steamgridImageID)");
    $stmt->execute([
        'gameName' => trim($_POST['title']),
        'gameRelease' => trim($_POST['release']),
        'devID' => $devID,
        'steamgridID' => 1234, // TODO: handle optional field
        'steamgridImageID' => trim($_POST['sgdb-grid-id']) // TODO: handle as link in frontend & database
    ]);
    $gameID = $PDO->lastInsertId();

    // connect game to platforms-------------------------------------------------------------------
    $PDO->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $selectedPlatforms = []; // array to hold IDs of selected platforms and their details

    foreach ($_POST as $key => $value) {
        // check if the form field name indicates a platform selection
        if (str_starts_with($key, 'platform')) {
            $platformID = $value;

            // initialize array to store platform details
            $platformDetails = [
                'id' => $platformID,
                'store_link' => null,
                'release_date' => null,
            ];

            // capture platform-specific details if they exist
            if (isset($_POST["store_link_$platformID"])) {
                $platformDetails['store_link'] = trim($_POST["store_link_$platformID"]);
            }
            if (isset($_POST["release_date_$platformID"])) {
                $platformDetails['release_date'] = trim($_POST["release_date_$platformID"]);
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
                    )

                [1]
                    (
                        [id] => 2
                        [store_link] =>
                        [release_date] => 2022-12-31
                    )

            )
            */
        }
    }

    // TODO: handle no platforms selected
    if (empty($selectedPlatforms)) {
        echo "No platforms selected";
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
        */

        $stmt = $PDO->prepare("INSERT INTO game_platform_link (gameID, platformID, releaseDate, storeLink) VALUES (:gameID, :platformID, :releaseDate, :storeLink)");
        $stmt->execute([
            ':gameID' => $gameID, // use the game ID from the previous insert
            ':platformID' => $platformDetails['id'],
            ':releaseDate' => $platformDetails['release_date'],
            ':storeLink' => $platformDetails['store_link'],
        ]);


        // connect game_platform to multiplayer modes---------------------------------------------------

        // get the last inserted ID for game_platform_link to use as game_platformID
        $gamePlatformID = $PDO->lastInsertId();

        // iterate over multiplayer modes submitted for this platform
        foreach ($_POST as $key => $value) {
            // check if the key starts with the multiplayer mode prefix and is related to the current platform
            // TODO: compare to all strings modeShort from DB instead of typing all strings again here

            foreach ($modes as $modeShort) {
                // following regex pattern checks if the key starts with the modeShort and platform ID, but not followed by a digit
                $pattern = sprintf('/^%s_%s(?!\d)/', $modeShort, $platformDetails['id']);

                if (preg_match($pattern, $key)) {
                    echo "Key: $key\n";

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
                            $minPlayers = $_POST[$modeShort . '_min_' . $platformDetails['id']] ?? 0;
                            $maxPlayers = $_POST[$modeShort . '_max_' . $platformDetails['id']] ?? 0;

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

    // TODO: redirect to list_games.php
}

function getModeIDFromModeName($PDO, $modeShort) {
    $stmt = $PDO->prepare("SELECT modeID FROM playermodes WHERE modeShort = :modeShort");
    $stmt->execute([':modeShort' => $modeShort]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['modeID'] : null;
}

?>