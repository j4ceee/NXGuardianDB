<?php
require_once(dirname(__DIR__) . '/util/conn_db.php'); // include database connection file
require_once(dirname(__DIR__) . '/util/validate.php'); // validate functions

$dbConnection = new DBConnection();
$PDO = $dbConnection->useDB();

if ($PDO === null || $dbConnection->checkDBSchema() !== true) {
    header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php");
    exit();
}

session_set_cookie_params([
    'lifetime' => 0, // cookie expires at end of session
    'path' => '/', // cookie available within entire domain
    'domain' => 'localhost', // cookie domain // TODO: change to actual domain
    'secure' => true, // cookie only sent over secure HTTPS connections
    'httponly' => true, // cookie only accessible via HTTP protocol, not by JS
    'samesite' => 'Strict' // cookie SameSite attribute: Lax (= some cross-site requests allowed) or Strict (= no cross-site requests allowed)
]);

session_start(); // start a session - preserves account data across pages // start a session - preserves account data across pages

$username = (string)$_POST['auth_username']; // store username
$email = (string)$_POST['auth_email']; // store email
$password = (string)$_POST['auth_password']; // store password in plaintext

$honeypot = $_POST['auth_pin']; // store honeypot value

if (!empty($honeypot)) {
    redirectToPreviousPage("333"); // honeypot value not empty -> redirect to previous page
} else {
    unset($honeypot); // honeypot value empty -> unset honeypot value
    echo "honeypot value empty";
}

validate_login($username, $email, $password); // validate login data

$stmt = $PDO->prepare("SELECT * FROM accounts WHERE username = :username AND email = :email"); // prepare statement to select account data
$stmt->bindParam(':username', $username); // bind parameter :username to $_POST['username']
$stmt->bindParam(':email', $email); // bind parameter :email to $_POST['email']
$stmt->execute(); // execute statement
$account = $stmt->fetch(PDO::FETCH_ASSOC); // fetch account data and store in $account

if ($stmt->rowCount() > 0) {
    $pw_hash = $account['password']; // store password hash
    $id = $account['id']; // store account id

    if (password_verify($password, $pw_hash)) { // compare plaintext password with password hash
        session_regenerate_id();
        $_SESSION['loggedin'] = true;
        $_SESSION['name'] = $username;
        $_SESSION['id'] = $id;
    } else {
        // incorrect password
        redirectToPreviousPage("333"); // "Incorrect password/username/email" -> don't tell the user which one is incorrect
    }
} else {
    // incorrect username or email
    redirectToPreviousPage("333");
}

header("Location: https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF'], 2) . "/index.php"); // redirect to home page
exit();

/*
 * the following php.ini settings are recommended for session security:
 * session.cookie_httponly = 1
 * session.use_only_cookies = 1
 * session.cookie_secure = 1
 */