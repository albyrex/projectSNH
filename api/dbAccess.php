<?php

/*
  Pagina contenente le informazioni di accesso al DB.
  Tutte le pagine che interagiscono con il DB fanno riferimento a questa pagina.
  Mette a disposizione la variabile $conn che rappresenta la connessione col DB.
*/

$db_servername = "localhost";
$db_username = "cappelliniserver";
$db_password = "";
$db_dbname = "my_cappelliniserver";

function getDbConnection() {
    global $db_servername, $db_username, $db_password, $db_dbname;
    // Create connection
    $conn = new mysqli($db_servername, $db_username, $db_password, $db_dbname);
    // Check connection
    if($conn->connect_error){
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
