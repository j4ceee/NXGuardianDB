<?php


function createGamePlatformSelection(array $platformsByCategory, array $previousPlatforms = null): void
{
    // loop through each category in $platformsByCategory
    // $category is the key, $platforms is the value
    foreach ($platformsByCategory as $category => $platforms) {
        echo '<div class="plat_category ' . htmlspecialchars($category) . '-cat">';
        echo '<h3>' . htmlspecialchars($category) . '</h3>';

        foreach ($platforms as $platform) {
            echo '<div class="plat_list_entry">';

            if ($previousPlatforms !== null && in_array($platform['platformID'], $previousPlatforms)) {
                echo '<input type="checkbox" class="plat_list_check" id="platform' . htmlspecialchars($platform['platformID']) . '" name="platform' . htmlspecialchars($platform['platformID']) . '" value="' . htmlspecialchars($platform['platformID']) . '" checked>';
            } else {
                echo '<input type="checkbox" class="plat_list_check" id="platform' . htmlspecialchars($platform['platformID']) . '" name="platform' . htmlspecialchars($platform['platformID']) . '" value="' . htmlspecialchars($platform['platformID']) . '">';
            }

            // same as 'echo '<input type="checkbox" name="platforms" value="' . $platform['platformID'] . '">';' but with htmlspecialchars()
            echo '<label for="platform' . htmlspecialchars($platform['platformID']) . '">' . htmlspecialchars($platform['platformName']) . '</label>';
            //echo '<img class="plat_list_logo" src="./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg">';
            echo '<div class="plat_list_logo" style="mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain; -webkit-mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain"> </div>';
            echo '</div>';
            echo "\r\n"; // line break
        }

        echo '</div>';
    }
}