<?php

/*
  Page able to logout the user
*/

include_once "sessionManager.php";


// Check if the user is logged
if(!SessionManager::isLogged()) {
    header("HTTP/1.0 400 Bad Request");
    die("Bad Request: User is not logged");
}

SessionManager::closeSession();

header("HTTP/1.0 307 Temporary Redirect");
header("Location: ../login.php");

?>
