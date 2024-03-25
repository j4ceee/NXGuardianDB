<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';
include_once './util/validate.php';
include_once './util/utility_func.php';

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

//-------------------- TitleDB mode --------------------

// check if url contains titledb mode (?mode=ns...) & game index (?index=0)
$titleDBMode = isset($_GET['mode']) ? $_GET['mode'] : '';
$gameIndex = isset($_GET['index']) ? $_GET['index'] : '';

//filter mode to only allow a - z & game index to only allow numbers
$titleDBMode = preg_replace("/[^a-z]/", "", $titleDBMode);
$gameIndex = preg_replace("/[^0-9]/", "", $gameIndex);

$titleDBenabled = false;

if ($titleDBMode === 'nsall' || $titleDBMode === 'nsfp') {
    $titleDBenabled = true;
}

$nsPlatID = 14; // Nintendo Switch platform ID

//---------------- TitleDB mode end --------------------

template_header('Add Game', 'add', true);
?>
<div class="manage_game_container">
    <?php
    if ($titleDBenabled) {
        // load next game from Nintendo Switch title database when submitting the form
        echo '<form class="add_game_form" action="./util/validate_add.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex + 1) . '" method="post">'; // form to add a game in TitleDB mode
        // TODO: prevent form from loading next game when the end of the list is reached (-> in JS)
    } else {
        echo '<form class="add_game_form" action="./util/validate_add.php" method="post">'; // form to add a game in normal mode
    }
    ?>
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

                if ($titleDBenabled) {
                    $previousPlatforms = [$nsPlatID];
                } else {
                    $previousPlatforms = null;
                }
                // generate checkboxes for platforms -> check all platforms that are already selected (array $previousPlatforms)
                createGamePlatformSelection($platformsByCategory, $previousPlatforms);
                ?>
            </div>
        </fieldset>

        <fieldset class="platform_specs_form">
            <legend>Platform Specifications</legend>

            <div class="platform_spec_cont">
                <template id="platform_template">
                    <fieldset class="platform_info info_[platID]">
                        <legend><!--suppress HtmlUnknownTarget -->
                            <img src="./img/platforms/[platID].svg" class="platform_info_logo" alt="Platform Logo"/>[platName]
                            Specifications
                        </legend>

                        <div class="platform_info_field">
                            <label for="store_link_[platID]">Store Link:</label>
                            <input type="url" class="win_dark_input" name="store_link_[platID]"
                                   id="store_link_[platID]">
                        </div>

                        <div class="platform_info_field">
                            <label for="game_id_[platID]">Game ID:</label>
                            <input type="text" class="win_dark_input" name="game_id_[platID]"
                                   id="game_id_[platID]">
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
                                generateMPCheckboxes($row, true); // line break
                            }
                            ?>
                        </fieldset>
                    </fieldset>
                </template>

                <?php
                if ($titleDBenabled) {
                    echo '<fieldset class="platform_info info_' . $nsPlatID . '">
                        <legend><!--suppress HtmlUnknownTarget -->
                            <img src="./img/platforms/' . $nsPlatID . '.svg" class="platform_info_logo" alt="Platform Logo"/>Nintendo Switch
                        </legend>

                        <div class="platform_info_field">
                            <label for="store_link_' . $nsPlatID . '">Store Link:</label>
                            <input type="url" class="win_dark_input" name="store_link_' . $nsPlatID . '"
                                   id="store_link_' . $nsPlatID . '">
                        </div>

                        <div class="platform_info_field">
                            <label for="game_id_' . $nsPlatID . '">Game ID:</label>
                            <input type="text" class="win_dark_input" name="game_id_' . $nsPlatID . '"
                                   id="game_id_' . $nsPlatID . '">
                        </div>

                        <div class="platform_info_field">
                            <label for="release_plat_' . $nsPlatID . '">Release Date:</label>
                            <input type="date" class="win_dark_input" name="release_plat_' . $nsPlatID . '"
                                   id="release_plat_' . $nsPlatID . '">
                        </div>

                        <fieldset class="multiplayer_info mp_info_' . $nsPlatID . '">
                            <legend>Multiplayer Functionality</legend>';

                            $query = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                            $stmt = $PDO->prepare($query);
                            $stmt->execute();
                            $playermodes = $stmt->fetchAll(PDO::FETCH_ASSOC);

                            foreach ($playermodes as $row) {
                                generateMPCheckboxes($row, true, $nsPlatID); // line break
                            }
                            echo '
                        </fieldset>
                    </fieldset>';
                }
                ?>

            </div>
        </fieldset>

    <div class="add_game_form_control">
    <?php
    echo '<div class="control_btn_cont ctrl_btn_cont_left">';
    if ($titleDBenabled) {
        if ($gameIndex > 0) {
            echo '<a class="control_btn" href="./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex - 1) . '">< PREV</a>';
        }
    }
    echo '</div>';
    ?>
        <input type="submit" value="Add game" class="submit_button">
    <?php
    echo '<div class="control_btn_cont ctrl_btn_cont_right">';
    if ($titleDBenabled) {
        echo '<a class="control_btn" href="./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex + 1) . '">NEXT ></a>';
    }
    echo '</div>';
    ?>
    </div>

    </form>
</div>
<?php


if ($titleDBenabled && $gameIndex !== '') {
    // nsall = all games, nsfp = first party games from Nintendo Switch title database
    template_footer(["game_editor.js", "load_titledb.js"]);
} else {
    template_footer(["game_editor.js"]);
}

getErrorMsg();
?>