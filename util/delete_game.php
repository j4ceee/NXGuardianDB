<?php /** @noinspection SqlWithoutWhere */
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->getConnection();

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || $dbConnection->checkDBSchema() !== true) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
    exit();
}

// ------------------- LOGIN CHECK -------------------
session_set_cookie_params([
    'lifetime' => 0, // cookie expires at end of session
    'path' => '/', // cookie available within entire domain
    'domain' => 'localhost', // cookie domain
    'secure' => true, // cookie only sent over secure HTTPS connections
    'httponly' => true, // cookie only accessible via HTTP protocol, not by JS
    'samesite' => 'Strict' // cookie SameSite attribute: Lax (= some cross-site requests allowed) or Strict (= no cross-site requests allowed)
]);

session_start(); // start a session - preserves account data across pages // start a session - preserves account data across pages

session_regenerate_id(true); // regenerate session ID to prevent session fixation attacks

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // if user is not logged in, redirect to home page
    header('Location: https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/index.php?status=334');
    exit();
}
// ----------------- LOGIN CHECK END -------------------

$msg = '';

if (isset($_GET['gameID'])) {

    // sanitize gameID
    $gameID = filter_input(INPUT_GET, 'gameID', FILTER_VALIDATE_INT);

    // get devID
    $devID = getDevID($PDO, $gameID);

    // prepare SQL to delete the game entry
    $stmt = $PDO->prepare('DELETE FROM games WHERE gameID = :gameID');
    $stmt->bindParam(':gameID', $gameID, PDO::PARAM_INT);
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