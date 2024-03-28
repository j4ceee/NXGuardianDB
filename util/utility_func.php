<?php /** @noinspection CssUnknownTarget */

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
            echo '<div class="plat_list_logo" style="mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain; -webkit-mask: url(./img/platforms/' . htmlspecialchars($platform['platformID']) . '.svg) no-repeat center / contain"> </div>';
            echo '</div>';
            echo "\r\n"; // line break
        }

        echo '</div>';
    }
}

// generate checkboxes for multiplayer modes
function generateMPCheckboxes(mixed $mode, bool $modifyPlayers, string $platID = null, int $maxPlayers = -1, int $minPlayers = -1, bool $isChecked = false): void
{
    if ($modifyPlayers) {
        // if we are editing a game, we need to add the platformID to the input names (e.g. in add_game.php or edit_game.php)

        if ($platID === null) {
            $platID = "_[platID]"; // if no platformID is given, use a placeholder
        }
        else {
            $platID = "_" . htmlspecialchars($platID); // if a platformID is given, use it
        }
    }

    echo '<div class="mp_feature_check_cont">';

    if ($isChecked) {
        $isChecked = 'checked';
    } else {
        $isChecked = '';
    }

    echo '<label class="mp_feature_label" for="' . htmlspecialchars($mode['modeShort']) . $platID . '">
          <input type="checkbox" class="mp_feature_check" name="' . htmlspecialchars($mode['modeShort']) . $platID . '" id="' . htmlspecialchars($mode['modeShort']) . $platID . '" ' . $isChecked . '>
          <span class="win_dark_check"></span>
          <div class="mp_mode_logo" style="mask: url(./icons/modes/modes_' . htmlspecialchars($mode["modeShort"]) . '.svg) no-repeat center / contain; -webkit-mask: url(./icons/modes/modes_' . htmlspecialchars($mode['modeShort']) . '.svg) no-repeat center / contain\"></div>
          <p class="mp_feature_text">' . htmlspecialchars($mode['modeName']) . '</p></label>';
    echo '</div>';

    if ($mode['modeShort'] != 'single') {
        echo '<div class="mp_feature_count_cont">';
        if ($modifyPlayers) {
                echo '<input type="number" class="mp_feature_minPlayers win_dark_input" name="' . htmlspecialchars($mode['modeShort']) . '_min' . $platID . '" id="' . htmlspecialchars($mode['modeShort']) . '_min' . $platID . '" min="1" max="999" step="1"';
                if ($minPlayers != -1) { // if we are editing a game, we need to add the player count to the input fields
                    echo ' value="' . htmlspecialchars($minPlayers) . '"';
                }
                echo '>';
                echo "<span>-</span>";
                echo '<input type="number" class="mp_feature_maxPlayers win_dark_input" name="' . htmlspecialchars($mode['modeShort']) . '_max' . $platID . '" id="' . htmlspecialchars($mode['modeShort']) . '_max' . $platID . '" min="1" max="999" step="1"';
                if ($maxPlayers != -1) { // if we are editing a game, we need to add the previous player count to the input fields
                    echo ' value="' . htmlspecialchars($maxPlayers) . '"';
                }
                echo '>';
        } else { // when searching for games, only display one input field for the player count
                echo '<input type="number" class="mp_feature_Players win_dark_input" name="' . htmlspecialchars($mode['modeShort']) . '_players" id="' . htmlspecialchars($mode['modeShort']) . '_players" min="1" max="999" step="1">';
        }
        echo '</div>';
    }
    echo "\r\n\r\n";
}