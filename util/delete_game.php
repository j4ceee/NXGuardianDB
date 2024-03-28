<?php /** @noinspection SqlWithoutWhere */
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->getConnection();

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
    exit();
}

$msg = '';

if (isset($_GET['gameID'])) {

    // sanitize gameID
    $gameID = filter_input(INPUT_GET, 'gameID', FILTER_VALIDATE_INT);

    // get devID
    $devID = getDevID($PDO, $gameID);

    // prepare SQL to delete the game entry
    $stmt = $PDO->prepare('DELETE FROM games WHERE gameID = ?');
    $stmt->bindParam(1, $gameID, PDO::PARAM_INT);
    $stmt->execute();

    // check if the game was the last game for the developer
    // if so, delete the developer
    delLastGameForDeveloper($PDO, $devID);

    $msg = 'You have deleted the Game!';
}
else {
    $msg = 'No Game selected!';
}
// redirect to list_games.php
// TODO: add a message to the URL
if (__FILE__ == $_SERVER['SCRIPT_FILENAME']) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/list_games.php");
    exit();
}


function getDevID($PDO, $gameID): int
{
    $stmt = $PDO->prepare('SELECT devID FROM games WHERE gameID = ?');
    $stmt->bindParam(1, $gameID, PDO::PARAM_INT);
    $stmt->execute();
    return $stmt->fetch(PDO::FETCH_ASSOC)['devID'];
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


// delete all games & developers

function deleteAllGames($PDO): void
{
    $stmt = $PDO->prepare('DELETE FROM games');
    $stmt->execute();
}

function deleteAllDevelopers($PDO): void
{
    $stmt = $PDO->prepare('DELETE FROM developers');
    $stmt->execute();
}