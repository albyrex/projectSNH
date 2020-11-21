<?php

/*
  Functions:
    - topDownloaded -> return the $TOP_DOWNLOADED_SIZE top most downloaded books
    - getBookById -> get a book specifying its id_book
    - getBooksByIdUser -> retrieve all books bought by the currently logged user
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php"


$TOP_DOWNLOADED_SIZE = 10;


$function = checkPostParameterOrDie("function");
if($function == "topDownloaded")
    topDownloaded();
else if($function == "getBookById")
    getBookById();
else if($function == "getBooksByIdUser")
    getBooksByIdUser();
else {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Invalid request\"}");
}


function topDownloaded() {
    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $r = $conn->query(
        "SELECT b.id_book, title, author, date, cover, abstract, price, COUNT(p.id_user) AS downloadCount
        FROM books b LEFT OUTER JOIN payments p ON (b.id_book = p.id_book)
        GROUP BY b.id_book
        ORDER BY downloadCount DESC
        LIMIT $TOP_DOWNLOADED_SIZE"
    );
    while($row = $r->fetch_object()) {
        array_push($result->body->bookList, $row);
    }
    $conn->close();

    echo json_encode($result);
}

function getBookById() {
    $idBook = checkPostNumericParameterOrDie("idBook");

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT * FROM books WHERE id_book = ?"
    );
    $stmt->bind_param("i", $idBook);
    $success = $stmt->execute();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }
    $r = $stmt->get_result();
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $result->body->book = $row;
    } else {
        header("HTTP/1.0 404 Not Found");
        die("{\"errorCode\": -404, \"body\": \"Book not found\"}");
    }
    $conn->close();

    echo json_encode($result);
}

function getBooksByIdUser() {
    if(!SessionManager::isLogged()) {
        header("HTTP/1.0 403 Forbidden");
        die("{\"errorCode\": -4, \"body\": \"User is not logged\"}");
    }
    $idUser = SessionManager::getIdUser();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT b.* FROM books b NATURAL JOIN payments WHERE id_user = ?"
    );
    $stmt->bind_param("i", $idUser);
    $success = $stmt->execute();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }
    $r = $stmt->get_result();
    while($row = $r->fetch_object()) {
        array_push($result->body->bookList, $row);
    }
    $conn->close();

    echo json_encode($result);
}

?>
