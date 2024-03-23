<?php /** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
/** @noinspection PhpUnnecessaryCurlyVarSyntaxInspection */
include_once './conn_db.php'; // include database connection file
include_once './validate.php';

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

ob_start(); // start output buffering

error_reporting(E_ALL);
ini_set('display_errors', 1);

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);

    // validate all inputs
    validate_inputs($_POST);

    $gameID = $_POST['gameID'];
    $gameName = trim($_POST['title']);
    $devName = trim($_POST['developer']);
    $gameRelease = trim($_POST['release']);
    $imageLink = trim($_POST['imageLink']);

    // sanitize input
    $gameID = filter_var($gameID, FILTER_SANITIZE_NUMBER_INT);
    $gameName = htmlspecialchars($gameName);
    $devName = htmlspecialchars($devName);
    $gameRelease = htmlspecialchars($gameRelease);
    $imageLink = filter_var($imageLink, FILTER_SANITIZE_URL);


    if (empty($gameID)) {
        return;
    }

    // update game name, developer, release date, and image link----------------------------------------

    $fetchCurrentDevSql = "SELECT g.devID, d.devName 
                           FROM games g 
                           JOIN developers d ON g.devID = d.devID 
                           WHERE g.gameID = :gameID";
    $fetchStmt = $PDO->prepare($fetchCurrentDevSql);
    $fetchStmt->bindParam(':gameID', $gameID, PDO::PARAM_INT);
    $fetchStmt->execute();
    $currentDev = $fetchStmt->fetch(PDO::FETCH_ASSOC);

    $currentDevID = $currentDev['devID'] ?? null;
    $currentDevName = $currentDev['devName'] ?? '';

    // update game info
    $sql = "UPDATE games SET gameName = :title, gameRelease = :release, imageLink = :imageLink WHERE gameID = :gameID";
    $stmt = $PDO->prepare($sql);
    $stmt->bindParam(':gameID', $gameID, PDO::PARAM_INT);
    $stmt->bindParam(':title', $gameName);
    $stmt->bindParam(':release', $gameRelease);
    $stmt->bindParam(':imageLink', $imageLink);

    // TODO: when updating the developer, check if the developer already exists in the database
    // TODO: if the developer already exists, change the current devID of the game to the existing devID
    // TODO: unhandled exception: if the developer name is changed to an existing developer name -> SQL error since devName is unique
    // update developer info
    $sql2 = "UPDATE developers SET devName = :devName WHERE devID = :devID";
    $stmt2 = $PDO->prepare($sql2);
    $stmt2->bindParam(':devID', $currentDevID, PDO::PARAM_INT);
    $stmt2->bindParam(':devName', $devName);


    // debug -> echo changes TODO: remove after testing
    // get the old values from the database
    $oldValuesQuery = $PDO->prepare("SELECT gameName, gameRelease, imageLink, devName FROM games JOIN developers ON games.devID = developers.devID WHERE gameID = :gameID");
    $oldValuesQuery->bindParam(':gameID', $gameID, PDO::PARAM_INT);
    $oldValuesQuery->execute();
    $oldValues = $oldValuesQuery->fetch(PDO::FETCH_ASSOC);

    // debug -> echo differences TODO: remove after testing
    $newValues = [
        'gameName' => $gameName,
        'gameRelease' => $gameRelease,
        'imageLink' => $imageLink,
        'devName' => $devName,
    ];

    foreach ($oldValues as $key => $value) { // TODO: remove after testing
        if ($value === $newValues[$key]) {
            /*
            echo "<br>";
            echo $key . "<br>";
            echo "same------------------<br>";
            echo "Old - $value / {$newValues[$key]} - New<br>";
            echo "----------------------<br>";
            */
        } else {
            echo "<br>";
            echo $key . "<br>";
            echo "different-------------<br>";
            echo "Old - $value / $newValues[$key] - New<br>";
            echo "----------------------<br>";
        }
    }

    //var_dump($stmt);
    //var_dump($stmt2);
    // execute the statement
    $stmt->execute();
    $stmt2->execute();

    echo "<br>------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br>";

    /****************************************************************************************
     * following code is for connecting the game to platforms and multiplayer modes         *
     * add if new, delete if removed & update if changed                                    *
     * **************************************************************************************/


    // fetch existing platforms for game
    $currentPlatformsQuery = $PDO->prepare("SELECT platformID, releaseDate, storeLink, storeID FROM game_platform_link WHERE gameID = :gameID");
    $currentPlatformsQuery->execute([':gameID' => $gameID]);
    $currentPlatforms = $currentPlatformsQuery->fetchAll(PDO::FETCH_ASSOC);

    // convert to associative array
    $currentPlatformsAssoc = [];
    foreach ($currentPlatforms as $platform) {
        $currentPlatformsAssoc[$platform['platformID']] = $platform;
    }

    // fetch existing multiplayer modes for game
    $currentModesQuery = $PDO->prepare("
    SELECT gp.platformID, gppl.modeID, gppl.minPlayers, gppl.maxPlayers
    FROM game_platform_player_link gppl
    JOIN game_platform_link gp ON gppl.game_platformID = gp.game_platformID
    WHERE gp.gameID = :gameID");
    $currentModesQuery->execute([':gameID' => $gameID]);
    $currentModes = $currentModesQuery->fetchAll(PDO::FETCH_ASSOC);

    // convert to associative array
    $currentModesAssoc = [];
    foreach ($currentModes as $mode) {
        $currentModesAssoc[$mode['platformID']][$mode['modeID']] = $mode;
    }

    // get all selected platforms
    $selectedPlatforms = []; // array to hold IDs of selected platforms and their details

    foreach ($_POST as $key => $value) {
        // check if the form field name indicates a platform selection
        if (str_starts_with($key, 'platform')) {

            // sanitize input
            $value = filter_var($value, FILTER_SANITIZE_NUMBER_INT);

            $platformID = (int)$value;

            // initialize array to store platform details
            $platformDetails = [
                'platformID' => $platformID,
                'storeLink' => null,
                'releaseDate' => null,
                'storeID' => null,
            ];

            // capture platform-specific details if they exist
            if (isset($_POST["store_link_$platformID"])) {
                $storeLink = htmlspecialchars(trim($_POST["store_link_$platformID"]));
                $platformDetails['storeLink'] = empty($storeLink) ? null : $storeLink;
            }
            if (isset($_POST["release_plat_$platformID"])) {
                $releaseDate = htmlspecialchars(trim($_POST["release_plat_$platformID"]));
                $platformDetails['releaseDate'] = empty($releaseDate) ? null : $releaseDate;
            }

            if (isset($_POST["game_id_$platformID"])) {
                $storeID = htmlspecialchars(trim($_POST["game_id_$platformID"]));
                $platformDetails['storeID'] = empty($storeID) ? null : $storeID;
            }

            // add to selected platforms array
            $selectedPlatforms[] = $platformDetails;
        }
    }


    // get all modeShort from database table playermodes
    $stmt = $PDO->prepare("SELECT modeShort FROM playermodes");
    $stmt->execute();
    $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);
    // for use to check if a mode is selected in following loop


    // start transaction
    $PDO->beginTransaction();

    try {
        // perform updates, inserts, and deletions

        // update existing platform links--------------------------------------------------------------
        foreach ($selectedPlatforms as $platformDetails) {
            $platformID = $platformDetails['platformID'];

            // update existing platform link
            if (array_key_exists($platformID, $currentPlatformsAssoc)) {
                // compare $platformDetails with $currentPlatformsAssoc[$platformID] & update if different

                // debug -> echo differences TODO: remove after testing
                foreach ($currentPlatformsAssoc[$platformID] as $key => $value) {
                    // var_dump($value, $platformDetails[$key]);
                    if ($value === $platformDetails[$key]) {
                        /*
                        echo "<br>";
                        echo $key . "<br>";
                        echo "same------------------<br>";
                        echo "Old - $value / {$platformDetails[$key]} - New<br>";
                        echo "----------------------<br>";
                        */
                    } else {
                        echo "<br>";
                        echo $key . "<br>";
                        echo "different-------------<br>";
                        echo "Old - $value / $platformDetails[$key] - New<br>";
                        echo "----------------------<br>";
                    }
                }

                if ($platformDetails['storeLink'] !== $currentPlatformsAssoc[$platformID]['storeLink'] || $platformDetails['releaseDate'] !== $currentPlatformsAssoc[$platformID]['releaseDate'] || $platformDetails['storeID'] !== $currentPlatformsAssoc[$platformID]['storeID']) {
                    $stmt = $PDO->prepare("UPDATE game_platform_link SET releaseDate = :releaseDate, storeLink = :storeLink, storeID = :storeID WHERE gameID = :gameID AND platformID = :platformID");
                    $stmt->execute([
                        ':gameID' => $gameID,
                        ':platformID' => $platformID,
                        ':releaseDate' => $platformDetails['releaseDate'],
                        ':storeLink' => $platformDetails['storeLink'],
                        ':storeID' => $platformDetails['storeID'],
                    ]);
                }

            } else {
                // debug -> echo new platform link TODO: remove after testing
                echo "<br>";
                echo "New platform link------------------<br>";
                echo "Game ID: $gameID<br>";
                echo "Platform ID: $platformID<br>";
                echo "Release Date: {$platformDetails['releaseDate']}<br>";
                echo "Store Link: {$platformDetails['storeLink']}<br>";
                echo "Store ID: {$platformDetails['storeID']}<br>";
                echo "-----------------------------------<br>";
                echo "<br>";


                // new platform link
                $stmt = $PDO->prepare("INSERT INTO game_platform_link (gameID, platformID, releaseDate, storeLink, storeID) VALUES (:gameID, :platformID, :releaseDate, :storeLink, :storeID)");
                $stmt->execute([
                    ':gameID' => $gameID,
                    ':platformID' => $platformID,
                    ':releaseDate' => $platformDetails['releaseDate'],
                    ':storeLink' => $platformDetails['storeLink'],
                    ':storeID' => $platformDetails['storeID'],
                ]);
            }
        }
        // delete removed platform links--------------------------------------------------------------
        foreach ($currentPlatformsAssoc as $platformID => $platformDetails) {
            if (!in_array($platformID, array_column($selectedPlatforms, 'platformID'))) {
                // debug -> echo removed platform link TODO: remove after testing
                echo "<br>";
                echo "Removed platform link------------------<br>";
                echo "Game ID: $gameID<br>";
                echo "Platform ID: $platformID<br>";
                echo "----------------------------------------<br>";


                $stmt = $PDO->prepare("DELETE FROM game_platform_link WHERE gameID = :gameID AND platformID = :platformID");
                $stmt->execute([
                    ':gameID' => $gameID,
                    ':platformID' => $platformID,
                ]);
            }
        }

        echo "<br>------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------<br>";

        // update existing multiplayer modes-----------------------------------------------------------
        foreach ($selectedPlatforms as $platformDetails) {
            $platformID = $platformDetails['platformID'];

            // Prepare a list to track which modes were processed
            $processedModes = [];

            foreach ($modes as $modeShort) {
                // check if mode is present in form submission
                $modePresenceKey = "{$modeShort}_{$platformID}";

                if (isset($_POST[$modePresenceKey])) { // mode is present in form submission
                    $minPlayersKey = "{$modeShort}_min_{$platformID}";
                    $maxPlayersKey = "{$modeShort}_max_{$platformID}";
                    $minPlayers = isset($_POST[$minPlayersKey]) ? (int)htmlspecialchars($_POST[$minPlayersKey]) : 0;
                    $maxPlayers = isset($_POST[$maxPlayersKey]) ? (int)htmlspecialchars($_POST[$maxPlayersKey]) : 0;

                    // determine if this mode needs to be updated or added
                    $modeID = getModeIDFromModeName($PDO, $modeShort);

                    // debug array for modeID with min and max players & platformID from $platformDetails TODO: remove after testing
                    $modeInfoArray = [
                        'modeID' => $modeID,
                        'minPlayers' => $minPlayers,
                        'maxPlayers' => $maxPlayers,
                        'platformID' => $platformID,
                    ];

                    if ($modeID !== null && isset($currentModesAssoc[$platformID][$modeID])) {
                        // update existing mode details if changed

                        // debug -> echo differences TODO: remove after testing
                        foreach ($currentModesAssoc[$platformID][$modeID] as $key => $value) {
                            // var_dump($value, $modeIDArray[$key]);
                            if ($value === $modeInfoArray[$key]) {
                                /*
                                echo "<br>";
                                // echo name of current value
                                echo $key . "<br>";
                                echo "same------------------<br>";
                                echo "Old - $value / {$modeIDArray[$key]} - New<br>";
                                echo "----------------------<br>";
                                */
                            } else {
                                echo "<br>";
                                echo $key . "<br>";
                                echo "different-------------<br>";
                                echo "Old - $value / $modeInfoArray[$key] - New<br>";
                                echo "----------------------<br>";
                            }
                        }

                        if ($minPlayers !== $currentModesAssoc[$platformID][$modeID]['minPlayers'] || $maxPlayers !== $currentModesAssoc[$platformID][$modeID]['maxPlayers']) {

                            $updateStmt = $PDO->prepare("UPDATE game_platform_player_link SET minPlayers = :minPlayers, maxPlayers = :maxPlayers WHERE game_platformID = (SELECT game_platformID FROM game_platform_link WHERE gameID = :gameID AND platformID = :platformID) AND modeID = :modeID");
                            $updateStmt->execute([
                                ':minPlayers' => $minPlayers,
                                ':maxPlayers' => $maxPlayers,
                                ':gameID' => $gameID,
                                ':platformID' => $platformID,
                                ':modeID' => $modeID,
                            ]);
                        }
                    } else { // mode is new
                        // insert new mode link

                        // debug -> echo new mode link TODO: remove after testing
                        echo "<br>";
                        echo "New mode link------------------<br>";
                        echo "Game ID: $gameID<br>";
                        echo "Platform ID: $platformID<br>";
                        echo "Mode ID: $modeID<br>";
                        echo "Min Players: $minPlayers<br>";
                        echo "Max Players: $maxPlayers<br>";
                        echo "-----------------------------------<br>";


                        $insertStmt = $PDO->prepare("INSERT INTO game_platform_player_link (game_platformID, modeID, minPlayers, maxPlayers) VALUES ((SELECT game_platformID FROM game_platform_link WHERE gameID = :gameID AND platformID = :platformID), :modeID, :minPlayers, :maxPlayers)");
                        $insertStmt->execute([
                            ':gameID' => $gameID,
                            ':platformID' => $platformID,
                            ':modeID' => $modeID,
                            ':minPlayers' => $minPlayers,
                            ':maxPlayers' => $maxPlayers,
                        ]);
                    }

                    // mark this mode as processed
                    $processedModes[] = $modeID;
                }
            }

            // delete any modes not processed (i.e., removed by the user)
            foreach ($currentModesAssoc[$platformID] ?? [] as $modeID => $details) {

                // debug -> echo removed mode link TODO: remove after testing
                if (!in_array($modeID, $processedModes)) {
                    echo "<br>";
                    echo "Removed mode link------------------<br>";
                    echo "Game ID: $gameID<br>";
                    echo "Platform ID: $platformID<br>";
                    echo "Mode ID: $modeID<br>";
                    echo "-----------------------------------<br>";
                    echo "<br>";
                }

                if (!in_array($modeID, $processedModes)) {
                    // mode was removed by the user
                    $deleteStmt = $PDO->prepare("DELETE FROM game_platform_player_link WHERE game_platformID = (SELECT game_platformID FROM game_platform_link WHERE gameID = :gameID AND platformID = :platformID) AND modeID = :modeID");
                    $deleteStmt->execute([
                        ':gameID' => $gameID,
                        ':platformID' => $platformID,
                        ':modeID' => $modeID,
                    ]);
                }
            }
        }

        $PDO->commit();
        // $PDO->rollBack(); // for now, rollback to test the code
    } catch (Exception $e) {
        $PDO->rollBack();
        // TODO: handle error
    }

    // redirect to list_games.php
    header("Location: ../list_games.php?gameID=$gameID");
    // echo '<a href="../list_games.php?gameID=' . $gameID . '">View Game</a>';
    ob_end_flush(); // end output buffering
    exit();
} else {
    // redirect to previous page
    ob_end_flush(); // end output buffering
    redirectToPreviousPage("400");
}

function getModeIDFromModeName($PDO, $modeShort)
{
    $stmt = $PDO->prepare("SELECT modeID FROM playermodes WHERE modeShort = :modeShort");
    $stmt->execute([':modeShort' => $modeShort]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['modeID'] : null;
}