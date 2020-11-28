<?php

/*
  This page allow a user to buy a book
*/

include_once "sessionManager.php";
include_once "utils.php";


// Check if user is logged
if(!SessionManager::isLogged()) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}


$idBook = checkPostNumericParameterOrDie("idBook");
$cardname = checkPostParameterOrDie("cardname");
$cardnumber = checkPostParameterOrDie("cardnumber");
$cvv = checkPostNumericParameterOrDie("cvv");
$monthyear = checkPostParameterOrDie("monthyear");
$idUser = SessionManager::getIdUser();

if(!SessionManager::validIdBook($idBook)) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}

if(SessionManager::userCanDownload($idUser, $idBook)) {
    die("{\"errorCode\": 1, \"body\": \"Already bought\"}");
}

if(!pay()) {
    die("{\"errorCode\": -3, \"body\": \"Error during payment\"}");
}

die("{\"errorCode\": 0, \"body\": \"Ok\"}");



/*
  Help functions
*/

function pay() {
    usleep(500);
    return true;
}

?>
