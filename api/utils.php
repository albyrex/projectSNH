<?php

/*

  Generic utility functions section

*/

function checkPostParameterOrDie($parName) {
    if(!isset($_POST[$parName]) || $_POST[$parName] == "") {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    return $_POST[$parName];
}

function checkPostNumericParameterOrDie($parName) {
    if(!isset($_POST[$parName]) || $_POST[$parName] == "" || !is_numeric($_POST[$parName])) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    return (int)$_POST[$parName];
}

function checkGetParameterOrDie($parName) {
    if(!isset($_GET[$parName]) || $_GET[$parName] == "") {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    return $_GET[$parName];
}

function checkGetNumericParameterOrDie($parName) {
    if(!isset($_GET[$parName]) || $_GET[$parName] == "" || !is_numeric($_GET[$parName])) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    return (int)$_GET[$parName];
}

/*
  If $email contains at leats a character not in the whitelist \w@., this
  function return true.
  true is also returned when
    - no "@" are found in $email
    - "@" is found at the beginning of $email
    - "@" is found at the end of $email
    - more than a "@" are found
*/
function invalidEmail($email) {
    if($email === "")
        return true;
    if(preg_match('/[^\w@.]/', $email))
        return true;
    $pos = strpos($email, "@");
    if($pos === false || $pos === 0 || $pos === (strlen($email)-1))
        return true;
    return (strpos($email, "@", $pos+1) !== false);
}

/*
  Filter htmlentities but not ' nor "
*/
function filterHtml($string) {
    return htmlspecialchars($string, ENT_NOQUOTES | ENT_SUBSTITUTE);
}

/*
  Filter htmlentities including ' and "
*/
function filterHtmlAndQuotes($string) {
    return htmlspecialchars($string, ENT_QUOTES | ENT_SUBSTITUTE);
}


/*

  Other utility functions

*/

function getBookPath($idBook) {
    include_once "dbAccess.php";
    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT id_book FROM books WHERE id_book=?");
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
        return "../pdfs/" . $row["id_book"] . ".pdf";
    } else {
        $conn->close();
        return -1;
    }
}

function parseAnswers($answers) {
    //json_decode(...) returns NULL if $answers is not a valid string
    //representation for a json object. In this case the following if is
    //"triggered".
    $obj = json_decode(strtolower($answers));
    if(count($obj) != 3) {
        header("HTTP/1.0 400 Bad Request");
        die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
    }
    $arr = array();
    for ($i = 0; $i < 3; $i++) {
    	if(!is_string($obj[$i])) {
            header("HTTP/1.0 400 Bad Request");
            die("{\"errorCode\": -400, \"body\": \"Bad request\"}");
        }
    	array_push($arr,(string)$obj[$i]);
    }
    return json_encode($arr);
}

?>
