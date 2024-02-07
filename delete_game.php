<?php
include_once './util/conn_db.php'; // include database connection file

$PDO = getPDO(); // get PDO connection

if (!checkDBExists()) {
    header("Location: ./index.php");
    exit();
}

useDB();

$msg = '';

// Check if game_platformID exists
if (isset($_GET['game_platformID'])) {
    // get gameID
    $gameID = getGameID($PDO, $_GET['game_platformID']);

    // get devID
    $devID = getDevID($PDO, $gameID);

    // prepare SQL to delete the game_platform entry
    $stmt = $PDO->prepare('DELETE FROM game_platform_link WHERE game_platformID = ?');
    $stmt->execute([$_GET['game_platformID']]);

    // check if the game_platform was the last release for the game
    // if so, delete the game
    delLastGamePlatformRelease($PDO, $gameID);

    // check if the game was the last game for the developer
    // if so, delete the developer
    delLastGameForDeveloper($PDO, $devID);

    $msg = 'You have deleted the Game-Platform Entry!';
}
// Check if gameID exists
else if (isset($_GET['gameID'])) {
    // get devID
    $devID = getDevID($PDO, $_GET['gameID']);

    // prepare SQL to delete the game entry
    $stmt = $PDO->prepare('DELETE FROM games WHERE gameID = ?');
    $stmt->execute([$_GET['gameID']]);

    // check if the game was the last game for the developer
    // if so, delete the developer
    delLastGameForDeveloper($PDO, $devID);

    $msg = 'You have deleted the Game!';
}
else {
    exit('No ID specified!');
}
// redirect to list_games.php
// TODO: add a message to the URL
header("Location: ./list_games.php");

function getGameID($PDO, $game_platformID): int
{
    $stmt = $PDO->prepare('SELECT gameID FROM game_platform_link WHERE game_platformID = ?');
    $stmt->execute([$game_platformID]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['gameID'];
}

function getDevID($PDO, $gameID): int
{
    $stmt = $PDO->prepare('SELECT devID FROM games WHERE gameID = ?');
    $stmt->execute([$gameID]);
    return $stmt->fetch(PDO::FETCH_ASSOC)['devID'];
}

function delLastGamePlatformRelease($PDO, $gameID): void
{
    $stmt = $PDO->prepare('SELECT gameID FROM game_platform_link WHERE gameID = ?');
    $stmt->execute([$gameID]);
    $game_platforms = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($game_platforms) == 0) {
        $stmt = $PDO->prepare('DELETE FROM games WHERE gameID = ?');
        $stmt->execute([$gameID]); // Pass $gameID as an array
    }
}

function delLastGameForDeveloper($PDO, $devID): void
{
    $stmt = $PDO->prepare('SELECT gameID FROM games WHERE devID = ?');
    $stmt->execute([$devID]);
    $games = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if (count($games) == 0) {
        $stmt = $PDO->prepare('DELETE FROM developers WHERE devID = ?');
        $stmt->execute([$devID]); // Pass $devID as an array
    }
}