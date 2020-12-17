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
	<script type="application/javascript" src="./js/registration.js"></script>
	<script type="application/javascript" src="./js/zxcvbn.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<title>REGISTRATION</title>
</head>

<body>
	<h1>REGISTRATION</h1>
	<div id="navbar">
		<a href="login.php">Login</a>
		<a href="registration.php">Registration</a>
	</div>
	<br><br><br><br>
    <form>

	  <label for="email">EMAIL</label><br>
	  <input type="email" id="email" name="email"><br><br>

	  <label for="password">PASSWORD</label><br>
	  <input type="password" id="password" name="password"><span style="margin-left:10px" id="pwd_strength"></span><br><br>

	  <label for="question1">QUESTION1</label><br>
	  <input type="text" id="question1" name="question1"><br><br>

	  <label for="question2">QUESTION2</label><br>
	  <input type="text" id="question2" name="question2"><br><br>

	  <label for="question3">QUESTION3</label><br>
	  <input type="text" id="question3" name="question3"><br><br>


	  <button id="button_registration">REGISTRATION</button>

	</form>
</body>

</html>
