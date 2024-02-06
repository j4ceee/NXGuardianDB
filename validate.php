<?php
/** @var PDO $conn */
/** @var string $dbname */
/** @var bool $failed */
include './util/conn_db.php'; // include database connection file

if (!$failed) {
    $stmt = $conn->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
    $stmt->execute(['dbname' => $dbname]); // execute statement with database name

    $result = $stmt->fetchAll(); // fetch all results and store in $result

    if (count($result) == 0) {
        // Redirect back to index.php
        header("Location: ../index.php");
        exit();
    }
} else {
    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

$conn->exec("USE $dbname");

// check if form is submitted
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    var_dump($_POST);

    // add developer-------------------------------------------------------------------------------
    $developerName = $_POST['developer'];
    // check if developer exists
    $stmt = $conn->prepare("SELECT devID FROM developers WHERE devName = :devName");
    $stmt->execute(['devName' => $developerName]);
    $dev = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$dev) {
        // insert new developer
        $stmt = $conn->prepare("INSERT INTO developers (devName) VALUES (:devName)");
        $stmt->execute(['devName' => $developerName]);
        $devID = $conn->lastInsertId();
    } else {
        $devID = $dev['devID'];
    }

    // add game-----------------------------------------------------------------------------------
    $stmt = $conn->prepare("INSERT INTO games (gameName, gameRelease, devID, steamgridID, steamgridImageID) VALUES (:gameName, :gameRelease, :devID, :steamgridID, :steamgridImageID)");
    $stmt->execute([
        'gameName' => $_POST['title'],
        'gameRelease' => $_POST['release'],
        'devID' => $devID,
        'steamgridID' => $_POST['sgdb-id'],
        'steamgridImageID' => $_POST['sgdb-grid-id'] // TODO: handle appropriately if this field is optional
    ]);
    $gameID = $conn->lastInsertId();

    // connect game to platforms-------------------------------------------------------------------
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

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
                $platformDetails['store_link'] = $_POST["store_link_$platformID"];
            }
            if (isset($_POST["release_date_$platformID"])) {
                $platformDetails['release_date'] = $_POST["release_date_$platformID"];
            }

            // add to selected platforms array
            $selectedPlatforms[] = $platformDetails;
        }
    }

    if (empty($selectedPlatforms)) {
        echo "No platforms selected";
    }

    foreach ($selectedPlatforms as $platformDetails) {
        /*
        echo "Debugging SQL Statement:\n";
        echo "Game ID: $gameID\n";
        echo "Platform ID: {$platformDetails['id']}\n";
        echo "Release Date: {$platformDetails['release_date']}\n";
        echo "Store Link: {$platformDetails['store_link']}\n";
        */

        $stmt = $conn->prepare("INSERT INTO game_platform_link (gameID, platformID, releaseDate, storeLink) VALUES (:gameID, :platformID, :releaseDate, :storeLink)");
        $stmt->execute([
            ':gameID' => $gameID, // use the game ID from the previous insert
            ':platformID' => $platformDetails['id'],
            ':releaseDate' => $platformDetails['release_date'],
            ':storeLink' => $platformDetails['store_link'],
        ]);



        // Get the last inserted ID for game_platform_link to use as game_platformID
        $gamePlatformID = $conn->lastInsertId();

        // Iterate over multiplayer modes submitted for this platform
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'single_' . $platformDetails['id']) ||
                str_starts_with($key, 'local_mp_' . $platformDetails['id']) ||
                str_starts_with($key, 'local_lan_' . $platformDetails['id']) ||
                str_starts_with($key, 'local_wir_' . $platformDetails['id']) ||
                str_starts_with($key, 'online_mp_' . $platformDetails['id']) ||
                str_starts_with($key, 'online_mmo_' . $platformDetails['id'])) {
                echo "Key: $key\n";

                // First, find the last underscore which separates the modeShort (and potential suffix) from the platform ID
                $lastUnderscorePos = strrpos($key, '_');
                if ($lastUnderscorePos !== false) {
                    // Extract everything before the last underscore
                    $modeWithSuffix = substr($key, 0, $lastUnderscorePos);

                    echo "Mode with Suffix: $modeWithSuffix\n";

                    // Now, check if there's a suffix like _min or _max and remove it
                    // This assumes that modeShorts do not naturally end in "_min" or "_max"
                    foreach (['_min', '_max'] as $suffix) {
                        if (str_ends_with($modeWithSuffix, $suffix)) {
                            $modeShort = substr($modeWithSuffix, 0, -strlen($suffix));
                            break;
                        } else {
                            $modeShort = $modeWithSuffix; // If no suffix, the modeShort remains as is
                        }
                    }

                    $modeID = getModeIDFromModeName($conn, $modeShort); // Retrieve the modeID based on modeShort
                    echo "Mode ID: $modeID\n";

                    if ($modeID !== null) { // Ensure the mode was checked/enabled
                        // Assuming min and max player counts are provided, retrieve them
                        $minPlayers = $_POST[$modeShort . '_min_' . $platformDetails['id']] ?? 0;
                        $maxPlayers = $_POST[$modeShort . '_max_' . $platformDetails['id']] ?? 0;

                        echo "Debugging SQL Statement:\n";
                        echo "Game Platform ID: $gamePlatformID\n";
                        echo "Mode ID: $modeID\n";
                        echo "Min Players: $minPlayers\n";
                        echo "Max Players: $maxPlayers\n";

                        // Insert into game_platform_player_link
                        $stmt = $conn->prepare("INSERT INTO game_platform_player_link (game_platformID, modeID, minPlayers, maxPlayers) VALUES (:gamePlatformID, :modeID, :minPlayers, :maxPlayers)");
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

function getModeIDFromModeName($conn, $modeShort) {
    $stmt = $conn->prepare("SELECT modeID FROM playermodes WHERE modeShort = :modeShort");
    $stmt->execute([':modeShort' => $modeShort]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
    return $result ? $result['modeID'] : null;
}

?>