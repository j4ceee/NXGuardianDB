<?php
global $dbname;
$dbname = "jaceedb"; // database name to connect to

global $conn;
$conn = null; // initialize $conn to null

function getPDO(): ?PDO
{
    $servername = "localhost"; // server name to connect to
    $username = "root"; // username to connect to server
    $password = ""; // password to connect to server

    if ($GLOBALS['conn'] == null) {
        try {
            $GLOBALS['conn'] = new PDO("mysql:host=$servername", $username, $password); // connect to database with PDO
            $GLOBALS['conn']->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // set PDO error mode to exception
        }
        catch(PDOException $e) {
            return null;
        }
    }

    // return $conn
    return $GLOBALS['conn'];
}

function checkDBExists(): bool
{
    $conn = getPDO(); // get PDO connection
    if ($conn == null) {
        return false;
    }
    $stmt = $conn->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
    $stmt->execute(['dbname' => $GLOBALS['dbname']]); // execute statement with database name
    $result = $stmt->fetchAll(); // fetch all results and store in $result
    return count($result) > 0; // return true if database exists, false otherwise
}

function useDB(): bool
{
    $conn = getPDO(); // get PDO connection
    try {
        $conn->exec("USE " . $GLOBALS['dbname']); // use database
    } catch (PDOException $e) {
        return false;
    }
    return true;
}
?>