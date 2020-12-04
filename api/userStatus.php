<?php

/*
  Page to retrieve the user status.
    - errorCode is always 0 because it is always possible to ask for the
      user status even if the user is not logged
    - status can be:
        - -1 -> User is not logged
        -  0 -> User is logged
        -  1 -> User is logged and it is an admin
*/

include_once "sessionManager.php";


$response = new stdClass();
$response->errorCode = 0;
$response->body = new stdClass();

if(!SessionManager::isLogged()) {
    $response->body->status = -1;
} else {
    if(SessionManager::isAdmin())
        $response->body->status = 1;
    else
        $response->body->status = 0;
    $response->body->idUser = SessionManager::getIdUser();
    $response->body->email = SessionManager::getEmail();
}

echo json_encode($response);

?>
