<?php
include_once './conn_db.php'; // include database connection file

$PDO = getPDO(); // get PDO connection

    // Execute SQL scripts
    $sql = file_get_contents('../db/setup_db.sql');
    $PDO->exec($sql);

    $sql = file_get_contents('../db/fill_basics_db.sql');
    $PDO->exec($sql);

    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
?>