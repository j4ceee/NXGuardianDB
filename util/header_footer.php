<?php
include_once './util/conn_db.php'; // include database connection file

function template_header($title, $active, bool $showSpinner = false): void
{
    $dbConnection = new DBConnection();

    $index = '';
    $search = '';
    $list = '';
    $add = '';

    switch ($active) {
        case 'index':
            $index = 'class="active"';
            break;
        case 'search':
            $search = 'class="active"';
            break;
        case 'list':
            $list = 'class="active"';
            break;
        case 'add':
            $add = 'class="active"';
            break;
        default:
            break;
    }

    echo <<<EOT
    <!DOCTYPE html>
    <html lang="en">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>$title — NXGuardianDB</title>
        <link rel="stylesheet" href="./css/default_style.css">
        <link rel="icon" type="image/png" sizes="32x32" href="./icons/nxguardian.png">
    </head>
    <!-- Generated by template_header -->
    <body>
    <div class="page_wrap">
        <header>
            <nav class="navbar">
                <a href="./index.php" $index>Start</a>
    EOT;
    if ($dbConnection->checkDBSchema()) {
        echo <<<EOT
            <a href="./search_games.php" $search>Search Games</a>
            <a href="./list_games.php" $list onclick="showSpinner()">List Games</a>
            <a href="./add_game.php" $add>Add Game</a>
        EOT;
    }

    echo <<<EOT
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
EOT;

    if ($showSpinner) {
        $displaySpinner = 'display: flex';
    } else {
        $displaySpinner = 'display: none';
    }

        echo '<div class="loading_overlay" id="loading_overlay" style="'. $displaySpinner .'">';
    echo <<<EOT
            <div class="loading_container">
                <svg class="loading_spinner" width="1200pt" height="1200pt" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg">
                     <path class="loading_spinner_path" d="m550 150c0-27.613 22.387-50 50-50 276.14 0 500 223.86 500 500 0 135.88-54.266 259.18-142.19 349.25-19.289 19.758-50.945 20.145-70.703 0.85547-19.762-19.293-20.145-50.953-0.85547-70.707 70.43-72.148 113.75-170.66 113.75-279.39 0-220.91-179.09-400-400-400-27.613 0-50-22.387-50-50z" fill-rule="evenodd"/>
                </svg>
            </div>
        </div>  
        EOT;

}

function template_footer(array $scripts = null): void
{
    // add loading_spinner.js to scripts array
    $scripts[] = 'loading_spinner.js';

    $dbConnection = new DBConnection();

    echo <<<EOT
    <!-- Generated by template_footer -->
    </main>
    <footer>
        <nav class="nav-bottom navbar">
            <a href="./index.php">Start</a>
            <a href="./index.php">Disclaimer</a>
    EOT;
    if ($dbConnection->checkDBSchema()) {
        echo <<<EOT
            <a href="./search_games.php">Search Games</a>
            <a href="./list_games.php" onclick="showSpinner()">List Games</a>
            <a href="./add_game.php">Add Game</a>
        EOT;

    }
    echo <<<EOT
        </nav>
    </footer>
</div>
EOT;
    if ($scripts !== null) {
        for ($i = 0; $i < count($scripts); $i++) {
            echo '<script src="./js/' . $scripts[$i] . '"></script>';
        };
    }
    echo <<<EOT
</body>
</html>
EOT;
}