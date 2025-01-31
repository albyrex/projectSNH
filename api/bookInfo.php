<?php

/*
  Functions:
    - topDownloaded -> return the $TOP_DOWNLOADED_SIZE top most downloaded books
    - getBookById -> get a book specifying its id_book
    - getUserBooks -> retrieve all books bought by the currently logged user
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
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}

$function = checkPostParameterOrDie("function");
if($function === "topDownloaded")
    topDownloaded();
else if($function === "getBookById")
    getBookById();
else if($function === "getUserBooks")
    getUserBooks();
else if($function === "searchByTitleOrAuthor")
    searchByTitleOrAuthor();
else {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}


function topDownloaded() {
	global $TOP_DOWNLOADED_SIZE;

    $idUser = SessionManager::getIdUser();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $r = $conn->query(
        "SELECT b.id_book, title, author, price, IF((
        	SELECT p2.id_user FROM payments p2 WHERE p2.id_book = b.id_book AND p2.id_user = $idUser
        ) is NULL, 1, 0) AS icanbuy,
        COUNT(p.id_user) AS downloadCount
        FROM books b LEFT OUTER JOIN payments p ON (b.id_book = p.id_book)
        GROUP BY b.id_book
        ORDER BY downloadCount DESC
        LIMIT $TOP_DOWNLOADED_SIZE"
    );
    while($row = $r->fetch_object()) {
        array_push($result->body->bookList, sanitizeBookRow($row));
    }
    $conn->close();

    echo json_encode($result);
}

function getBookById() {
    $idBook = checkPostNumericParameterOrDie("idBook");
    $idUser = SessionManager::getIdUser();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();

    $conn = getDbConnection();

    //Retrieve the book
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
        $row = $r->fetch_object();
        $result->body->book = sanitizeBookRow($row);
    } else {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }

    //Can i buy it?
    $stmt = $conn->prepare(
        "SELECT * FROM payments WHERE id_book = ? AND id_user = ?"
    );
    $stmt->bind_param("ii", $idBook, $idUser);
    $success = $stmt->execute();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }
    $r = $stmt->get_result();
    if($r->num_rows > 0) {
        $result->body->icanbuy = 0;
    } else {
        $result->body->icanbuy = 1;
    }

    $conn->close();

    echo json_encode($result);
}

function getUserBooks() {
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
        array_push($result->body->bookList, sanitizeBookRow($row));
    }
    $conn->close();

    echo json_encode($result);
}

function searchByTitleOrAuthor() {
    $searchString = checkPostParameterOrDie("searchString");
    $idUser = SessionManager::getIdUser();

    if(strlen($searchString) < 3) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }

    if(preg_match('/(%|__)/', $searchString)) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    $searchString = "%$searchString%";

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->bookList = array();

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "SELECT id_book, title, author, price, IF((
        	SELECT p.id_user FROM payments p WHERE p.id_book = b.id_book AND p.id_user = ?
        ) is NULL, 1, 0) AS icanbuy
        FROM books b
        WHERE title LIKE ? OR author LIKE ?
        LIMIT 10"
    );
    $stmt->bind_param("iss", $idUser, $searchString, $searchString);
    $success = $stmt->execute();

    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }
    $r = $stmt->get_result();

    while($row = $r->fetch_object()) {
        array_push($result->body->bookList, sanitizeBookRow($row));
    }
    $conn->close();

    echo json_encode($result);
}


/*
  Utility Functions
*/
function sanitizeBookRow($row) {
    $row->id_book = $row->id_book;
    $row->title = filterHtmlAndQuotes($row->title);
    $row->author = filterHtmlAndQuotes($row->author);
    $row->price = $row->price;
    return $row;
}

?>
