<?php

/*
  Functions:
    - topDownloaded -> return the $TOP_DOWNLOADED_SIZE top most downloaded books
    - getBookById -> get a book specifying its id_book
    - getBooksByIdUser -> retrieve all books bought by the currently logged user
    - searchByTitleOrAuthor -> retrieve the books whose title or author matches
          the search criteria. Maximum number of results: $SEARCH_RESULT_MAX_SIZE
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php";


$TOP_DOWNLOADED_SIZE = 10;
$SEARCH_RESULT_MAX_SIZE = 10;


if(!SessionManager::isLogged()) {
    header("HTTP/1.0 403 Forbidden");
    die("{\"errorCode\": -4, \"body\": \"User is not logged\"}");
}

$function = checkPostParameterOrDie("function");
if($function == "topDownloaded")
    topDownloaded();
else if($function == "getBookById")
    getBookById();
else if($function == "getBooksByIdUser")
    getBooksByIdUser();
else if($function == "searchByTitleOrAuthor")
    searchByTitleOrAuthor();
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
    $conn->query("SET CHARACTER SET utf8");
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
    $conn->query("SET CHARACTER SET utf8");
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
    $idUser = SessionManager::getIdUser();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $conn->query("SET CHARACTER SET utf8");
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

function searchByTitleOrAuthor() {
    $searchString = checkPostParameterOrDie("searchString");

    if(strlen($searchString) < 3) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Invalid request: too short search string\"}");
    }

    if(preg_match('/(%|__)/', $searchString)) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Invalid request\"}");
    }
    $searchString = "%$searchString%";

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $conn->query("SET CHARACTER SET utf8");
    $stmt = $conn->prepare(
        "SELECT id_book, title, author, date, cover, abstract, price
        FROM books
        WHERE title LIKE ? OR author LIKE ?
        LIMIT 10"
    );
    $stmt->bind_param("ss", $searchString, $searchString);
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
