<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';

$PDO = getPDO(); // get PDO connection

if (!checkDBExists()) {
    header("Location: ./index.php");
    exit();
}
useDB();

template_header('Search Game', 'search');
?>
        <div class="manage_game_container">
            <form class="add_game_form" action="./list_games.php" method="post">
                <fieldset class="basic_info_form">
                    <legend>Game Information</legend>

                    <div class="game_info_cont">
                        <div class="game_info_field">
                            <label for="title">Game Title:</label>
                            <input type="text" class="win_dark_input" name="title" id="title">
                        </div>

                        <!-- TODO: populate developer list from database -->
                        <div class="game_info_field">
                            <label for="developer">Developer:</label>
                            <input list="developers" class="win_dark_input" name="developer" id="developer">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="platforms_form">
                    <legend>Platforms</legend>
                    <div class="platforms-container">
                        <?php
                        $sql = "SELECT * FROM platforms ORDER BY platformCategory, platformID"; // SQL statement to select all platforms
                        $stmt = $PDO->query($sql); // execute SQL statement using PDO ("query" sends SQL statement to MySQL server & returns results)

                        $platformsByCategory = []; // empty array to store platforms by category
                        // pairs of keys and values (here: keys = platformCategory, values = platforms)

                        // fetch each row from $stmt and store in $row
                        // PDO::FETCH_ASSOC get next row & return as an associative array with column names as keys
                        while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) { // fetch each single row from $stmt and store in $row

                            $platformsByCategory[$row['platformCategory']][] = $row; // add platform to $platformsByCategory array
                            // $row['platformCategory'] accesses the platformCategory column of the current $row
                            // -> accesses sub-array of $platformsByCategory with key $row['platformCategory'] (key (category) <-> array of all platforms of that category)
                            // [] appends $row to the sub-array of the category
                        }

                        // loop through each category in $platformsByCategory
                        // $category is the key, $platforms is the value
                        foreach ($platformsByCategory as $category => $platforms) {
                            echo '<div class="plat_category ' . htmlspecialchars($category) . '-cat">';
                            echo '<h3>' . htmlspecialchars($category) . '</h3>';

                            foreach ($platforms as $platform) {
                                echo '<div class="plat_list_entry">';
                                echo '<input type="checkbox" class="plat_list_check" id="platform' . htmlspecialchars($platform['platformID']) . '" name="platform' . htmlspecialchars($platform['platformID']) . '" value="' . htmlspecialchars($platform['platformID']) . '">';
                                // same as 'echo '<input type="checkbox" name="platforms" value="' . $platform['platformID'] . '">';' but with htmlspecialchars()
                                echo '<label for="platform' . htmlspecialchars($platform['platformID']) . '">'. htmlspecialchars($platform['platformName']) . '</label>';
                                //echo '<img class="plat_list_logo" src="./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg">';
                                echo '<div class="plat_list_logo" style="mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain; -webkit-mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain"> </div>';
                                echo '</div>';
                                echo "\r\n"; // line break
                            }

                            echo '</div>';
                        }
                        ?>
                    </div>
                </fieldset>

                <fieldset class="mp_specs_form">
                    <legend>Multiplayer Specifications</legend>

                    <div class="mp_info_search">
                                    <?php
                                    $sql = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                                    $stmt = $PDO->query($sql);

                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo '<div class="mp_feature_check_cont">';
                                        echo "\r\n"; // line break
                                        echo "\r\n"; // line break
                                        echo '<label class="mp_feature_label" for="' . htmlspecialchars($row['modeShort']) . '"><input type="checkbox" class="mp_feature_check" name="' . htmlspecialchars($row['modeShort']) . '" id="' . htmlspecialchars($row['modeShort']) . '"><span class="win_dark_check"></span>' . htmlspecialchars($row['modeName']) . '</label>';
                                        echo '</div>';
                                        echo "\r\n\r\n"; // line break

                                        if ($row['modeShort'] != 'single') {
                                            echo '<div class="mp_feature_count_cont">';
                                            echo '<input type="number" class="mp_feature_minPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_min" id="' . htmlspecialchars($row['modeShort']) . '_min" min="1" max="999" step="1">';
                                            echo "\r\n"; // line break
                                            echo "<span>-</span>";
                                            echo "\r\n"; // line break
                                            echo '<input type="number" class="mp_feature_maxPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_max" id="' . htmlspecialchars($row['modeShort']) . '_max" min="1" max="999" step="1">';
                                            echo '</div>';
                                        }
                                        echo "\r\n\r\n"; // line break
                                    }
                                    ?>
                    </div>
                </fieldset>

                <input type="submit" value="Search game" class="submit_button">
            </form>
        </div>
<?php template_footer("search_games.js"); ?>