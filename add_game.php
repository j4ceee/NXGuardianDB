<?php
/** @var PDO $conn */
/** @var string $dbname */
/** @var bool $failed */
include './util/conn_db.php'; // include database connection file

if (!$failed) {
    $stmt = $conn->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
    $stmt->execute(['dbname' => $dbname]); // execute statement with database name

    $result = $stmt->fetchAll(); // fetch all results and store in $result

    if (count($result) == 0) {
        // Redirect back to index.php
        header("Location: ../index.php");
        exit();
    }
} else {
    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Game — NXGuardianDB</title>
    <link rel="stylesheet" href="./css/default_style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./icons/nxguardian.png">
</head>

<body>
<div class="page_wrap">
    <header>
        <nav class="navbar">
            <a href="./index.php">Start</a>
            <a href="">Search Games</a>
            <a href="./add_game.php" class="active">Add Game</a>
        </nav>

        <div class="logo-header">

            <a href="./index.php">

                <svg class="logo" width="100%" height="100%" viewBox="0 0 622 650" xmlns="http://www.w3.org/2000/svg"
                     xmlns:xlink="http://www.w3.org/1999/xlink"
                     style="fill-rule:evenodd;clip-rule:evenodd;stroke-miterlimit:10;">
                    <g>
                        <g transform="matrix(1.00595,0,0,1.00595,-1.85001,-1.99511)">
                            <g transform="matrix(0.800715,0,0,0.800715,46.9196,51.496)">
                                <path d="M235.44,196.329C269.728,196.329 297.515,224.122 297.515,258.404C297.515,292.691 269.725,320.479 235.44,320.479C201.158,320.479 173.365,292.689 173.365,258.404C173.365,224.122 201.158,196.329 235.44,196.329Z"
                                      style="fill:rgb(255,95,85);"/>
                            </g>
                            <g transform="matrix(0.969287,0,0,0.969287,7.23112,12.6656)">
                                <rect x="189.289" y="393.879" width="92.303" height="37.009"
                                      style="fill:rgb(2,197,229);"/>
                            </g>
                            <g transform="matrix(0.800684,0,0,0.800684,77.0472,82.1944)">
                                <path d="M386.56,350.306C420.842,350.306 448.635,378.096 448.635,412.381C448.635,446.663 420.842,474.456 386.56,474.456C352.272,474.456 324.48,446.663 324.48,412.381C324.48,378.094 352.272,350.306 386.56,350.306Z"
                                      style="fill:rgb(255,95,85);"/>
                            </g>
                            <g transform="matrix(0.800684,0,0,0.800684,77.0472,51.5042)">
                                <path d="M386.56,196.329C420.842,196.329 448.635,224.122 448.635,258.404C448.635,292.691 420.842,320.479 386.56,320.479C352.272,320.479 324.48,292.689 324.48,258.404C324.48,224.122 352.272,196.329 386.56,196.329Z"
                                      style="fill:rgb(255,95,85);"/>
                            </g>
                        </g>
                        <g transform="matrix(1,0,0,1,8,-3)">
                            <path d="M192.742,568.03L192.478,639.778C76.059,575.998 0.814,435.203 0.814,281.041L0.814,156.669C0.814,135.918 15.034,118.86 32.546,118.479C131.985,116.95 194.703,37.007 194.703,37.007L194.4,119.166C166.067,142.208 119.143,171.688 63.213,179.833C57.906,180.598 53.873,185.944 53.873,192.437L53.873,281.041C53.873,399.815 107.417,509.215 192.742,568.03Z"
                                  style="fill:rgb(2,197,229);stroke:rgb(2,197,229);stroke-width:13.98px;"/>
                        </g>
                        <g transform="matrix(-0.974012,0,0,0.995183,614.169,-14.4133)">
                            <path d="M192.742,568.03L192.478,639.778C76.059,575.998 0.814,435.203 0.814,281.041L0.814,156.669C0.814,135.918 15.034,118.86 32.546,118.479C131.985,116.95 194.067,37.897 194.703,37.007L194.4,119.166C166.067,142.208 119.143,171.688 63.213,179.833C57.906,180.598 53.873,185.944 53.873,192.437L53.873,281.041C53.873,399.815 107.417,509.215 192.742,568.03Z"
                                  style="fill:rgb(255,95,85);stroke:rgb(255,95,85);stroke-width:14.22px;"/>
                        </g>
                    </g>
                </svg>

            </a>

        </div>
    </header>

    <main>
        <div class="manage_game_container">
            <form class="text_container" action="validate.php" method="post">
                <fieldset class="input_form">
                    <legend>Game Information</legend>

                    <label for="title">Title:</label>
                    <input type="text" name="title" id="title" required>

                    <label for="developer">Developer:</label>
                    <input list="developers" name="developer" id="developer" required>

                    <label for="release">Release Date:</label>
                    <input type="date" name="release" id="release" required>

                    <label for="sgdb-id">SteamGridDB ID:</label>
                    <input type="number" name="sgdb-id" id="sgdb-id" min="0" max="999999" step="1" required>

                    <label for="sgdb-grid-id">SteamGridDB Grid ID:</label>
                    <input type="number" name="sgdb-grid-id" id="sgdb-grid-id" min="0" max="999999" step="1">
                </fieldset>

                <fieldset>
                    <legend>Platforms</legend>
                    <div class="platforms-container">
                        <!-- Dynamically generate platform checkboxes with JavaScript -->
                        <?php
                        $conn->exec("USE $dbname"); // use database
                        $sql = "SELECT * FROM platforms ORDER BY platformCategory, platformID"; // SQL statement to select all platforms
                        $stmt = $conn->query($sql); // execute SQL statement using PDO ("query" sends SQL statement to MySQL server & returns results)

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
                                echo '<img class="plat_list_logo" src="./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg">';
                                echo '</div>';
                            }

                            echo '</div>';
                        }
                        ?>
                    </div>
                </fieldset>


                <input type="submit" value="Add game" class="submit_button">
            </form>
        </div>
    </main>
    <footer>
        <nav class="nav-bottom navbar">
            <a href="./index.php">Start</a>
            <a href="./index.php">Disclaimer</a>
            <a href="">Search Games</a>
            <a href="./add_game.php">Add Game</a>
        </nav>
    </footer>
</div>
</body>
</html>