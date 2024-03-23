<?php
include_once './conn_db.php'; // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->getConnection();

if ($PDO !== null) {
// this script will only get called when there are issues with the database
// -> recreate the database regardless if it exists or not

    if ($dbConnection->checkDBExists()) {
        // drop the database
        $PDO->exec("DROP DATABASE " . $dbConnection->getDbname());
    }

    $PDO->exec("CREATE DATABASE " . $dbConnection->getDbname() . " DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// use the database
    $PDO = $dbConnection->useDB();

    if ($PDO !== null) {
        // execute SQL scripts
        $sql = file_get_contents('../db/setup_db.sql');
        $PDO->exec($sql);

        $sql = file_get_contents('../db/fill_basics_db.sql');
        $PDO->exec($sql);
    }
}

// redirect back to index.php
header("Location: ../index.php");
exit();

?>