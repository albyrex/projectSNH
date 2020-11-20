<?php

/*
  Page able to register a new account
*/

include_once "sessionManager.php";


// Check if the user is logged
if(!SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}

// Check parameters
if(!isset($_POST["email"]) || $_POST["email"] == "")
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing email)\"}");
else
    $email = $_POST["email"];
if(!isset($_POST["username"]) || $_POST["username"] == "")
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing username)\"}");
else
    $username = $_POST["username"];
if(!isset($_POST["password"]) || $_POST["password"] == "")
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing password)\"}");
else
    $password = $_POST["password"];


$success = SessionManager::createUser($email, $username, $password);

if($success != 0) {
    die("{\"errorCode\": -1, \"body\": \"Error. Email or username already in use\"}");
}

die("{\"errorCode\": 0, \"body\": \"Success\"}");

?>
