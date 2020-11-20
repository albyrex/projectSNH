<?php

/*
  Page able to register a new account
*/

include_once "sessionManager.php";
include_once "utils.php"


// Check if the user is already logged
if(SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is already logged\"}");
}

// Check parameters
$email = checkPostParameterOrDie("email");
$username = checkPostParameterOrDie("username");
$password = checkPostParameterOrDie("password");
$answers = checkPostParameterOrDie("answers");

// Check if username and email are valid
if(invalidUsername($username))
    die("{\"errorCode\": -4, \"body\": \"Invalid username\"}");
if(invalidEmail($email))
    die("{\"errorCode\": -4, \"body\": \"Invalid email\"}");

$success = SessionManager::createUser($email, $username, $password, $answers);

if($success != 0)
    die("{\"errorCode\": -1, \"body\": \"Error. Email or username already in use\"}");

die("{\"errorCode\": 0, \"body\": \"Success\"}");

?>
