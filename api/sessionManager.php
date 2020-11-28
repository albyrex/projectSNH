<?php

/*
  Service page to manage the user session.
  A user session is composed by:
   - id_user
   - username
   - admin
*/

if(session_status() == PHP_SESSION_NONE) {
    @session_start();
}


$userNotFound = -1;
$wrongPassword = -2;
$operationSuccessful = 0;
$userUnderBruteforceProtection = -3;
$emailNotVerified = -4;
$emailAlreadyVerified = -5;

$loginBruteforceProtectionInterval = 600; //10 minutes
$maxConsecutiveFailedLoginCount = 5;


class SessionManager {

    public static function isLogged() {
        return isset($_SESSION["id_user"]);
    }

    public static function isAdmin() {
        if(!isset($_SESSION["admin"]))
            return false;
        return $_SESSION["admin"] == 1;
    }

    public static function hashPassword($password) {
        $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
        if($hashedPassword == false)
            return "invalid hash";
        else
            return $hashedPassword;
    }

    public static function getIdUser() {
        return $_SESSION["id_user"];
    }

    public static function getEmail() {
        return $_SESSION["email"];
    }

    public static function getUsername() {
        return $_SESSION["username"];
    }

    public static function closeSession() {
        // session_start();
        // remove all session variables
        session_unset();
        // destroy the session
        session_destroy();
    }

    /*
      Returns:
        - 0 in case of sucess
        - -1 in case of failure if the email is already in use
    */
    public static function createUser($email, $username, $password, $answers) {
        global $operationSuccessful;
		
		include_once "dbAccess.php";
		 
        $hashedPassword = SessionManager::hashPassword($password);

        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO users(email,username,password,answers) VALUES (?,?,?,?)");
        $stmt->bind_param("ssss", $email, $username, $hashedPassword, $answers);
        $success = $stmt->execute();
        $conn->close();
        if($success === false) {
            return -1;
        }
        if(SessionManager::sendVerificationEmail($email))
            return $operationSuccessful;
        else
            return -1;
    }

    public static function userCanDownload($idUser, $idBook) {
        include_once "dbAccess.php";

        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id_user FROM payments WHERE id_user = ? AND id_book = ?");
        $stmt->bind_param("ii", $idUser, $idBook);
        $success = $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public static function changeUserPassword($idUser, $oldpwd, $newpwd) {
        global $userNotFound, $wrongPassword, $operationSuccessful;

        include_once "dbAccess.php";

        // Retrive the user from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
        $stmt->bind_param("i", $idUser);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $conn->close();
            return $userNotFound;
        }

        // Check if the old password is wrong
        if(!password_verify($oldpwd, $row["password"])) {
            $conn->close();
            return $wrongPassword;
        }

        // Update the password in the database
        $stmt = $conn->prepare(
            "UPDATE users SET password = ? WHERE id_user = ?"
        );
		$newHashedPassword = SessionManager::hashPassword($newpwd);
		$stmt->bind_param("si", $newHashedPassword, $idUser);
        $stmt->execute();
        $conn->close();

        return $operationSuccessful;
    }

    public static function changeUserPasswordByToken($email, $token, $newpwd) {
        global $userNotFound, $wrongPassword, $operationSuccessful;

        include_once "dbAccess.php";

        // Retrive the user from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT email_verification_token FROM users WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $conn->close();
            return $userNotFound;
        }

        // Check if the token is wrong
        if($row["email_verification_token"] != $token) {
            $conn->close();
            return $wrongPassword;
        }

        // Update the password in the database
        $stmt = $conn->prepare(
            "UPDATE users SET password = ? WHERE email = ?"
        );
        $stmt->bind_param("ss", hashPassword($newpwd), $email);
        $stmt->execute();
        $conn->close();

        return $operationSuccessful;
    }

    public static function sessionStart($email, $password) {
        global $userNotFound, $wrongPassword, $operationSuccessful;
        global $userUnderBruteforceProtection, $maxConsecutiveFailedLoginCount;
        global $emailNotVerified;

        include_once "dbAccess.php";

        // Retrive the user from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE BINARY email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $conn->close();
            return $userNotFound;
        }

        // Check if the email is verified
        if($row["verified_email"] != 1) {
            $conn->close();
            return $emailNotVerified;
        }

        // Check if the user can login or the bruteforce protection has to kick in
        $now = time();
        $failedLoginTimestamp = (int)$row["failed_login_timestamp"];
        $consecutiveFailedLoginCount = (int)$row["consecutive_failed_login_count"];
        if(
            $consecutiveFailedLoginCount >= $maxConsecutiveFailedLoginCount &&
            $failedLoginTimestamp != 0 &&
            $failedLoginTimestamp + $loginBruteforceProtectionInterval > $now
        ) {
            SessionManager::updateDbLoginInfo($conn, $email, $consecutiveFailedLoginCount+1, $now);
            $conn->close();
            return $userUnderBruteforceProtection;
        }

        // Check if the password is wrong
        if(!password_verify($password, $row["password"])) {
            SessionManager::updateDbLoginInfo($conn, $email, $consecutiveFailedLoginCount+1, $now);
            $conn->close();
            return $wrongPassword;
        }

        // If I'm here the login credentials are correct and the user can
        // finally login
        SessionManager::updateDbLoginInfo($conn, $email, 0, 0);
        $conn->close();
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["id_user"] = $row["id_user"];
        $_SESSION["email"] = $row["email"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["admin"] = $row["admin"];
        return $operationSuccessful;
    }

    public static function validToken($email, $token) {
        include_once "dbAccess.php";

        // Retrive the user given his email and his token
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT id_user FROM users WHERE email = ? AND token = ?"
        );
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    }

    public static function validIdBook($idBook) {
        include_once "dbAccess.php";

        // Retrive the book given his id_book
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT id_book FROM books WHERE id_book = ?"
        );
        $stmt->bind_param("i", $idBook);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $conn->close();
            return true;
        } else {
            $conn->close();
            return false;
        }
    }

    public static function verifyEmail($email, $token) {
        global $emailNotVerified, $operationSuccessful, $emailAlreadyVerified;

        include_once "dbAccess.php";

        // Retrive the user given his email and verification token
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT verified_email FROM users WHERE email = ? AND email_verification_token = ?"
        );
        $stmt->bind_param("ss", $email, $token);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
        } else {
            $conn->close();
            return $emailNotVerified;
        }

        // Check if it is already verified
        if($row["verified_email"] != 0) {
            $conn->close();
            return $emailAlreadyVerified;
        }

        // Update the database record
        $stmt = $conn->prepare(
            "UPDATE users SET verified_email = 1, email_verification_token = NULL WHERE email = ?"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $conn->close();

        return $operationSuccessful;
    }


    /*
       Private functions
    */

    private static function updateDbLoginInfo($conn, $email, $consecutiveFailedLoginCount, $failedLoginTimestamp) {
        $stmt = $conn->prepare(
            "UPDATE users SET consecutive_failed_login_count = ?, failed_login_timestamp = ? WHERE BINARY email = ?"
        );
        $stmt->bind_param("iis", $consecutiveFailedLoginCount, $failedLoginTimestamp, $email);
        $stmt->execute();
    }

    private static function sendVerificationEmail($email) {
        include_once "dbAccess.php";
        require "./lib/PHPMailer/PHPMailer.php";
        require "./lib/PHPMailer/SMTP.php";

        // Generate random token
        $token = bin2hex(random_bytes(16));

        // Store the token in the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "UPDATE users SET email_verification_token = ? WHERE BINARY email = ?"
        );
        $stmt->bind_param("ss", $token, $email);
        $stmt->execute();
        $conn->close();

        // Prepare and send the email
        $verificationUrl = "https://localhost/api/verifyEmail.php?token=$token&email=$email";
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer;
            $mail->setFrom("noreply@bookshop.com");
            $mail->addAddress($email);
            $mail->Subject = "E-mail verification for bookshop";
            $mail->Body = "Your verification link is $verificationUrl";
            $mail->IsSMTP();
            $mail->SMTPSecure = "ssl";
            $mail->Host = "ssl://smtp.gmail.com";
            $mail->SMTPAuth = true;
            $mail->Port = 465;
            $mail->Username = "ebookunipi@gmail.com";
            $mail->Password = "phpmailertest*";
            $mail->send();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }

}

?>
