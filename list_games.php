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

            var_dump($_POST);

            // collect search criteria from form
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

            // debug output
            echo "<p>Search Criteria:</p>";
            echo "<p>Title: " . htmlspecialchars($title) . "</p>";
            echo "<p>Developer: " . htmlspecialchars($developer) . "</p>";
            echo "<p>Platforms: " . htmlspecialchars(implode(', ', $platforms)) . "</p>";

            // output multiplayer modes with player counts
            foreach ($multiplayerModesWithCounts as $mode => $counts) {
                echo "<p>Mode: " . htmlspecialchars($mode) . " - Min: " . htmlspecialchars($counts['min'] ?? 'N/A') . ", Max: " . htmlspecialchars($counts['max'] ?? 'N/A') . "</p>";
            }

            // get all games with multiplayer modes
            $stmt = $PDO->prepare("
            SELECT 
                g.gameName AS game_name,
                g.gameID as game_id,
                g.steamgridImageID AS steamgrid_image_id,
                d.devName AS developer,
                IFNULL(gpl.releaseDate, g.gameRelease) AS release_date,
                p.platformName AS platform,
                gpl.game_platformID,
                gpl.platformID,
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
            ORDER BY 
                g.gameName, p.platformName, pm.modeName DESC ;
        ");
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
                        echo "<a href='./delete_game.php?game_platformID=" . htmlspecialchars($row['game_platformID']) . "' class='delete_button' title='Delete Game-Platform Entry'>X Game Plat Entry</a>";
                        // button to delete game entry
                        echo "<a href='./delete_game.php?gameID=" . htmlspecialchars($row['game_id']) . "' class='delete_button' title='Delete Game'>X Game</a>";
                    echo "</div>";

                    // show steamgriddb image if available
                    echo "<div class='game_prev'>";
                    echo "<div class='game_image'>";
                    echo "<img src='https://cdn2.steamgriddb.com/thumb/" . htmlspecialchars($row['steamgrid_image_id']) . ".jpg' alt='Game Image'>";
                    echo "<img src='./img/platforms/" . htmlspecialchars($row['platformID']) . ".svg' class='platform_info_logo' alt='Platform Logo'/>";
                    echo "</div>";

                    echo "<h2>" . htmlspecialchars($row['game_name']) . "</h2>";
                    echo "</div>";

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