<?php

/*
  Page able to logout the user
*/

include_once "sessionManager.php";


// Check if the user is logged
if(!SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}

SessionManager::closeSession();

die("{\"errorCode\": 0, \"body\": \"Ok\"}");




?>
