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
include "libSql.php";


// Check if the user is already logged
if(SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is already logged\"}");
}

// Check parameters
if(!isset($_POST["email"]) || $_POST["email"] == "")
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing name)\"}");
else
    $email = $_POST["email"];
if(!isset($_POST["password"]) || $_POST["password"] == "")
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing surname)\"}");
else
    $password = $_POST["password"];

$result = SessionManager::sessionStart($email, $password);

if($result == $userNotFound) {
    die("{\"errorCode\": -4, \"body\": \"Bad credentials\"}");
} else if($result == $wrongPassword) {
    die("{\"errorCode\": -4, \"body\": \"Bad credentials\"}");
} else if($result == $loginSuccess) {
    if(SessionManager::isAdmin())
        die("{\"errorCode\": 0, \"body\": 1}");  // Controllare!!!
    else
        die("{\"errorCode\": 0, \"body\": 0}");  // Controllare!!!
}

die("{\"errorCode\": -1, \"body\": \"General error\"}");

?>
