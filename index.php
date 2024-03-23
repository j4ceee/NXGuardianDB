<?php
include_once './util/conn_db.php'; // include database connection file
include './util/header_footer.php';

$dbConnection = new DBConnection();
$PDO = $dbConnection->getConnection();

$dbExists = false;

template_header("Start", "index");
?>

        <?php
        if ($PDO != null) {
            if ($dbConnection->checkDBExists() && $dbConnection->checkDBSchema()) {
                $dbExists = true;
                // Database exists, display tick SVG and message
                echo '<div class="db-status">
                <svg class="status-symbol" width="100%" height="100%" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg" style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;" > <path d="M448.8,1098C429.601,1098 411.601,1090.8 398.402,1077.6L84.002,753.602C54.002,726 52.803,679.204 81.604,649.202C109.206,619.202 156.002,618.003 186.004,646.804L416.404,811.204C429.603,823.204 450.006,820.805 460.802,806.403L1006.8,132.003C1030.8,99.605 1077.6,92.401 1110,117.605C1142.4,141.605 1149.6,188.406 1124.4,220.805L507.6,1068.01C494.401,1084.81 475.202,1095.61 454.799,1096.81C452.396,1098.01 451.197,1098.01 448.799,1098.01L448.8,1098Z" style="fill:rgb(0,225,80);fill-rule:nonzero;"/>
                 </svg>
                <p class="status-text">Database is set up & running</p></div>';

                if (file_exists('./db/bk.sql')) {
                    echo '<a href="util/bk_restore.php" class="db-restore">
                            <svg class="status-symbol" width="100%" height="100%" viewBox="0 0 1200 1200" xmlns="http://www.w3.org/2000/svg" xml:space="preserve"  style="fill-rule:evenodd;clip-rule:evenodd;stroke-linejoin:round;stroke-miterlimit:2;">
                                <g transform="matrix(1.2558,0,0,1.2558,-97.5828,-153.784)">
                                    <path d="M1012.5,600C1012.56,687.047 985.27,771.91 934.492,842.62C883.719,913.327 812.022,966.3 729.522,994.06C647.022,1021.83 557.882,1022.97 474.692,997.349C391.501,971.728 318.462,920.622 265.872,851.249C258.462,840.561 257.118,826.8 262.317,814.882C267.516,802.964 278.52,794.589 291.395,792.753C304.266,790.921 317.172,795.886 325.497,805.874C371.946,867.261 437.797,911.114 512.347,930.304C586.894,949.495 665.737,942.89 736.057,911.562C806.373,880.23 864.007,826.027 899.597,757.772C935.179,689.51 946.613,611.222 932.035,535.632C917.453,460.046 877.723,391.622 819.305,341.492C760.887,291.367 687.215,262.488 610.295,259.562C533.373,256.632 457.725,279.828 395.665,325.367C333.603,370.91 288.785,436.117 268.505,510.367L273.755,505.117C280.915,498.156 290.548,494.324 300.536,494.465C310.521,494.606 320.044,498.707 327.005,505.867C333.966,513.027 337.798,522.66 337.657,532.648C337.517,542.632 333.415,552.156 326.255,559.117L244.13,637.492C237.138,644.25 227.794,648.031 218.068,648.031C208.342,648.031 198.998,644.25 192.006,637.492L109.881,559.117C100.237,549.738 96.326,535.926 99.623,522.883C102.92,509.84 112.928,499.547 125.873,495.883C138.818,492.219 152.736,495.739 162.381,505.117L187.506,529.867C205.127,427.307 260.596,335.087 342.936,271.457C425.28,207.824 528.506,177.406 632.196,186.219C735.886,195.036 832.496,242.438 902.916,319.059C973.338,395.68 1012.45,495.939 1012.51,599.999L1012.5,600ZM525,562.5L750,562.5C763.398,562.5 775.777,555.352 782.477,543.75C789.176,532.148 789.176,517.852 782.477,506.25C775.778,494.648 763.399,487.5 750,487.5L525,487.5C511.602,487.5 499.223,494.648 492.523,506.25C485.824,517.852 485.824,532.148 492.523,543.75C499.222,555.352 511.601,562.5 525,562.5ZM525,712.5L637.5,712.5C650.898,712.5 663.277,705.352 669.977,693.75C676.676,682.148 676.676,667.852 669.977,656.25C663.278,644.648 650.899,637.5 637.5,637.5L525,637.5C511.602,637.5 499.223,644.648 492.523,656.25C485.824,667.852 485.824,682.148 492.523,693.75C499.222,705.352 511.601,712.5 525,712.5Z" style="fill:rgb(111,111,111);fill-rule:nonzero;stroke:rgb(111,111,111);stroke-width:19.07px;"/>
                                </g>
                            </svg>
                            <p class="status-text">Restore from backup</p></a>';
                }
            } else {
                // Database doesn't exist / not set up correctly, display cross SVG and message
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
            if ($dbExists) {
                echo "
                <div class=\"menu-buttons\">
                    <button class=\"menu-btn\" onclick=\"window.location='./add_game.php'\">Add Game</button>
                    <button class=\"menu-btn\" onclick=\"window.location='./search_games.php'\">Search Games</button>
                </div>";
            }
            ?>
        </div>
<?php template_footer(); ?>