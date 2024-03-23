<?php

set_time_limit(300); // 5 minutes

// creates a backup of the database
// saves it to db/bk/bk_DATE.sql
// store the last 2 backups
// redirects to index.php

include_once './conn_db.php'; // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || !$dbConnection->checkDBSchema()) {
    header("Location: ../index.php");
    exit();
}

$files = glob('../db/bk/bk_*.sql'); // Get all file names

// check if there are 2 or more files -> delete the oldest ones & keep newest
do {
    if (count($files) < 2) {
        break;
    }

    $oldestFile = $files[0];
    foreach ($files as $file) {
        if (filemtime($file) < filemtime($oldestFile)) {
            $oldestFile = $file;
        }
    }

    unlink($oldestFile);
    $files = glob('../db/bk/bk_*.sql');
} while (count($files) > 1);


$cmd = sprintf('%s -h %s -u %s ',
    '"C:\\Users\\jance\\Documents\\XAMPP\\mysql\\bin\\mysqldump"', // path to mysqldump
    escapeshellarg($dbConnection->getServername()), // host name
    escapeshellarg($dbConnection->getUsername()) // MySQL username
);

// add password
if ($dbConnection->getPassword() != null) {
    $cmd .= '-p' . escapeshellarg($dbConnection->getPassword() . ' ');
}

$cmd .= sprintf('%s developers games game_platform_link game_platform_player_link --no-create-info --compact > %s',
    escapeshellarg($dbConnection->getDBName()), // database name
    escapeshellarg('../db/bk/bk_' . date("U") . '.sql') // backup file name
);

exec($cmd);

header("Location: ../index.php");
exit();
