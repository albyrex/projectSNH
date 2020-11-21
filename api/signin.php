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
$answers = parseAnswers(checkPostParameterOrDie("answers"));

// Check if username, email and answers are valid
if(invalidUsername($username)) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -4, \"body\": \"Bad request: invalid username\"}");
}
if(invalidEmail($email)) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -5, \"body\": \"Bad request: invalid email\"}");
}

$success = SessionManager::createUser($email, $username, $password, $answers);

if($success != 0) {
    die("{\"errorCode\": -1, \"body\": \"Error. Email or username already in use\"}");
}

die("{\"errorCode\": 0, \"body\": \"Success\"}");

?>
