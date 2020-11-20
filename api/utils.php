<?php

/*
  This page provides utility functions
*/

function checkPostParameterOrDie($parName) {
    if(!isset($_POST[$parName]) || $_POST[$parName] == "")
        die("{\"errorCode\": -3, \"body\": \"Invalid request\"}");
    return $_POST[$parName];
}

/*
  If $username contains at leats a character not in the whitelist \w, this
  function return true.
*/
function invalidUsername($username) {
    if($username === "")
        return true;
    return preg_match('/[^\w]/', $username);
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
    return (strpos($email, "@", $pos) != false);
}

/*
  Vecchie funzioni. Rimuovere?
*/
function filterHtml($string) {
    //Does not filter ' nor "
    return htmlentities($string, ENT_NOQUOTES | ENT_SUBSTITUTE);
}

function filterSQLQueryAndHtml($string) {
    //Does not filter ' nor "
    return filterSQLQuery(htmlentities($string, ENT_NOQUOTES | ENT_SUBSTITUTE));
}

function filterHtmlAndQuotes($string) {
    //Does filter ' and "
    return htmlentities($string, ENT_QUOTES | ENT_SUBSTITUTE);
}

?>
