<?php

include_once "./api/sessionManager.php";


header("HTTP/1.0 307 Temporary Redirect");

if(SessionManager::isLogged()) {
    header("Location: ./home.html");
} else {
    header("Location: ./login.html");
}

?>
