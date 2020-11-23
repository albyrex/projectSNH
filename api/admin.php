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
else if($function == "removeBookById")
    removeBookById();
else {
    header("HTTP/1.0 400 Bad Request");
    die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
}


/**
 * Add a new book
 */
function addBook() {
    $title = checkPostParameterOrDie("title");
    $author = checkPostParameterOrDie("author");
    $data = checkPostParameterOrDie("date");
    $abstract = checkPostParameterOrDie("abstract");
    $price = checkPostParameterOrDie("price");

    //ATTENZIONE! Vanno sanificati title, author, abstract e price?
    //ATTENZIONE! Da title si ricava il nome del pdf, quindi ocio che non deve
    //essere una path

    $targetFile = "../pdfs/$title.pdf";

    if(
        !isset($_FILES["cover"]["tmp_name"]) || $_FILES["cover"]["tmp_name"] == "" ||
        !isset($_FILES["book"]["tmp_name"]) || $_FILES["book"]["tmp_name"] == ""
    ) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }

    // Check if file already exists
    if (file_exists($target_file)) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request: file already exists\"}"); //Togliere!! E' solo a scopo di debug
    }

    // Check file size: max cover size = 512KiB, max book size = 128MiB
    if ($_FILES["cover"]["size"] > 524288 || $_FILES["book"]["size"] > 134217728) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request: file too large\"}");
    }

    $coverBase64 = getBase64FromFile($_FILES["cover"]["tmp_name"]);

    $result = new stdClass();
    $result->errorCode = 0;

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "INSERT INTO books(title,author,date,cover,abstract,price,path) VALUES (?,?,?,?,?,?,?)"
    );
    $stmt->bind_param(
        "sssssds", $title, $author, $data, $coverBase64, $abstract, $price, $targetFile
    );
    $success = $stmt->execute();
    $conn->close();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }

    move_uploaded_file($_FILES["book"]["tmp_name"], $targetFile);

    $result->body = "Ok";

    echo json_encode($result);
}

function removeBookById() {
    $idBook = checkPostNumericParameterOrDie("idBook");

    $path = getBookPath($idBook);
    if($path === -1) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }

    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "DELETE FROM books WHERE id_book = ?"
    );
    $stmt->bind_param("i", $idBook);
    $success = $stmt->execute();
    $conn->close();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("{\"errorCode\": -500, \"body\": \"Internal server error\"}");
    }

    die("{\"errorCode\": 0, \"body\": \"Ok\"}");
}


/*
  Utility functions
*/
function getBase64FromFile($filepath) {
    $imgbinary = fread(fopen($filepath, "r"), filesize($filepath));
    return base64_encode($imgbinary);
}

?>
