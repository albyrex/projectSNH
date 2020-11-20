<?php

/*
  This page lets the administrator manage the site
  Valid errorCode values:
    - 0: successful operation
    - negative number: error -> see "body"
*/

include_once "sessionManager.php";
include_once "libSql.php";


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
        die("{\"errorCode\": -3, \"body\": \"User is not an admin\"}");
    }
}

if(!isset($_POST["function"]) || $_POST["function"] == "")
    die("{\"errorCode\": -1, \"body\": \"Invalid request\"}");
else if($_POST["function"] == "addBook")
    addBook();
else
    die("{\"errorCode\": -1, \"body\": \"Invalid request\"}");


function checkPostParameter($parName) {
    if(!isset($_POST[$parName]) || $_POST[$parName] == "")
        die("{\"errorCode\": -1, \"body\": \"Invalid request\"}");
    return $_POST[$parName];
}

/**
 * Add a new book
 */
function addBook() {
    $title = checkPostParameter("title");
    $author = checkPostParameter("author");
    $data = checkPostParameter("data");
    $cover = checkPostParameter("cover");
    $price = checkPostParameter("price");
    $path = checkPostParameter("path");

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

?>
