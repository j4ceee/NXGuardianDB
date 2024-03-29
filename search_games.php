<?php
require_once(__DIR__ . '/util/conn_db.php'); // include database connection file
require_once(__DIR__ . '/util/header_footer.php');
require_once(__DIR__ . '/util/utility_func.php');

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/index.php");
    exit();
}

template_header('Search Game', 'search');
?>
        <div class="manage_game_container">
            <form class="manage_game_form" action="./list_games.php" method="post" onsubmit="showSpinner()">
                <fieldset class="basic_info_form">
                    <legend>Game Information</legend>

                    <div class="game_info_cont">
                        <div class="game_info_field">
                            <label for="title">Game Title:</label>
                            <input type="text" class="win_dark_input" name="title" id="title">
                        </div>

                        <div class="game_info_field">
                            <label for="developer">Developer:</label>
                            <input list="developers" class="win_dark_input" name="developer" id="developer">

                            <?php //TODO: AJAX populate the list instead of loading all at once ?>
                            <datalist id="developers">
                                <?php
                                $query = "SELECT devName FROM developers ORDER BY devName"; // SQL statement to select all developers
                                $stmt = $PDO->prepare($query); // prepare SQL statement for execution
                                $stmt->execute(); // execute prepared statement
                                $developers = $stmt->fetchAll(PDO::FETCH_COLUMN, 0); // fetch all results from $stmt and store in $developers
                                // PDO::FETCH_COLUMN get each row & return as an array with column values
                                foreach ($developers as $developer) {
                                    echo '<option value="' . htmlspecialchars($developer) . '">'; // output each developer as an option in the datalist
                                }
                                ?>
                            </datalist>
                        </div>

                        <div class="game_info_field">
                            <label for="game_id">Game ID:</label>
                            <input type="text" class="win_dark_input" name="game_id" id="game_id">
                        </div>
                    </div>
                </fieldset>

                <fieldset class="platforms_form">
                    <legend>Platforms</legend>
                    <div class="platforms-container">
                        <?php
                        $sql = "SELECT * FROM platforms ORDER BY platformCategory, platformID"; // SQL statement to select all platforms
                        $stmt = $PDO->prepare($sql); // prepare SQL statement for execution
                        $stmt->execute(); // execute prepared statement
                        $results = $stmt->fetchAll(PDO::FETCH_ASSOC); // fetch all results from $stmt and store in $results
                        // PDO::FETCH_ASSOC get each row & return as an associative array with column names as keys

                        $platformsByCategory = []; // empty array to store platforms by category
                        // pairs of keys and values (here: keys = platformCategory, values = platforms)

                        // fetch each row from $results and store in $row
                        foreach($results as $row) { // fetch each single row from $stmt and store in $row

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

                <input type="submit" value="Search games" class="submit_button" onclick="showSpinner()">
            </form>
        </div>
<?php template_footer(["search_games.js"]); ?>