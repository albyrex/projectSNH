<?php

/*
 * Library for string filtering
*/

include_once "dbAccess.php";


/*
La seguente funzione è basata su quella spiegata a lezione
e prensente nelle slide di Javascript.

get_magic_quotes_gpc() restituisce la configurazione corrente
dei setting di magic_quotes_gpc.

When magic_quotes are on for GPC (Get/Post/Cookie) operations,
all ', ", \ and NUL's are escaped with a backslash automatically –
This feature has been removed in PHP 5.4.0 and the function returns
always false

stripslashes(...) returns a string with backslashes stripped off.
(\' becomes ' and so on.) Double backslashes (\\) are made into a
single backslash (\).
*/
/*function filterSQLQuery($string, $conn) {
    if(get_magic_quotes_gpc()){
        $string = stripslashes($string);
    }

    if (!is_numeric($string))
        //$string = "'" . mysqli_real_escape_string($conn, $string) . "'";
        $string = mysqli_real_escape_string($conn, $string);

    return $string;
}*/



/*
 * The following functions are used for filtering strings in different ways
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
