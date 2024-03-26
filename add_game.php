<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';
include_once './util/validate.php';
include_once './util/utility_func.php';

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ./index.php");
    exit();
}

//-------------------- TitleDB mode --------------------

// check if url contains titledb mode (?mode=ns...) & game index (?index=0)
$titleDBMode = isset($_GET['mode']) ? $_GET['mode'] : '';
$gameIndex = isset($_GET['index']) ? $_GET['index'] : '';
$dir = isset($_GET['dir']) ? $_GET['dir'] : 1; // direction of game index (next or previous) -> 0 = previous

//filter mode to only allow a - z & game index to only allow numbers
$titleDBMode = preg_replace("/[^a-z]/", "", $titleDBMode);
$gameIndex = (int)preg_replace("/[^0-9]/", "", $gameIndex);
$dir = (int)substr(preg_replace("/[^0-1]/", "1", $dir), 0, 1);

$titleDBenabled = false;

if ($titleDBMode === 'nsall' || $titleDBMode === 'nsfp') {

    $titleDBenabled = true;

    $titleDBgames = null;
    $lastGame = false;

    if ($titleDBMode === 'nsall') {
        $titleDBgames = json_decode(file_get_contents('./titledb/nx_titledb_all.json'), true); // load all games from Nintendo Switch title database
    } else {
        $titleDBgames = json_decode(file_get_contents('./titledb/nx_titledb_fp.json'), true); // load first-party games from Nintendo Switch title database
    }

    // if file not found, redirect to fetch_titledb.php
    if ($titleDBgames === null) {
        header('Location: ./util/fetch_titledb.php?mode=' . htmlspecialchars($titleDBMode));
        exit();
    }

    // check if game index is within bounds
    if ($gameIndex >= count($titleDBgames)) {
        header('Location: ./index.php');
        exit();
    }
    // check if game index is the last game
    else if ($gameIndex === count($titleDBgames) - 1) {
        $lastGame = true;
    }

    $currentGame = $titleDBgames[$gameIndex];

    // check if game is already in database
    $isGameInDB = check_duplicate_game_entry($currentGame['title'], $currentGame['publisher'], $currentGame['releaseDate']);

    if ($isGameInDB !== false) {
        // load next game index
        if ($dir === 0) {
            header('Location: ./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex - 1));
        } else {
            header('Location: ./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex + 1));
        }
    }

    $nsPlatID = 14; // Nintendo Switch platform ID
}



//---------------- TitleDB mode end --------------------

template_header('Add Game', 'add');
?>
<div class="manage_game_container">
    <?php
    if ($titleDBenabled) {
        // load next game from Nintendo Switch title database when submitting the form, if there are more games
        if (!$lastGame) {
            echo '<form class="add_game_form" action="./util/validate_add.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex + 1) . '" method="post">'; // form to add a game in TitleDB mode
        } else {
            echo '<form class="add_game_form" action="./util/validate_add.php" method="post">'; // form to add last game in TitleDB mode -> no next game
        }
    } else {
        echo '<form class="add_game_form" action="./util/validate_add.php" method="post">'; // form to add a game in normal mode
    }
    ?>
        <fieldset class="basic_info_form">
            <legend>Game Information</legend>

            <div class="game_info_cont">
                <div class="game_info_field">
                    <label for="title">Game Title:</label>
                    <input type="text" class="win_dark_input" name="title" id="title"
                            <?php if ($titleDBenabled) {
                                    echo 'value="' . htmlspecialchars($currentGame['title']) . '"'; // set value to current game title
                                } ?>
                           required>
                </div>

                <!-- TODO: populate developer list from database -->
                <div class="game_info_field">
                    <label for="developer">Developer:</label>
                    <input list="developers" class="win_dark_input" name="developer" id="developer"
                           <?php if ($titleDBenabled) {
                                    echo 'value="' . htmlspecialchars($currentGame['publisher']) . '"'; // set value to current game developer
                                } ?>
                           required>
                </div>

                <div class="game_info_field">
                    <label for="release">Release Date:</label>
                    <input type="date" class="win_dark_input" name="release" id="release"
                           <?php if ($titleDBenabled) {
                                    echo 'value="' . htmlspecialchars($currentGame['releaseDate']) . '"'; // set value to current game release date
                                } ?>
                           required>
                </div>

                <div class="game_info_field">
                    <label for="imageLink">Image Link (1:1):</label>
                    <input type="url" class="win_dark_input" name="imageLink" id="imageLink"
                           <?php if ($titleDBenabled) {
                                    echo 'value="' . htmlspecialchars($currentGame['imageLink']) . '"'; // set value to current game image link
                                } ?>
                           required>
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
                                   id="game_id_' . $nsPlatID . '" value="'. $currentGame['storeID'] .'">
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
                                if ($row['modeShort'] === 'single') { // if modeID is 1 (Singleplayer)
                                    generateMPCheckboxes($row, true, $nsPlatID, -1, -1, true);
                                } else if ($row['modeShort'] === 'online_mp') { // if modeID is 5 (Online Multiplayer)
                                    if ($currentGame['numberOfPlayers'] > 1) {
                                        generateMPCheckboxes($row, true, $nsPlatID, $currentGame['numberOfPlayers'], 1, true);
                                    } else {
                                        generateMPCheckboxes($row, true, $nsPlatID);
                                    }
                                } else {
                                    generateMPCheckboxes($row, true, $nsPlatID);
                                }
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
            echo '<a class="control_btn" href="./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex - 1) . '&dir=0">< PREV</a>';
        }
    }
    echo '</div>';
    ?>
        <input type="submit" value="Add game" class="submit_button">
    <?php
    echo '<div class="control_btn_cont ctrl_btn_cont_right">';
    if ($titleDBenabled) {
        if (!$lastGame) {
            echo '<a class="control_btn" href="./add_game.php?mode=' . htmlspecialchars($titleDBMode) . '&index=' . htmlspecialchars($gameIndex + 1) . '">NEXT ></a>';
        }
    }
    echo '</div>';
    ?>
    </div>

    </form>
</div>
<?php
template_footer(["game_editor.js"]);

getErrorMsg();
?>