<?php

/*
  Page to download a PDF book
*/

include_once "sessionManager.php";
include_once "dbAccess.php";


function getBookPath($idBook) {
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT path FROM books WHERE id_book=?");
    $stmt->bind_param("i", $idBook);
    $success = $stmt->execute();
    if($success === false){
        $conn->close();
        return -1;
    }
    $result = $stmt->get_result();
    if($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $conn->close();
        return "../pdfs/" . $row["path"];
    } else {
        $conn->close();
        return -1;
    }
}


// Check if the user is already logged
if(!SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}

// Check parameters
if(!isset($_POST["idBook"]) || $_POST["idBook"] === "" || !is_numeric($_POST["idBook"]))
    die("{\"errorCode\": -3, \"body\": \"Bad request (Missing or invalid idBook)\"}");
else
    $idBook = $_POST["idBook"];

if(SessionManager::userCanDownload(SessionManager::getIdUser(),$idBook) === false) {
    header('HTTP/1.0 403 Forbidden');
    die("Error 403: Forbidden");
}

$bookPath = getBookPath($idBook);

if($bookPath === -1) {
    header('HTTP/1.0 403 Forbidden');
    die("Error 403: Forbidden");
}

header("Content-type:application/pdf");
header("Content-Disposition:attachment;filename='book.pdf'");
readfile($bookPath);

?>
