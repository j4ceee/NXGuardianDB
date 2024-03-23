<?php

// creates a backup of the database
// TODO: saves it to db/bk/bk_DATE.sql
// TODO: store the last 2 backups
// redirects to index.php

include_once './conn_db.php'; // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

// $bkFilePattern = '../db/bk/bk_' . date("Y-m-d-H-i-s") . '.sql';
$bkFilePattern = '../db/bk/bk.sql';

$cmd = 'mysqldump --user=' . $dbConnection->getUsername() . ' --password=' . $dbConnection->getPassword() . ' --host=' . $dbConnection->getServername() . ' ' . $dbConnection->getDbname() . ' > ' . $bkFilePattern;

exec($cmd);