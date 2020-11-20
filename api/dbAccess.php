<?php

/*
  Libray used to connect to a MySQL databse.
  Every page that wants to connect to the database must use the
  getDbConnection() function of this library.
  It will return a connection object (mysqli).
*/

function getDbConnection() {
    // Create connection
    $conn = new mysqli(
        "localhost",          //Server address
        "root",               //DBMS user
        "",                   //DBMS user password
        "my_cappelliniserver" //DB name
    );
    // Check connection
    if($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    return $conn;
}

?>
