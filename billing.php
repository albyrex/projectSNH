<?php
include_once "api/sessionManager.php";
include_once "api/utils.php";

if(!SessionManager::isLogged()) {
	header("HTTP/1.0 307 Temporary Redirect");
    header("Location: ./login.php");
	die();
}

$idBook = checkGetNumericParameterOrDie("idBook");
$path = getBookPath($idBook);
if ($path === -1) {
	header("HTTP/1.0 404 Bad Request");
	die("Bad Request");
}

# true: user cannot buy but can download already
# false: user has to buy
if (SessionManager::userCanDownload(SessionManager::getIdUser(), $idBook) ) {
	header("HTTP/1.0 307 Temporary Redirect");
    header("Location: api/downloadBook.php?idBook=$idBook");
	die();
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <script type="application/javascript" src="./js/ajax.js"></script>
	<script type="application/javascript" src="./js/billing.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>BILLING</title>
</head>

<body>
	<h1>BILLING</h1>
	<div id="navbar">
		<a href="index.php">Home</a>
		<a href="profile.php">Profile</a>
		<a href="api/logout.php">Logout</a>
	</div>
	<br><br><br><br>
  <h3>You are buying:</h3>
	<div id="book" style="border:1px solid black; display:inline-block; padding:10px">
		<p id="title"></p>
		<p id="author"></p>
		<p id="price"></p>
	</div>
  <br><br><br>
    <form>

	  <label for="cardname">NAME ON CARD</label><br>
	  <input type="text" id="cardname" name="cardname"><br><br>

	  <label for="cardnumber">CARD NUMBER</label><br>
	  <input type="text" id="cardnumber" name="cardnumber"><br><br>

	  <label for="cvv">CVV</label><br>
	  <input type="number" id="cvv" name="cvv"><br><br>

	  <label for="monthyear">EXPIRATION MONTH - YEAR</label><br>
	  <input type="month" id="monthyear" name="monthyear"><br><br>

	  <button id="button_pay">PAY</button>

	</form>
</body>

</html>
