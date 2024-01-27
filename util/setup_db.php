<?php
/** @var PDO $conn */
include 'conn_db.php'; // include database connection file

    // Execute SQL scripts
    $sql = file_get_contents('../db/setup_db.sql');
    $conn->exec($sql);

    $sql = file_get_contents('../db/fill_basics_db.sql');
    $conn->exec($sql);

    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
?>