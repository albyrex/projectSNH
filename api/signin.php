<?php

/*
  Page able to register a new account
*/

include_once "sessionManager.php";


// Check if the user is logged
if(!SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}



?>
