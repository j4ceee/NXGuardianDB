<?php
// TODO: only run this script once & comment out everything for release


// redirect to home page
header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
exit();


/*
 * This script adds users to the database.
 * It should only be run once to add the initial users.
 * After that, it should be commented out.
 */


/*
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || $dbConnection->checkDBSchema() !== true) {
    exit("Database connection failed or schema is incorrect.");
}

// Define the users
$users = [
    // [ // Example user
    //    'username' => 'user1',
    //    'email' => 'user1@example.com',
    //    'password' => 'password1'
    // ],
    [
        'username' => 'test',
        'email' => 'test@test.com',
        'password' => 'testtest'
    ],
];

foreach ($users as $user) {
    // Hash the password
    $hashedPassword = password_hash($user['password'], PASSWORD_DEFAULT);

    // Prepare the SQL statement
    $stmt = $PDO->prepare("INSERT INTO accounts (username, email, password) VALUES (:username, :email, :password)");

    // Bind the parameters
    $stmt->bindParam(':username', $user['username']);
    $stmt->bindParam(':email', $user['email']);
    $stmt->bindParam(':password', $hashedPassword);

    // Execute the statement
    $stmt->execute();
}

echo "Users have been added successfully: \n";
echo "<pre>";
print_r($users);
echo "</pre>";
*/

