<?php
include_once "api/sessionManager.php";
include_once "api/utils.php";

if(SessionManager::isLogged()) {
	header("HTTP/1.0 307 Temporary Redirect");
    header("Location: ./home.php");
	die();
}


?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <script type="application/javascript" src="./js/ajax.js"></script>
	<script type="application/javascript" src="./js/login.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>LOGIN</title>
</head>

<body>
	<h1>LOGIN</h1>
	<div id="navbar">
		<a href="login.php">Login</a>
		<a href="registration.php">Registration</a>
	</div>
	<br><br><br><br>
    <form>

	  <label for="email">EMAIL</label><br>
	  <input type="email" id="email" name="email"><br><br>

	  <label for="password">PASSWORD</label><br>
	  <input type="password" id="password" name="password"><br><br>

	  <button id="button_login">LOGIN</button>

	  <br><br>
	  <a href="password-recovery.php">Forgot Password?</a>
	</form>
	<div style="margin-top: 30px">
		<p>In case of consecutive login errors, you must wait 10 minutes before try again</p>
	</div>
</body>

</html>
