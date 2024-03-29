<?php

class DBConnection {
    private ?PDO $connection;
    private string $dbname = "nxguardiandb"; // Consider loading from environment variables
    private string $servername = "localhost"; // server name to connect to
    private string $username = "root"; // username to connect to server
    private string $password = ""; // password to connect to server

    public function __construct() {
        $this->connect();
    }

    private function connect(): void
    {
        try {
            $servername = $this->servername;
            $username = $this->username;
            $password = $this->password;

            $this->connection = new PDO("mysql:host=$servername", $username, $password);
            $this->connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch(PDOException) {
            $this->connection = null;
        }
    }

    public function getConnection(): PDO|null
    {
        return $this->connection;
    }

    public function getDbname(): string
    {
        return $this->dbname;
    }

    public function getServername(): string
    {
        return $this->servername;
    }

    public function getUsername(): string
    {
        return $this->username;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    function checkDBExists(): bool
    {
        if ($this->connection === null) {
            return false;
        }
        $stmt = $this->connection->prepare("SHOW DATABASES LIKE :dbname"); // prepare statement to check if database exists, :dbname is a placeholder
        $stmt->bindParam(':dbname', $this->dbname); // bind parameter :dbname to $this->dbname
        $stmt->execute(); // execute statement
        $result = $stmt->fetchAll(); // fetch all results and store in $result
        return count($result) > 0; // return true if database exists, false otherwise
    }

    function checkDBSchema(): bool
    {
        $reqTables = array("developers", "games", "game_platform_link", "game_platform_player_link", "platforms", "playermodes");

        if ($this->connection === null) {
            return false;
        }
        try {
            $stmt = $this->connection->prepare("SHOW TABLES FROM " . $this->dbname); // prepare statement to check if database schema exists
            $stmt->execute(); // execute statement
            $result = $stmt->fetchAll(); // fetch all results and store in $result
        } catch (PDOException) {
            return false;
        }

        // compare tables in database with required tables

        $tables = array();
        foreach ($result as $table) {
            $tables[] = $table[0]; // store table name in $tables
        }

        foreach ($reqTables as $reqTable) {
            if (!in_array($reqTable, $tables)) { // if required table is not $tables (all tables in database)
                return false;
            }
        }
        return true;
    }

    function useDB(): ?PDO
    {
        if ($this->connection === null) {
            return null;
        }
        try {
            $dbname = $this->dbname;
            $stmt = $this->connection->prepare("USE $dbname"); // use database
            $stmt->execute(); // execute statement
        } catch (PDOException) {
            return null;
        }
        return $this->connection;
    }
}
