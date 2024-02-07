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
    $conn->exec("USE $dbname"); // use database
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
    <title>List Games — NXGuardianDB</title>
    <link rel="stylesheet" href="./css/default_style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./icons/nxguardian.png">
</head>

<body>
<div class="page_wrap">
    <header>
        <nav class="navbar">
            <a href="./index.php">Start</a>
            <a href="">Search Games</a>
            <a href="list_games.php" class="active">List Games</a>
            <a href="./add_game.php">Add Game</a>
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
        <h1 class="page_h1">Games</h1>

        <div class="game-list">
            <?php
            // get all games with multiplayer modes
            $stmt = $conn->prepare("
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
                        echo "</ul></div></div>"; // Close the previous list and game div if not first
                    }
                    $lastGamePlatformID = $row['game_platformID'];
                    // Start a new game-platform div
                    echo "<div class='game game_platform_" . htmlspecialchars($row['game_platformID']) . "' tabindex='0'>";

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
                // Always output the current multiplayer feature
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


            // Close the last game-platform div
            if ($lastGamePlatformID !== null) {
                echo "</ul></div></div>";
            }

            ?>
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
<script src="./js/game_editor.js"></script>
</body>
</html>