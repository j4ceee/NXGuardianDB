<?php
$servername = "localhost"; // server name to connect to
$username = "root"; // username to connect to server
$password = ""; // password to connect to server
$dbname = "jaceedb"; // database name to connect to
$failed = false; // initialize $failed to false

try {
    $conn = new PDO("mysql:host=$servername", $username, $password); // connect to server with PDO
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set PDO error mode to exception
}
catch(PDOException $e) {
    $failed = true; // set $failed to true

    // Redirect back to index.php
    header("Location: ../index.php");
    exit();
}
?>