<?php
include_once './conn_db.php'; // include database connection file

$PDO = getPDO(); // get PDO connection

$sql = file_get_contents('../db/bk.sql');
if ($sql === false) {
    header("Location: ../index.php");
    exit();
}
$PDO->exec($sql);
header("Location: ../index.php");
exit();
