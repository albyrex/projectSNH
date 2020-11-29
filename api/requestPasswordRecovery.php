<?php

/*
  The user can request a password change performing a request to this page.
  The user must provide his email and the correct secret answers to the security
  questions.
  If the email is valid and the answers are correct, a new "password recovery
  request" will be inserted in the database and a password reset link will be
  sent to the user email.
*/

include_once "sessionManager.php";
include_once "utils.php";


$answers = parseAnswers(checkPostParameterOrDie("answers"));
$email = checkPostParameterOrDie("email");

if(invalidEmail($email)) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}

$operationResult = SessionManager::createPasswordRecoveryRequest($email, $answers);

if($operationResult != $operationSuccessful) {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}

die("{\"errorCode\": 0, \"body\": \"Ok\"}");

?>
