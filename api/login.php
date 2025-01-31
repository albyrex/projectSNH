<?php

/*
  Login page
  Possible errorCode values:
    -  0 -> No errors
    - -1 -> Generic error (should never be returned)
    - -2 -> User is already logged
    - -4 -> Something went wrong
*/

include_once "sessionManager.php";
include_once "utils.php";


// Check if the user is already logged
if(SessionManager::isLogged()) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -2, \"body\": \"User is already logged\"}");
}

// Check parameters
$email = checkPostParameterOrDie("email");
$password = checkPostParameterOrDie("password");

$result = SessionManager::sessionStart($email, $password);


if($result === $operationSuccessful) {
    die("{\"errorCode\": 0, \"body\": 0}");
} else {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -4, \"body\": \"Something went wrong\"}");
}

die("{\"errorCode\": -1, \"body\": \"General error\"}");

?>
