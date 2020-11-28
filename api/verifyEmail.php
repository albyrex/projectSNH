<?php

/*
  This page allow a user to verify his email.
  The link to this page is provided via an email.
*/

include_once "sessionManager.php";
include_once "utils.php";

$email = checkGetParameterOrDie("email");
$token = checkGetParameterOrDie("token");

if(invalidEmail($email)) {
    header("HTTP/1.0 400 Bad Request");
    die("400 Bad Request");
}

$operationResult = SessionManager::verifyEmail($email, $token);

if($operationResult != $operationSuccessful) {
    die("Something went wrong");
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<title>EMAIL VERIFIED</title>
    <script type="application/javascript">
        var sec = 5;
        window.addEventListener("load" function() {
            let interval = setInterval(function(){
                countdown.innerText = sec;
                if(sec <= 0) {
                    clearInterval(interval);
                    window.location.href = "../login.html";
                }
                sec--;
            }, 1000);
        });
    </script>
</head>

<body>
	<h1>EMAIL VERIFIED</h1>
	<div>
        <p>Your email is now verified. You will be redirected to the login page in <span id="countdown">5</span> seconds</p>
    </div>
</body>

</html>
