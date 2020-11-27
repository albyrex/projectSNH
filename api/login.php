<?php

/*
  Login page
  Possible errorCode values:
    -  0 -> No errors
    - -1 -> Generic error (should never be returned)
    - -2 -> User is already logged
    - -3 -> Missing parameters
    - -4 -> Non valid parameters
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php";


// Check if the user is already logged
if(SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is already logged\"}");
}

// Check parameters
$email = checkPostParameterOrDie("email");
$password = checkPostParameterOrDie("password");

$result = SessionManager::sessionStart($email, $password);

if($result == $userNotFound || $result == $wrongPassword) {
    die("{\"errorCode\": -4, \"body\": \"Something went wrong\"}");
} else if($result == $loginSuccess) {
    if(SessionManager::isAdmin())
        die("{\"errorCode\": 0, \"body\": 1}");  // Controllare!!!
    else
        die("{\"errorCode\": 0, \"body\": 0}");  // Controllare!!!
}

die("{\"errorCode\": -1, \"body\": \"General error\"}");

?>
