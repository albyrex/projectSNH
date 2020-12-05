<?php

/*
  This page lets the administrator manage the site
  Valid errorCode values:
    - 0: successful operation
    - negative number: error -> see "body"
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php";


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
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
    }
    // Check if user is an admin
    if(!SessionManager::isAdmin()) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -2, \"body\": \"User is not an admin\"}");
    }
}


$function = checkPostParameterOrDie("function");
if($function === "addBook")
    addBook();
else if($function === "removeBookById")
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
    $price = checkPostParameterOrDie("price");

    //Check if the file is correctly uploaded
    if(!isset($_FILES["book"]["tmp_name"]) || $_FILES["book"]["tmp_name"] == "") {
        header("HTTP/1.0 400 Bad Request");
        die("Bad request: file not uploaded correctly");
    }

    // Check file size: max book size = 128MiB
    if ($_FILES["book"]["size"] > 134217728) {
        header("HTTP/1.0 400 Bad Request");
        die("Bad request: file too large");
    }

    // Check file type
    if(!isAValidPdf($_FILES["book"]["tmp_name"])) {
        header("HTTP/1.0 400 Bad Request");
        die("Bad request: wrong file type");
    }

    // Insert the new book in the database
    $conn = getDbConnection();
    $stmt = $conn->prepare(
        "INSERT INTO books(title,author,price) VALUES (?,?,?)"
    );
    $stmt->bind_param(
        "ssd", $title, $author, $price
    );
    $success = $stmt->execute();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("Internal server error");
    }

    // Retrive the new assigned index
    $stmt = $conn->prepare(
        "SELECT id_book FROM books WHERE title = ? AND author = ?"
    );
    $stmt->bind_param(
        "ss", $title, $author
    );
    $success = $stmt->execute();
    if($success === false) {
        header("HTTP/1.0 500 Internal Server Error");
        die("Internal server error");
    }
    $r = $stmt->get_result();
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $idBook = $row["id_book"];
        $conn->close();
    } else {
        $conn->close();
        header("HTTP/1.0 500 Internal Server Error");
        die("Internal server error");
    }

    $targetFile = getBookPath($idBook);

    // Check if file already exists
    if(file_exists($targetFile)) {
        header("HTTP/1.0 500 Internal Server Error");
        die("Internal server error");
    }
    move_uploaded_file($_FILES["book"]["tmp_name"], $targetFile);

    die("<html><body>Ok, book uploaded correctly. <a href='/index.php'>Homepage</a></body></html>");
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

    unlink($path);

    die("{\"errorCode\": 0, \"body\": \"Ok\"}");
}


/*
  Utility functions
*/
function isAValidPdf($filepath) {
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $ret = false;
    if(finfo_file($finfo, $filepath) === "application/pdf")
        $ret = true;
    finfo_close($finfo);
    return $ret;
}


?>
