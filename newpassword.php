<?php

/*
  This page allow a user to recover his password.
  No "manually" access to this page can be performed: only a valid link received
  by email can let the user access correctly this page.
*/

include_once "./api/sessionManager.php";
include_once "./api/utils.php";

if(!isset($_POST["newpwd1"]) || $_POST["newpwd1"] == "") {
	$token = checkGetParameterOrDie("token");
	$email = checkGetParameterOrDie("email");
	if(invalidEmail($email)) {
		header("HTTP/1.0 400 Bad Request");
		die("Error: invalid request");
	}
	if(!SessionManager::validPasswordRecoveryToken($email, $token)) {
		header("HTTP/1.0 400 Bad Request");
		die("Error: invalid request");
	}
?>
<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" href="css/style.css">
	<link rel="icon" type="image/png" href="icon.png">
	<title>NEW PASSWORD</title>
	<script type="application/javascript" src="./js/zxcvbn.js"></script>
	<script type="application/javascript">
		window.addEventListener("load", function() {
			newpwd1.addEventListener("keydown", function() {
				let res = zxcvbn(newpwd1.value);
				pwd_strength.innerText = "Strength: " + res.score;
			});
			submit_pwd_recovery.onclick = function(ev) {
				let res = zxcvbn(newpwd1.value);
				if(res.score < 4) {
					alert("The password is too weak");
					ev.preventDefault();
				}
			}
		});
	</script>
</head>

<body>
	<h1>INSERT NEW PASSWORD</h1>
	<br><br><br><br>
    <form method="post" action="newpassword.php">
	  	<label for="newpwd1">NEW PASSWORD</label><br>
	  	<input type="password" id="newpwd1" name="newpwd1"><span style="margin-left:10px" id="pwd_strength"></span><br><br>
		<label for="newpwd2">REPEAT NEW PASSWORD</label><br>
	  	<input type="password" id="newpwd2" name="newpwd2"><br><br>
	  	<input type="submit" value="CHANGE YOUR PASSWORD" id="submit_pwd_recovery">
		<input type="text" style="display:none" name="token" value="<?php echo $token; ?>">
		<input type="text" style="display:none" name="email" value="<?php echo $email; ?>">
	</form>
</body>

</html>
<?php
	die("");
}


// The user has already typed his new pasword. Let's change it!
$newpwd1 = checkPostParameterOrDie("newpwd1");
$newpwd2 = checkPostParameterOrDie("newpwd2");
$token = checkPostParameterOrDie("token");
$email = checkPostParameterOrDie("email");

if($newpwd1 !== $newpwd2) {
	header("HTTP/1.0 400 Bad Request");
	die("Error: the two passwords do not correspond");
}

if(invalidEmail($email)) {
	header("HTTP/1.0 400 Bad Request");
	die("Error: invalid request");
}

$operationResult = SessionManager::changeUserPasswordByPasswordRecoveryToken($email, $token, $newpwd1);

if($operationResult !== $operationSuccessful) {
	header("HTTP/1.0 400 Bad Request");
	die("Error: invalid request");
}

?>

<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
	<title>PASSWORD RECOVERED</title>
	<script type="application/javascript">
		window.addEventListener("load", function() {
			alert("Password changed correctly! You will be redirected to the login page.");
			window.location.href = "login.php";
		});
	</script>
</head>

<body></body>

</html>
