<?php
include_once './util/conn_db.php'; // include database connection file
include_once './util/header_footer.php';
include_once './util/utility_func.php';

$PDO = getPDO(); // get PDO connection

if (!checkDBExists() || !isset($_GET['gameID'])) {
    header("Location: ./index.php");
    exit();
}
useDB();

// get gameID via URL
$gameID = (int)$_GET['gameID'];

$query = "
            SELECT 
                g.gameName AS game_name,
                g.gameID as game_id,
                g.imageLink AS imageLink,
                d.devName AS developer,
                g.gameRelease AS release_date
            FROM 
                games g
            JOIN 
                developers d ON g.devID = d.devID
            WHERE
                g.gameID = :gameID
            ";

$stmt = $PDO->prepare($query);
$stmt->bindParam(':gameID', $gameID);
$stmt->execute();
$game = $stmt->fetchAll(PDO::FETCH_ASSOC);


template_header('Edit Game', null);
?>
<div class="manage_game_container">
    <form class="add_game_form" action="./util/validate_update.php" method="post" autocomplete="off">
        <fieldset class="basic_info_form">
            <legend>Game Information</legend>

            <?php // var_dump($game);?>

            <div class="game_info_cont">
                <div class="game_info_field">
                    <label for="title">Game Title:</label>
                    <input type="text" class="win_dark_input" name="title" id="title" value="<?=$game[0]["game_name"]?>" required>
                </div>

                <div class="game_info_field">
                    <label for="developer">Developer:</label>
                    <input list="developers" class="win_dark_input" name="developer" id="developer" value="<?=$game[0]["developer"]?>" required>
                </div>

                <div class="game_info_field">
                    <label for="release">Release Date:</label>
                    <input type="date" class="win_dark_input" name="release" id="release" value="<?=$game[0]["release_date"]?>" required>
                </div>

                <div class="game_info_field">
                    <label for="imageLink">Image Link (1:1):</label>
                    <input type="url" class="win_dark_input" name="imageLink" id="imageLink" value="<?=$game[0]["imageLink"]?>" required>
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

                $query = "
                    SELECT
                        platformID
                    FROM
                        game_platform_link
                    WHERE
                        gameID = :gameID
                ";

                $stmt = $PDO->prepare($query);
                $stmt->bindParam(':gameID', $gameID);
                $stmt->execute();
                $platforms = $stmt->fetchAll(PDO::FETCH_COLUMN, 0);

                // loop through each category in $platformsByCategory
                // $category is the key, $platforms is the value
                createGamePlatformSelection($platformsByCategory, $platforms);
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
                            $sql = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                            $stmt = $PDO->query($sql);

                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
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

                <?php
                // for each platform the game is available on, create a platform specification fieldset (with platform info from game_platform_link && multiplayer info from game_platform_player_link)
                $query = "
                    SELECT
                        gpl.platformID AS platformID,
                        gpl.storeLink AS storeLink,
                        gpl.releaseDate AS platform_release_date,
                        p.platformName AS platformName
                    FROM
                        game_platform_link gpl
                    JOIN 
                        platforms p ON gpl.platformID = p.platformID
                    WHERE
                        gpl.gameID = :gameID";

                $stmt = $PDO->prepare($query);
                $stmt->bindParam(':gameID', $gameID);
                $stmt->execute();
                $platforms = $stmt->fetchAll(PDO::FETCH_ASSOC);

                // echo '<pre>'; var_dump($platforms); echo '</pre>';

                foreach ($platforms as $platform) {
                    $platID = $platform['platformID'];
                    $platName = $platform['platformName'];
                    $platStoreLink = $platform['storeLink'];
                    $platReleaseDate = $platform['platform_release_date'];
                    echo <<<EOT
                        <fieldset class="platform_info info_$platID">
                        <legend><img src="./img/platforms/$platID.svg" class="platform_info_logo" alt="Platform Logo"/>$platName
                            Specifications
                        </legend>

                        <div class="platform_info_field">
                            <label for="store_link_$platID">Store Link:</label>
                            <input type="url" class="win_dark_input" name="store_link_$platID" id="store_link_$platID" value="$platStoreLink">
                        </div>

                        <div class="platform_info_field">
                            <label for="release_plat_$platID">Release Date:</label>
                            <input type="date" class="win_dark_input" name="release_plat_$platID"
                                   id="release_plat_$platID" value="$platReleaseDate">
                        </div>

                        <fieldset class="multiplayer_info mp_info_$platID">
                            <legend>Multiplayer Functionality</legend>
                        EOT;

                            $query = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                            $stmt = $PDO->prepare($query);
                            $stmt->execute();
                            $playermodes = $stmt->fetchAll(PDO::FETCH_ASSOC);


                            foreach ($playermodes as $row) {

                                /*
                                // set multiplayer mode checkbox to checked if it exists in game_platform_player_link
                                // set min/max player count to values from game_platform_player_link
                                $query = "
                                    SELECT
                                        minPlayers,
                                        maxPlayers
                                    FROM
                                        game_platform_player_link
                                    JOIN

                                    WHERE
                                        gameID = :gameID
                                    AND
                                        platformID = :platformID
                                    AND
                                        modeID = :modeID";
                                $stmt = $PDO->prepare($query);
                                $stmt->bindParam(':gameID', $gameID);
                                $stmt->bindParam(':platformID', $platID);
                                $stmt->bindParam(':modeID', $row['modeID']);
                                $stmt->execute();
                                $mpData = $stmt->fetch(PDO::FETCH_ASSOC);
                                */
                                $mpData = null;
                                // check if the multiplayer mode exists in the fetched data
                                $isChecked = $mpData ? 'checked' : '';
                                $minPlayers = $mpData['minPlayers'] ?? '';
                                $maxPlayers = $mpData['maxPlayers'] ?? '';

                                echo '<div class="mp_feature_check_cont">';
                                echo "\r\n"; // line break
                                echo "\r\n"; // line break
                                echo '<label class="mp_feature_label" for="' . htmlspecialchars($row['modeShort']) . '_' .htmlspecialchars($platID) . '"><input type="checkbox" class="mp_feature_check" name="' . htmlspecialchars($row['modeShort']) . '_' .htmlspecialchars($platID) . '" id="' . htmlspecialchars($row['modeShort']) . '_' .htmlspecialchars($platID) . '" ' . $isChecked . '><span class="win_dark_check"></span>' . htmlspecialchars($row['modeName']) . '</label>';
                                echo '</div>';
                                echo "\r\n\r\n"; // line break

                                if ($row['modeShort'] != 'single') {
                                    echo '<div class="mp_feature_count_cont">';
                                    echo '<input type="number" class="mp_feature_minPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_min_' .htmlspecialchars($platID) . '" id="' . htmlspecialchars($row['modeShort']) . '_min_' .htmlspecialchars($platID) . '" min="1" max="999" step="1" value="' . $minPlayers . '">';
                                    echo "\r\n"; // line break
                                    echo "<span>-</span>";
                                    echo "\r\n"; // line break
                                    echo '<input type="number" class="mp_feature_maxPlayers win_dark_input" name="' . htmlspecialchars($row['modeShort']) . '_max_' .htmlspecialchars($platID) . '" id="' . htmlspecialchars($row['modeShort']) . '_max_' .htmlspecialchars($platID) . '" min="1" max="999" step="1" value="' . $maxPlayers . '">';
                                    echo '</div>';
                                }
                                echo "\r\n\r\n"; // line break
                            }
                    echo <<<EOT
                        </fieldset>
                    </fieldset>
                    EOT;
                }
                ?>
            </div>
        </fieldset>

        <input type="submit" value="Update game" class="submit_button">
    </form>
</div>
<?php template_footer("game_editor.js"); ?>