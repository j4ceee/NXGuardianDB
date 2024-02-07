<?php
include_once './util/conn_db.php'; // include database connection file
include './util/header_footer.php';

$PDO = getPDO(); // get PDO connection

template_header("Start", "index");
?>

        <?php
        if ($PDO != null) {
            if (checkDBExists()) {
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
            if (checkDBExists()) {
                echo "
                <div class=\"menu-buttons\">
                    <button class=\"menu-btn\" onclick=\"window.location='./add_game.php'\">Add Game</button>
                    <button class=\"menu-btn\" onclick=\"window.location=''\">Search Games</button>
                </div>";
            }
            ?>
        </div>
<?php template_footer(); ?>