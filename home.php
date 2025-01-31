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
	<script type="application/javascript" src="./js/home.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>HOME</title>
</head>

<body>
	<h1>HOME</h1><br><br>
	<div id="navbar">
		<a href="index.php">Home</a>
		<a href="profile.php">Profile</a>
		<a href="api/logout.php">Logout</a>
	</div>
	<br><br><br><br>
	<div>
		<span style="font-weight:bold">Top Downloaded</span><br><br>
		<table id="topdownloaded" style="width:500px; border: 1px solid black;">
		  <tr>
			<th>Title</th>
			<th>Author</th>
			<th>Price</th>
		  </tr>
		</table>
	</div>
	<br><br><br><br>
	<div>
		<div>
			<input type="text" id="searchBook" placeholder="Search by title or author..">
		</div><br>
		<table id="booklist" style="width:500px; border: 1px solid black;">
		  <tr>
			<th>Title</th>
			<th>Author</th>
			<th>Price</th>
		  </tr>
		</table>
	</div>
</body>

</html>
