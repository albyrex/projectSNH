<?php

/*
  This page lets the administrator manage the site
  Valid errorCode values:
    - 0: successful operation
    - negative number: error -> see "body"
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php"


/**
 * WARNING!
 * If $DEBUG_MODE is true, all checks on the user will be avoided and all
 * administrative functionalities will be available for averyone, included non
 * logged clients.
 */
$DEBUG_MODE = false;

if(!$DEBUG_MODE) {
    // Check if user is logged
    if(!SessionManager::isLogged()) {
        die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
    }
    // Check if user is an admin
    if(!SessionManager::isAdmin()) {
        die("{\"errorCode\": -2, \"body\": \"User is not an admin\"}");
    }
}


$function = checkPostParameterOrDie("function");
if($function == "addBook")
    addBook();
else if($function == "removeBook")
    removeBook();
else
    die("{\"errorCode\": -3, \"body\": \"Invalid request\"}");


/**
 * Add a new book
 */
function addBook() {
    $title = checkPostParameterOrDie("title");
    $author = checkPostParameterOrDie("author");
    $data = checkPostParameterOrDie("data");
    $cover = checkPostParameterOrDie("cover");
    $price = checkPostParameterOrDie("price");
    $path = checkPostParameterOrDie("path");

    $result = new stdClass();
    $result->errorCode = 0;

    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO books(title,author,data,cover,price,path) VALUES (?,?,?,?,?,?)");
    $stmt->bind_param(
        "ssssds", $title, $author, $data, $cover, $price, $path
    );
    $success = $stmt->execute();
    $conn->close();
    if($success === false) {
        die("{\"errorCode\": -10, \"body\": \"Internal server error\"}");
    }

    $result->body = "Ok";

    echo json_encode($result);
}

function removeBook() {

}

?>
