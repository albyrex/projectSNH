<?php

/*
   This api page let the user change his password.
   He has to specity both the old and the new password.
*/

include_once "sessionManager.php";
include_once "utils.php";


if(!SessionManager::isLogged()) {
    header("HTTP/1.0 403 Forbidden");
    die("{\"errorCode\": -4, \"body\": \"Something went wrong\"}");
}

$oldpwd = checkPostParameterOrDie("oldpwd");
$newpwd = checkPostParameterOrDie("newpwd");

$operationResult = SessionManager::changeUserPassword(SessionManager::getIdUser(), $oldpwd, $newpwd);

if($operationResult !== $operationSuccessful) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -4, \"body\": \"Something went wrong\"}");
}

die("{\"errorCode\": 0, \"body\": \"Ok\"}");

?>
