<?php

if ($_POST["newpwd1"] == "" || !isset($_POST["newpwd1"])) {
	
}else{
	
}

?>


<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <title>NEW PASSWORD</title>
</head>

<body>
	<h1>INSERT NEW PASSWORD</h1>
	<br><br><br><br>
    <form method="post" action="newpassword.php">  
	  	<label for="newpwd1">NEW PASSWORD</label><br>
	  	<input type="password" id="newpwd1" name="newpwd1"><br><br>
		<label for="newpwd2">REPEAT NEW PASSWORD</label><br>
	  	<input type="password" id="newpwd2" name="newpwd2"><br><br>
	  	<input type="submit" value="CHANGE YOUR PASSWORD">
		<input type="text" style="display:none" name="token">
		<input type="text" style="display:none" name="email">
	</form>	
</body>

</html>