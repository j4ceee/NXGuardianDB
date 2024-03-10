<?php /** @noinspection CssUnknownTarget */
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
        // TODO: add search function directly in the page

        // get gameID via URL
        $gameID = isset($_GET['gameID']) ? (int)$_GET['gameID'] : null;

        // collect search criteria from form sent via POST
        $title = isset($_POST['title']) ? trim($_POST['title']) : null;
        $developer = isset($_POST['developer']) ? trim($_POST['developer']) : null;
        $storeID = isset($_POST['game_id']) ? trim($_POST['game_id']) : null;

        // collect platforms based on dynamic keys
        $platforms = [];
        foreach ($_POST as $key => $value) {
            if (str_starts_with($key, 'platform')) { // checks if the key starts with 'platform'
                $platforms[] = $value;
            }
        }

        // initialize an associative array for multiplayer modes with player counts
        $multiplayerModesWithCounts = [];

        // get all modeShort from database table playermodes
        $stmt = $PDO->prepare("SELECT modeShort FROM playermodes");
        $stmt->execute();
        $modes = $stmt->fetchAll(PDO::FETCH_COLUMN);

        // loop through each mode and check if it is selected
        foreach ($modes as $modeKey) {
            if (isset($_POST[$modeKey]) && $_POST[$modeKey] === 'on') { // mode is selected

                $multiplayerModesWithCounts[$modeKey] = array('mode' => $modeKey); // add mode to array

                $playerKey = $modeKey . '_players'; // create the key for the min player count
                $currPlayers = isset($_POST[$playerKey]) && $_POST[$playerKey] !== '' ? (int)$_POST[$playerKey] : null; // get the min player count

                $multiplayerModesWithCounts[$modeKey]['players'] = $currPlayers;
                // sets player count to null if not set or empty, else to the value from the form
            }
        }

        /*
        // debug output
        var_dump($_POST);

        echo "<p>Search Criteria:</p>";
        echo "<p>Title: " . htmlspecialchars($title) . "</p>";
        echo "<p>Developer: " . htmlspecialchars($developer) . "</p>";
        echo "<p>Store ID: " . htmlspecialchars($storeID) . "</p>";
        echo "<p>Platforms: " . htmlspecialchars(implode(', ', $platforms)) . "</p>";

        // output multiplayer modes with player counts
        foreach ($multiplayerModesWithCounts as $mode => $counts) {
            echo "<p>Mode: " . htmlspecialchars($mode) . " - Min: " . htmlspecialchars($counts['min'] ?? 'N/A') . ", Max: " . htmlspecialchars($counts['max'] ?? 'N/A') . "</p>";
        }
        */

        // get all games with all infos
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
                gpl.storeID AS storeID,
                pm.modeID as multiplayer_modeID,
                pm.modeName AS multiplayer_mode,
                pm.modeShort AS multiplayer_mode_short,
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
                $conditions[] = "g.gameName LIKE :title"; // LIKE -> search for partial matches
                $params[':title'] = "%" . $title . "%";
                //$conditions[] = "MATCH(g.gameName) AGAINST (:title IN BOOLEAN MODE)"; // LIKE -> search for partial matches
                //$params[':title'] = $title . "*";
            }

            // check if developer is set and not empty -> add to query and parameters
            if ($developer !== null && $developer !== '') {
                $conditions[] = "d.devName LIKE :developer"; // = -> exact match
                $params[':developer'] = "%" . $developer . "%";
                //$conditions[] = "MATCH(d.devName) AGAINST (:developer IN BOOLEAN MODE)"; //
                //$params[':developer'] = $developer . "*";
            }

            // check if storeID is set and not empty -> add to query and parameters
            if ($storeID !== null && $storeID !== '') {
                $conditions[] = "gpl.storeID = :storeID"; // = -> exact match
                $params[':storeID'] = $storeID;
            }

            // check if platforms is not empty ->
            if (!empty($platforms)) {
                // create an array of placeholders for the platforms
                $placeholders = [];
                foreach ($platforms as $index => $platform) {
                    $placeholder = ":platform" . $index; // unique placeholder for each platform (e.g. :platform0, :platform1, ...)
                    $placeholders[] = $placeholder; // add placeholder to array
                    $params[$placeholder] = $platform; // add platform ID to parameters array
                }
                $conditions[] = "p.platformID IN (" . implode(',', $placeholders) . ")"; // create a condition with all placeholders
                // e.g. p.platformID IN (:platform0, :platform1, :platform2, ...)
                // this will list all games that are available on at least one of the selected platforms
            }

            if (!empty($multiplayerModesWithCounts)) {
                $selectedModeCount = count($multiplayerModesWithCounts);

                // placeholder for each mode
                $modeSubqueries = [];

                foreach ($multiplayerModesWithCounts as $mode => $counts) {
                    $modeKey = ":mode_$mode";
                    $playersKey = ":players_$mode";

                    // add mode and player count to parameters
                    $params[$modeKey] = $mode;

                    if ($counts['players'] !== null) {
                        $params[$playersKey] = $counts['players']; // default to 0 if not set

                        // create subquery for each mode with player counts and add to array
                        $modeSubqueries[] = "EXISTS (
                                                    SELECT 1
                                                    FROM game_platform_player_link gppl
                                                    INNER JOIN playermodes pm ON gppl.modeID = pm.modeID
                                                    WHERE gppl.game_platformID = gpl.game_platformID
                                                    AND pm.modeShort = $modeKey
                                                    AND gppl.minPlayers <= $playersKey
                                                    AND gppl.maxPlayers >= $playersKey
                                                )";
                                                // check if player count is within the range of the mode, so:
                                                // minPlayers <= players <= maxPlayers
                    } else {
                        // create subquerry for each mode without player counts and add to array
                        $modeSubqueries[] = "EXISTS (
                                                    SELECT 1
                                                    FROM game_platform_player_link gppl
                                                    INNER JOIN playermodes pm ON gppl.modeID = pm.modeID
                                                    WHERE gppl.game_platformID = gpl.game_platformID
                                                    AND pm.modeShort = $modeKey
                                                )";
                    }
                }

                // add subquerries to conditions
                if (!empty($modeSubqueries)) {
                    $conditions[] = implode(' AND ', $modeSubqueries);
                }
            }
        }

        $query .= " WHERE " . implode(' AND ', $conditions);
        $query .= " ORDER BY g.gameName, p.platformName, pm.modeID ASC";


        // debug output
        /*
        echo "Debug Query: " . htmlspecialchars($query);
        echo "<br><br><p></p><br>";
        echo "Debug Params: ";
        print_r($params);
        */

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

                // edit button
                echo "<a href='./edit_game.php?gameID=" . htmlspecialchars($row['game_id']) . "' class='edit_button' title='Edit " . htmlspecialchars($row['game_name']) . "'>
                        <img class='edit_icon' src='./icons/noun-edit-1047822-grey.svg' alt='Edit " . htmlspecialchars($row['game_name']) . "'>
                      </a>";

                // delete buttons
                echo "<div class='game_delete'>";
                // button to delete game_platform entry
                /*
                echo "<a href='./util/delete_game.php?game_platformID=" . htmlspecialchars($row['game_platformID']) . "' class='delete_button' title='Delete " . htmlspecialchars($row['platform']) . " version'>
                        <img class='trash_icon' src='./icons/noun-trash-2025467-grey.svg' alt='Delete " . htmlspecialchars($row['platform']) . " version'>
                        <img class='trash_gpl_icon' src='./img/platforms/" . htmlspecialchars($row['platformID']) . ".svg' class='trash_game_icon' alt='Platform Logo'/>
                      </a>";
                */
                // button to delete game
                echo "<a href='./util/delete_game.php?gameID=" . htmlspecialchars($row['game_id']) . "' class='delete_button' title='Delete " . htmlspecialchars($row['game_name']) . "'>
                      <img class='trash_icon' src='./icons/noun-trash-2025467-grey.svg' alt='Delete " . htmlspecialchars($row['game_name']) . "'>";
                // echo "<img class='trash_game_icon' src='" . htmlspecialchars($row['imageLink']) . "' alt='Game Image'/>";
                echo "</a>";
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

                // show platform icon (in colour if available, else b/w)
                echo "<img src='./img/platforms/" . htmlspecialchars($row['platformID']) . "_col.svg' class='platform_info_logo' alt='Platform Logo' onerror=\"this.onerror=null; this.src='./img/platforms/" . htmlspecialchars($row['platformID']) . ".svg'\"/>";
                echo "</div>";

                echo "<h2>" . htmlspecialchars($row['game_name']) . "</h2>";

                // close the link or div
                if ($row['storeLink'] !== null && $row['storeLink'] !== '') {
                    echo "</a>";
                } else {
                    echo "</div>";
                }

                // create a div for the game details
                echo "<div class='game_details'>";
                echo "<div><p class='game_list_info game_list_cat'>Developer:</p><p class='game_list_info game_list_det'>" . htmlspecialchars($row['developer']) . "</p></div>";
                echo "<div><p class='game_list_info game_list_cat'>Release Date:</p><p class='game_list_info game_list_det'>" . htmlspecialchars($row['release_date']) . "</p></div>";
                // game id of platform release
                if ($row['storeID'] !== null) {
                    echo "<div><p class='game_list_info game_list_cat'>Game ID:</p><p class='game_list_info game_list_det game_list_cat'>" . htmlspecialchars($row['storeID']) . "</p></div>";
                }
                echo "<ul class='game_mp_features'>";

            }
            // always output the current multiplayer feature
            echo "<li> 
                  <div class=\"mp_mode_logo\" style=\"mask: url(./icons/modes/modes_" . htmlspecialchars($row['multiplayer_mode_short']) . ".svg) no-repeat center / contain; -webkit-mask: url(./icons/modes/modes_" . htmlspecialchars($row['multiplayer_mode_short']) . ".svg) no-repeat center / contain\"></div> 
                  <p class='game_list_info game_list_cat game_list_mode'>" . htmlspecialchars($row['multiplayer_mode']);

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
                    echo "<p class='game_list_info game_list_det'>" . htmlspecialchars($row['min_players']) . " players</p></li>";
                }
            } else {
                echo "<p class='game_list_info game_list_det'>" . htmlspecialchars($row['min_players']) . " - " . htmlspecialchars($row['max_players']) . " players</p></li>";
            }
        }


        // close the last game-platform div
        if ($lastGamePlatformID !== null) {
            echo "</ul></div></div>";
        }

        ?>
    </div>
<?php template_footer(); ?>