<?php
include_once './conn_db.php'; // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

$sql = file_get_contents('../db/bk.sql');
if ($sql === false) {
    header("Location: ../index.php");
    exit();
}
$PDO->exec($sql);
header("Location: ../index.php");
exit();
