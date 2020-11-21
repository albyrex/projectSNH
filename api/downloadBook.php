<?php

/*
  Page to download a PDF book
*/

include_once "sessionManager.php";
include_once "dbAccess.php";
include_once "utils.php";


// Check if the user is already logged
if(!SessionManager::isLogged()) {
    die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
}

// Check parameters
$idBook = checkPostNumericParameterOrDie("idBook");

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
