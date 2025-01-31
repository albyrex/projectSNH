<?php
include_once "api/sessionManager.php";
include_once "api/utils.php";

if(!SessionManager::isLogged()) {
	header("HTTP/1.0 307 Temporary Redirect");
    header("Location: ./login.php");
	die();
}


?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <script type="application/javascript" src="./js/ajax.js"></script>
	<script type="application/javascript" src="./js/profile.js"></script>
	<script type="application/javascript" src="./js/zxcvbn.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>PROFILE</title>
</head>

<body>
	<h1>PROFILE</h1>
	<h1 id="username"></h1>
	<div id="navbar">
		<a href="index.php">Home</a>
		<a href="profile.php">Profile</a>
		<a href="api/logout.php">Logout</a>
	</div>
	<br><br><br><br>
  <h3>Your books</h3>
	<div style="vertical-align: top">
		<div style="display:inline-block; margin-bottom: 50px;min-width: 600px;vertical-align: top">
			<table id="booklistdownload" style="width:500px; border: 1px solid black;">
					<tr>
				<th>Title</th>
				<th>Author</th>
				<th>Download</th>
					</tr>
			</table>
		</div>
		<div style="display:inline-block">
			<form>
				<span>Change your password</span><br><br>
				<input type="password" id="oldpwd" placeholder="Write here your old password"><br><br>
				<input type="password" id="newpwd1" placeholder="Write here your new password"><span style="margin-left:10px" id="new_pwd_strength"></span><br><br>
				<input type="password" id="newpwd2" placeholder="Repeat here your new password"><br><br>
				<button id="button_changepwd">Change</button>
			</form>
            <p>If you fail too much attempts, you must wait 10 minutes before try again</p>
			<div id = "sugg"></div>
		</div>
	</div>


</body>

</html>
