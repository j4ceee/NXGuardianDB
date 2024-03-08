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
                        /**
                         * @param array $platformsByCategory
                         * @return void
                         */

                        createGamePlatformSelection($platformsByCategory);
                        ?>
                    </div>
                </fieldset>

                <fieldset class="mp_specs_form">
                    <legend>Multiplayer Specifications</legend>

                    <div class="mp_info_search">
                                    <?php
                                    $query = "SELECT * FROM playermodes ORDER BY modeID"; // SQL statement to select all platforms
                                    $stmt = $PDO->prepare($query);
                                    $stmt->execute();
                                    $playermodes = $stmt->fetchAll(PDO::FETCH_ASSOC);


                                    foreach ($playermodes as $row) {
                                        generateMPCheckboxes($row, false);
                                    }
                                    ?>
                    </div>
                </fieldset>

                <input type="submit" value="Search games" class="submit_button">
            </form>
        </div>
<?php template_footer("search_games.js"); ?>