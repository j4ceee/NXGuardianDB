<?php
/** @var PDO $conn */
/** @var string $dbname */
/** @var bool $failed */
include './util/conn_db.php'; // include database connection file
$db_setup = false; // initialize $db_setup to false

if (!$failed) {
    $stmt = $conn->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
    $stmt->execute(['dbname' => $dbname]); // execute statement with database name

    $result = $stmt->fetchAll(); // fetch all results and store in $result

    if (count($result) > 0) {
        $db_setup = true;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Start — NXGuardianDB</title>
    <link rel="stylesheet" href="./css/default_style.css">
    <link rel="icon" type="image/png" sizes="32x32" href="./icons/nxguardian.png">
</head>

<body>
<div class="page_wrap">
    <header>
        <nav class="navbar">
            <a href="./index.php" class="active">Start</a>
            <?php
            if ($db_setup) {
                echo '<a href="">Search Games</a>
                      <a href="./list_games.php">List Games</a>
                      <a href="./add_game.php">Add Game</a>';
            }
            ?>
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
        <?php
        if (!$failed) {
            $stmt = $conn->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
            $stmt->execute(['dbname' => $dbname]); // execute statement with database name

            $result = $stmt->fetchAll(); // fetch all results and store in $result

            if ($db_setup) {
                // Database exists, display tick SVG and message
                echo '<div class="db-status">
                <svg class="status-symbol" width="100%" height="100%" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" > <path d="M448.8,1098C429.601,1098 411.601,1090.8 398.402,1077.6L84.002,753.602C54.002,726 52.803,679.204 81.604,649.202C109.206,619.202 156.002,618.003 186.004,646.804L416.404,811.204C429.603,823.204 450.006,820.805 460.802,806.403L1006.8,132.003C1030.8,99.605 1077.6,92.401 1110,117.605C1142.4,141.605 1149.6,188.406 1124.4,220.805L507.6,1068.01C494.401,1084.81 475.202,1095.61 454.799,1096.81C452.396,1098.01 451.197,1098.01 448.799,1098.01L448.8,1098Z" style="fill:rgb(0,225,80);fill-rule:nonzero;"/>
                 </svg>
                <p class="status-text">Database is set up & running</p></div>';
            } else {
                // Database doesn't exist, display cross SVG and message
                echo '<a href="./util/setup_db.php"><div class="db-status">
                <svg class="status-symbol" width="100%" height="100%" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" >     
                    <g transform="matrix(0.813183,0,0,0.813183,114.261,114.269)">
                        <path d="M1194.6,0C1167.57,-27.027 902.71,189.19 600.01,486.49C291.9,189.19 32.44,-27.02 0.01,0C-27.017,32.434 189.2,291.89 486.5,600C189.2,902.7 -27.01,1167.57 0.01,1194.59C32.444,1221.62 291.9,1010.8 600.01,708.1C902.71,1010.8 1167.58,1221.61 1194.6,1194.59C1221.63,1167.56 1010.81,902.7 708.11,600C1010.81,291.89 1221.62,32.43 1194.6,0Z" style="fill:rgb(255,95,84);"/>
                    </g> 
                </svg>
                <p class="status-text">Database is not set up</p></div></a>';
            }
        } else {
            echo '<div class="db-status">
            <svg class="status-symbol" width="100%" height="100%" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" >     
                <g transform="matrix(0.813183,0,0,0.813183,114.261,114.269)">
                    <path d="M1194.6,0C1167.57,-27.027 902.71,189.19 600.01,486.49C291.9,189.19 32.44,-27.02 0.01,0C-27.017,32.434 189.2,291.89 486.5,600C189.2,902.7 -27.01,1167.57 0.01,1194.59C32.444,1221.62 291.9,1010.8 600.01,708.1C902.71,1010.8 1167.58,1221.61 1194.6,1194.59C1221.63,1167.56 1010.81,902.7 708.11,600C1010.81,291.89 1221.62,32.43 1194.6,0Z" style="fill:rgb(255,95,84);"/>
                </g> <
            /svg>
            <p class="status-text">Connection to Database failed</p></div>';
        }
        ?>
        <div class="start-container">
            <div class="title-container">
                <div class="hline1">
                    <h1 class="title">NXGuardian</h1>
                </div>
                <div class="hline2">
                    <h1 class="title">Game</h1>
                </div>
                <div class="hline3">
                    <h1 class="title">Database</h1>
                </div>
            </div>
            <?php
            if ($db_setup) {
                echo "
                <div class=\"menu-buttons\">
                    <button class=\"menu-btn\" onclick=\"window.location='./add_game.php'\">Add Game</button>
                    <button class=\"menu-btn\" onclick=\"window.location=''\">Search Games</button>
                </div>";
            }
            ?>
        </div>
    </main>
    <footer>
        <nav class="nav-bottom navbar">
            <a href="./index.php">Start</a>
            <a href="./index.php">Disclaimer</a>
            <?php
            if ($db_setup) {
                echo '<a href="">Search Games</a>
                      <a href="">Add Game</a>';
            }
            ?>
        </nav>
    </footer>
</div>
</body>
</html>