<?php
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file
require_once(dirname(__DIR__) . '/util/utility_func.php'); // include delete game file

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

if (!isset($_SESSION['loggedin']) || $_SESSION['loggedin'] !== true) {
    // if user is not logged in, redirect to home page
    header('Location: https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . '/index.php?status=334');
    exit();
}
// ----------------- LOGIN CHECK END -------------------

// script gets called with ?file=filename

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $file = preg_replace('/[^a-zA-Z0-9_]/', '', $file); // remove all characters except letters, numbers and underscores
    $file .= '.sql'; // add .sql extension

    // get all backup files from db/bk/
    $sql_files = glob(dirname(__DIR__) . '/db/bk/bk*.sql');
    // get only the filenames of the backup files
    $sql_files = array_map('basename', $sql_files);

    if (in_array($file, $sql_files)) { // check if the file exists in the backup folder
            // delete all games & developers first
            deleteAllGames($PDO);
            deleteAllDevelopers($PDO);

            // restore the database from the backup file
            $cmd = sprintf('%s -h %s -u %s ',
                '"C:\\Users\\jance\\Documents\\XAMPP\\mysql\\bin\\mysql"', // path to mysql //TODO: change path
                escapeshellarg($dbConnection->getServername()), // host name
                escapeshellarg($dbConnection->getUsername()) // MySQL username
            );

            // append password if it is set
            if ($dbConnection->getPassword() != null) {
                $cmd .= '-p' . escapeshellarg($dbConnection->getPassword() . ' ');
            }

            // append database name and backup file
            $cmd .= sprintf('%s --default-character-set=utf8mb4 < %s',
                escapeshellarg($dbConnection->getDBName()), // database name
                escapeshellarg(dirname(__DIR__) . '/db/bk/' . $file) // backup file name
            );

            exec($cmd);

            header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/list_games.php");
            exit();
    }
}

header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
exit();
