<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';
include_once './util/utility_func.php';

$PDO = getPDO(); // get PDO connection

if (!checkDBExists()) {
    header("Location: ./index.php");
    exit();
}
useDB();

template_header('Add Game', 'add');
?>
<div class="manage_game_container">
    <form class="add_game_form" action="./util/validate_add.php" method="post">
        <fieldset class="basic_info_form">
            <legend>Game Information</legend>

            <div class="game_info_cont">
                <div class="game_info_field">
                    <label for="title">Game Title:</label>
                    <input type="text" class="win_dark_input" name="title" id="title" required>
                </div>

                <!-- TODO: populate developer list from database -->
                <div class="game_info_field">
                    <label for="developer">Developer:</label>
                    <input list="developers" class="win_dark_input" name="developer" id="developer" required>
                </div>

                <div class="game_info_field">
                    <label for="release">Release Date:</label>
                    <input type="date" class="win_dark_input" name="release" id="release" required>
                </div>

                <!--
                <div class="game_info_field">
                    <label for="sgdb-id">SteamGridDB ID:</label>
                    <input type="number" class="win_dark_input" name="sgdb-id" id="sgdb-id" min="0" max="999999" step="1" required>
                </div>
                -->

                <div class="game_info_field">
                    <label for="imageLink">Image Link (1:1):</label>
                    <input type="url" class="win_dark_input" name="imageLink" id="imageLink" required>
                </div>

                <div class="game_info_empty"></div>
                <div class="game_info_empty"></div>
                <div class="game_info_empty"></div>
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
                createGamePlatformSelection($platformsByCategory);
                ?>
            </div>
        </fieldset>

        <fieldset class="platform_specs_form">
            <legend>Platform Specifications</legend>

            <div class="platform_spec_cont">
                <template id="platform_template">
                    <fieldset class="platform_info info_[platID]">
                        <legend><img src="./img/platforms/[platID].svg" class="platform_info_logo" alt="Platform Logo"/>[platName]
                            Specifications
                        </legend>

                        <div class="platform_info_field">
                            <label for="store_link_[platID]">Store Link:</label>
                            <input type="url" class="win_dark_input" name="store_link_[platID]"
                                   id="store_link_[platID]">
                        </div>

                        <div class="platform_info_field">
                            <label for="release_plat_[platID]">Release Date:</label>
                            <input type="date" class="win_dark_input" name="release_plat_[platID]"
                                   id="release_plat_[platID]">
                        </div>

                        <fieldset class="multiplayer_info mp_info_[platID]">
                            <legend>Multiplayer Functionality</legend>

                            <?php
                            $query = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                            $stmt = $PDO->prepare($query);
                            $stmt->execute();
                            $playermodes = $stmt->fetchAll(PDO::FETCH_ASSOC);


                            foreach ($playermodes as $row) {
                                echo '<div class="mp_feature_check_cont">';
                                //echo '<input type="checkbox" class="mp_feature_check" name="' . htmlspecialchars($row['modeShort']) . '[platID]" id="' . htmlspecialchars($row['modeShort']) . '[platID]" required>';
                                echo "\r\n"; // line break
                                //echo '<span class="win_dark_check"></span>';
                                echo "\r\n"; // line break
                                //echo '<label class="mp_feature_label" for="' . htmlspecialchars($row['modeShort']) . '[platID]">' . htmlspecialchars($row['modeName']) . '</label>';
                                echo '<label class="mp_feature_label" for="' . htmlspecialchars($row['modeShort']) . '_[platID]"><input type="checkbox" class="mp_feature_check" name="' . htmlspecialchars($row['modeShort']) . '_[platID]" id="' . htmlspecialchars($row['modeShort']) . '_[platID]"><span class="win_dark_check"></span>' . htmlspecialchars($row['modeName']) . '</label>';
                                echo '</div>';
                                echo "\r\n\r\n"; // line break

                                if ($row['modeShort'] != 'single') {
                                    echo '<div class="mp_feature_count_cont">';
                                    echo '<input type="number" class="mp_feature_minPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_min_[platID]" id="' . htmlspecialchars($row['modeShort']) . '_min_[platID]" min="1" max="999" step="1">';
                                    echo "\r\n"; // line break
                                    echo "<span>-</span>";
                                    echo "\r\n"; // line break
                                    echo '<input type="number" class="mp_feature_maxPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_max_[platID]" id="' . htmlspecialchars($row['modeShort']) . '_max_[platID]" min="1" max="999" step="1">';
                                    echo '</div>';
                                }
                                echo "\r\n\r\n"; // line break
                            }
                            ?>
                        </fieldset>
                    </fieldset>
                </template>
            </div>
        </fieldset>

        <input type="submit" value="Add game" class="submit_button">
    </form>
</div>
<?php template_footer("game_editor.js"); ?>