<?php
include_once "api/sessionManager.php";
include_once "api/utils.php";

if(SessionManager::isLogged()) {
	header("HTTP/1.0 400 Bad Request");
	die("You are already logged");
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <script type="application/javascript" src="./js/ajax.js"></script>
	<script type="application/javascript" src="./js/password-recovery.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>PASSWORD RECOVERY</title>
</head>

<body>
	<h1>PASSWORD RECOVERY</h1>
	<div id="navbar">
		<a href="login.php">Login</a>
		<a href="registration.php">Registration</a>
	</div>
	<br><br><br><br>
    <form>

	  <label for="email">EMAIL</label><br>
	  <input type="email" id="email" name="email"><br><br>

	  <label for="question1">What is the name of the person you gave the first kiss to?</label><br>
	  <input type="text" id="question1" name="question1"><br><br>

	  <label for="question2">What city did you visit on your first trip abroad?</label><br>
	  <input type="text" id="question2" name="question2"><br><br>

	  <label for="question3">What is your favorite movie?</label><br>
	  <input type="text" id="question3" name="question3"><br><br>


	  <button id="button_recovery">RECOVERY</button>

	</form>
</body>

</html>
