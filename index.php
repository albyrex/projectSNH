<?php

include_once "./api/sessionManager.php";


header("HTTP/1.0 307 Temporary Redirect");

if(SessionManager::isLogged()) {
    header("Location: ./home.php");
} else {
    header("Location: ./login.php");
}

?>
