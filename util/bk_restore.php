<?php
include_once './conn_db.php'; // include database connection file
include_once './delete_game.php'; // include delete game file

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

// script gets called with ?file=filename.sql

if (isset($_GET['file'])) {
    $file = $_GET['file'];
    $file = preg_replace('/[^a-zA-Z0-9_]/', '', $file); // remove all characters except letters, numbers and underscores
    $file .= '.sql'; // add .sql extension

    // get all backup files from db/bk/
    $sql_files = glob('../db/bk/bk*.sql');
    // get only the filenames of the backup files
    $sql_files = array_map('basename', $sql_files);

    if (in_array($file, $sql_files)) { // check if the file exists in the backup folder

        if (file_exists('../db/bk/' . $file)) {

            // delete all games & developers first
            deleteAllGames($PDO);
            deleteAllDevelopers($PDO);

            $cmd = sprintf('%s -h %s -u %s ',
                '"C:\\Users\\jance\\Documents\\XAMPP\\mysql\\bin\\mysql"', // path to mysql
                escapeshellarg($dbConnection->getServername()), // host name
                escapeshellarg($dbConnection->getUsername()) // MySQL username
            );

            // add password
            if ($dbConnection->getPassword() != null) {
                $cmd .= '-p' . escapeshellarg($dbConnection->getPassword() . ' ');
            }

            $cmd .= sprintf('%s --default-character-set=utf8mb4 < %s',
                escapeshellarg($dbConnection->getDBName()), // database name
                escapeshellarg('../db/bk/' . $file) // backup file name
            );

            exec($cmd);

            header("Location: ../list_games.php");
            exit();

        }
    }
}

header("Location: ../index.php");
exit();
