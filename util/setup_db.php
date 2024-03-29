<?php
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->getConnection();

if ($PDO !== null) {
// this script will only get called when there are issues with the database
// -> recreate the database regardless if it exists or not

    if ($dbConnection->checkDBExists()) {
        try {
            // drop the database
            $dbname = $dbConnection->getDbname();
            $stmt = $PDO->prepare("DROP DATABASE $dbname");
            $stmt->execute();
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    try {
        // create the database
        $dbname = $dbConnection->getDbname();
        $stmt = $PDO->prepare("CREATE DATABASE $dbname DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        $stmt->execute();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }

// use the database
    $PDO = $dbConnection->useDB();

    if ($PDO !== null) {
        // execute SQL scripts
        $sql = file_get_contents(dirname(__DIR__) . '/db/setup_db.sql'); // read the SQL file - returns false if file does not exist
        if ($sql !== false) {
            $PDO->exec($sql);
        }

        $sql = file_get_contents(dirname(__DIR__) . '/db/fill_basics_db.sql');
        if ($sql !== false) {
            $PDO->exec($sql);
        }
    }
}

// redirect back to index.php
header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
exit();

