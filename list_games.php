<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';

$PDO = getPDO(); // get PDO connection

if (!checkDBExists()) {
    header("Location: ./index.php");
    exit();
}
useDB();

template_header('List Games', 'list');
?>
    <h1 class="page_h1">Games</h1>

    <div class="game-list">
        <?php
        // TODO: add search function directly in the page (not part of exam)

        // get gameID via URL
        $gameID = isset($_GET['gameID']) ? (int)$_GET['gameID'] : null;

        // collect search criteria from form sent via POST
        $title = isset($_POST['title']) ? trim($_POST['title']) : null;
        $developer = isset($_POST['developer']) ? trim($_POST['developer']) : null;

        // collect platforms based on dynamic keys
        $platforms = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'platform')) { // checks if the key starts with 'platform'
                $platforms[] = $value;
            }
        }

        // Initialize an associative array for multiplayer modes with player counts
        $multiplayerModesWithCounts = [];

        // get all modeShort from database table playermodes
        $stmt = $PDO->prepare("SELECT modeShort FROM playermodes");
        $stmt->execute();
        $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // loop through each mode and check if it is selected
        foreach ($modes as $modeKey) {
            if (isset($_POST[$modeKey]) && $_POST[$modeKey] === 'on') { // mode is selected

                $multiplayerModesWithCounts[$modeKey] = array('mode' => $modeKey); // add mode to array

                $minKey = $modeKey . '_min'; // create the key for the min player count
                $maxKey = $modeKey . '_max'; // create the key for the max player count
                $minPlayers = isset($_POST[$minKey]) && $_POST[$minKey] !== '' ? (int)$_POST[$minKey] : null; // get the min player count
                $maxPlayers = isset($_POST[$maxKey]) && $_POST[$maxKey] !== '' ? (int)$_POST[$maxKey] : null; // get the max player count

                if ($minPlayers !== null) {
                    $multiplayerModesWithCounts[$modeKey]['min'] = $minPlayers;
                }

                if ($maxPlayers !== null) {
                    $multiplayerModesWithCounts[$modeKey]['max'] = $maxPlayers;
                }
            }
        }

        /*
        // debug output
        var_dump($_POST);

        echo "<p>Search Criteria:</p>";
        echo "<p>Title: " . htmlspecialchars($title) . "</p>";
        echo "<p>Developer: " . htmlspecialchars($developer) . "</p>";
        echo "<p>Platforms: " . htmlspecialchars(implode(', ', $platforms)) . "</p>";

        // output multiplayer modes with player counts
        foreach ($multiplayerModesWithCounts as $mode => $counts) {
            echo "<p>Mode: " . htmlspecialchars($mode) . " - Min: " . htmlspecialchars($counts['min'] ?? 'N/A') . ", Max: " . htmlspecialchars($counts['max'] ?? 'N/A') . "</p>";
        }
        */

        // get all games with multiplayer modes
        $query = "
            SELECT 
                g.gameName AS game_name,
                g.gameID as game_id,
                g.imageLink AS imageLink,
                d.devName AS developer,
                IFNULL(gpl.releaseDate, g.gameRelease) AS release_date,
                gpl.releaseDate AS platform_release_date,
                p.platformName AS platform,
                gpl.game_platformID,
                gpl.platformID,
                gpl.storeLink AS storeLink,
                pm.modeName AS multiplayer_mode,
                gppl.minPlayers AS min_players,
                gppl.maxPlayers AS max_players
            FROM 
                games g
            JOIN 
                developers d ON g.devID = d.devID
            JOIN 
                game_platform_link gpl ON g.gameID = gpl.gameID
            JOIN 
                platforms p ON gpl.platformID = p.platformID
            JOIN 
                game_platform_player_link gppl ON gpl.game_platformID = gppl.game_platformID
            JOIN 
                playermodes pm ON gppl.modeID = pm.modeID
            ";

        // WHERE 1=1 is a trick to always have a valid WHERE clause in a dynamic query
        $conditions = ['1=1'];
        $params = [];


        // check if gameID is set -> add to query and parameters
        if ($gameID !== null) {
            $conditions[] = "g.gameID = :gameID";
            $params[':gameID'] = $gameID;
        }
        else {

            // check if title is set and not empty -> add to query and parameters
            if ($title !== null && $title !== '') {
                $conditions[] = "MATCH(g.gameName) AGAINST (:title IN NATURAL LANGUAGE MODE)"; // LIKE -> search for partial matches
                $params[':title'] = $title;
            }

            // check if developer is set and not empty -> add to query and parameters
            if ($developer !== null && $developer !== '') {
                $conditions[] = "MATCH(d.devName) AGAINST (:developer IN NATURAL LANGUAGE MODE)"; // = -> exact match
                $params[':developer'] = $developer;
            }

            // check if platforms is not empty ->
            if (!empty($platforms)) {
                // create an array of placeholders for the platforms
                $placeholders = [];
                foreach ($platforms as $index => $platform) {
                    $placeholder = ":platform" . $index; // unique placeholder for each platform
                    $placeholders[] = $placeholder;
                    $params[$placeholder] = $platform; // add platform ID to parameters array
                }
                $conditions[] = "p.platformID IN (" . implode(',', $placeholders) . ")"; //
            }

            if (!empty($multiplayerModesWithCounts)) {
                $selectedModeCount = count($multiplayerModesWithCounts);

                // placeholder for each mode
                $modeSubquerries = [];

                foreach ($multiplayerModesWithCounts as $mode => $counts) {
                    $modeKey = ":mode_$mode";
                    $minPlayersKey = ":minPlayers_$mode";
                    $maxPlayersKey = ":maxPlayers_$mode";

                    // add mode and player count to parameters
                    $params[$modeKey] = $mode;
                    $params[$minPlayersKey] = $counts['min'] ?? ($counts['max'] ?? 9999); // default to 0 if not set
                    $params[$maxPlayersKey] = $counts['max'] ?? 9999; // no max set -> default to very high number

                    // create subquerry for each mode with player counts and add to array
                    $modeSubquerries[] = "EXISTS (
                                                    SELECT 1
                                                    FROM game_platform_player_link gppl
                                                    INNER JOIN playermodes pm ON gppl.modeID = pm.modeID
                                                    WHERE gppl.game_platformID = gpl.game_platformID
                                                    AND pm.modeShort = $modeKey
                                                    AND gppl.minPlayers <= $minPlayersKey
                                                    AND gppl.maxPlayers <= $maxPlayersKey
                                                )";
                    //TODO: fix min max player selection
                                                    // gppl.minPlayers >= $minPlayersKey -> only games with a higher or equal min player count
                                                    // gppl.maxPlayers <= $maxPlayersKey -> only games with a lower or equal max player count
                }

                // add subquerries to conditions
                if (!empty($modeSubquerries)) {
                    $conditions[] = implode(' AND ', $modeSubquerries);
                }
            }
        }

        $query .= " WHERE " . implode(' AND ', $conditions);
        $query .= " ORDER BY g.gameName, p.platformName, pm.modeName DESC";


        // debug output

        echo "Debug Query: " . htmlspecialchars($query);
        echo "<br><br><p></p><br>";
        echo "Debug Params: ";
        print_r($params);


        $stmt = $PDO->prepare($query);

        // Binding parameters
        foreach ($params as $key => $val) {
            $stmt->bindValue($key, $val);
        }





        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $lastGamePlatformID = null;
        foreach ($results as $row) {
            if ($row['game_platformID'] != $lastGamePlatformID) {
                if ($lastGamePlatformID !== null) {
                    echo "</ul></div></div>"; // close the previous list and game div if not first
                }
                $lastGamePlatformID = $row['game_platformID'];
                // start a new game-platform div
                echo "<div class='game game_platform_" . htmlspecialchars($row['game_platformID']) . "' tabindex='0'>";

                // delete buttons
                echo "<div class='game_delete'>";
                // button to delete game_platform entry
                echo "<a href='./util/delete_game.php?game_platformID=" . htmlspecialchars($row['game_platformID']) . "' class='delete_button' title='Delete Game-Platform Entry'>X Game Plat Entry</a>";
                // button to delete game entry
                echo "<a href='./util/delete_game.php?gameID=" . htmlspecialchars($row['game_id']) . "' class='delete_button' title='Delete Game'>X Game</a>";
                echo "</div>";

                // if store link is set, create a link to the store
                // else wrap in div
                if ($row['storeLink'] !== null && $row['storeLink'] !== '') {
                    echo "<a class='game_prev' href='" . $row['storeLink'] ."' target='_blank'>";
                } else {
                    echo "<div class='game_prev'>";
                }

                echo "<div class='game_image'>";
                echo "<img src='" . htmlspecialchars($row['imageLink']) . "' alt='Game Image'>";

                // show platform icon (colour if available, else b/w)
                echo "<img src='./img/platforms/" . htmlspecialchars($row['platformID']) . "_col.svg' class='platform_info_logo' alt='Platform Logo' onerror=\"this.onerror=null; this.src='./img/platforms/" . htmlspecialchars($row['platformID']) . ".svg'\"/>";
                echo "</div>";

                echo "<h2>" . htmlspecialchars($row['game_name']) . "</h2>";

                if ($row['storeLink'] !== null && $row['storeLink'] !== '') {
                    echo "</a>";
                } else {
                    echo "</div>";
                }

                echo "<div class='game_details'>";
                echo "<p>Developer: " . htmlspecialchars($row['developer']) . "</p>";
                echo "<p>Release Date: " . htmlspecialchars($row['release_date']) . "</p>";
                echo "<ul class='game_mp_features'>";

            }
            // always output the current multiplayer feature
            echo "<li>" . htmlspecialchars($row['multiplayer_mode']);

            // check if any player number 0 -> set to 1
            if ($row['min_players'] == 0) {
                $row['min_players'] = 1;
            }
            if ($row['max_players'] == 0) {
                $row['max_players'] = 1;
            }

            // output player numbers
            if ($row['min_players'] === $row['max_players']) {
                if ($row['min_players'] == 1) {
                    echo "</li>";
                } else {
                    echo " (" . htmlspecialchars($row['min_players']) . " players)</li>";
                }
            } else {
                echo " (" . htmlspecialchars($row['min_players']) . " - " . htmlspecialchars($row['max_players']) . " players)</li>";
            }
        }


        // close the last game-platform div
        if ($lastGamePlatformID !== null) {
            echo "</ul></div></div>";
        }

        ?>
    </div>
<?php template_footer(); ?>