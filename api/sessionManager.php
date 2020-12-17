<?php

/*
  Service page to manage the user session.
  A user session is composed by:
   - id_user
   - email
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
$invalidRequest = -6;
$genericError = -100;

$loginBruteforceProtectionInterval = 600; //10 minutes
$maxConsecutiveFailedLoginCount = 5;
$changePasswordBruteforceInterval = 600; //10 minuts
$maxConsecutiveFailedPasswordChanges = 5;


class SessionManager {

    public static function isLogged() {
        return isset($_SESSION["id_user"]);
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
    public static function createUser($email, $password, $answers) {
        global $operationSuccessful;

		include_once "dbAccess.php";

        $hashedPassword = SessionManager::hashPassword($password);

        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO users(email,password,answers) VALUES (?,?,?)");
        $stmt->bind_param("sss", $email, $hashedPassword, $answers);
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
        global $changePasswordBruteforceInterval, $userUnderBruteforceProtection;
        global $maxConsecutiveFailedPasswordChanges;

        include_once "dbAccess.php";

        // Retrive the user from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT password, last_password_change_attempt, consecutive_failed_password_changes
            FROM users WHERE id_user = ?"
        );
        $stmt->bind_param("i", $idUser);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $lpca = (int)$row["last_password_change_attempt"];
            $cfpg = (int)$row["consecutive_failed_password_changes"];
        } else {
            $conn->close();
            return $userNotFound;
        }

        // Check last_password_change_attempt
        $now = time();
        if(
            $cfpg >= $maxConsecutiveFailedPasswordChanges &&
            $lpca !== 0 &&
            $lpca + $changePasswordBruteforceInterval > $now
        ) {
            SessionManager::updatePasswordChangeInfo($conn,$idUser,$cfpg,$now);
            $conn->close();
            return $userUnderBruteforceProtection;
        }

        // Check if the old password is wrong
        if(!password_verify($oldpwd, $row["password"])) {
            SessionManager::updatePasswordChangeInfo($conn,$idUser,$cfpg+1,$now);
            $conn->close();
            return $wrongPassword;
        }

        // The user is allowed to change his password
        SessionManager::updatePasswordChangeInfo($conn,$idUser,0,0);

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

    public static function changeUserPasswordByPasswordRecoveryToken($email, $token, $newpwd) {
        global $invalidRequest, $wrongPassword, $operationSuccessful;

        include_once "dbAccess.php";

        // Retrive the recovery request from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT id_user, password_recovery_token
            FROM password_recovery_requests
            WHERE id_user = (SELECT id_user FROM users WHERE email = ?)"
        );
        $stmt->bind_param("s", $email);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idUser = $row["id_user"];
        } else {
            $conn->close();
            return $invalidRequest;
        }

        // Check if the token is wrong
        if($row["password_recovery_token"] !== $token) {
            $conn->close();
            return $wrongPassword;
        }

        // Update the password in the database
        $stmt = $conn->prepare(
            "UPDATE users SET password = ? WHERE email = ?"
        );
        $hashedPassword = SessionManager::hashPassword($newpwd);
        $stmt->bind_param("ss", $hashedPassword, $email);
        $stmt->execute();

        // Remove the password recovery request
        $stmt = $conn->prepare(
            "DELETE FROM password_recovery_requests WHERE id_user = ?"
        );
        $stmt->bind_param("i", $idUser);
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
        if((int)$row["verified_email"] !== 1) {
            $conn->close();
            return $emailNotVerified;
        }

        // Check if the user can login or the bruteforce protection has to kick in
        $now = time();
        $failedLoginTimestamp = (int)$row["failed_login_timestamp"];
        $consecutiveFailedLoginCount = (int)$row["consecutive_failed_login_count"];
        if(
            $consecutiveFailedLoginCount >= $maxConsecutiveFailedLoginCount &&
            $failedLoginTimestamp !== 0 &&
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
        $_SESSION["id_user"] = (int)$row["id_user"];
        $_SESSION["email"] = $row["email"];
        return $operationSuccessful;
    }

    public static function validPasswordRecoveryToken($email, $token) {
        include_once "dbAccess.php";

        // Retrive the user given his email and his token
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT u.id_user
            FROM users u NATURAL JOIN password_recovery_requests prr
            WHERE u.email = ? AND prr.password_recovery_token = ?"
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
        if((int)$row["verified_email"] !== 0) {
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

    public static function createPasswordRecoveryRequest($email, $answers) {
        global $userNotFound, $operationSuccessful, $genericError;

        include_once "dbAccess.php";

        // Retrive the user from the database
        $conn = getDbConnection();
        $stmt = $conn->prepare(
            "SELECT id_user FROM users WHERE email = ? AND answers = ?"
        );
        $stmt->bind_param("ss", $email, $answers);
        $stmt->execute();

        // Check if it really exists
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $idUser = $row["id_user"];
        } else {
            $conn->close();
            return $userNotFound;
        }

        // Check if a valid password recovery request is already present
        $stmt = $conn->prepare(
            "SELECT * FROM password_recovery_requests WHERE id_user = ?"
        );
        $stmt->bind_param("i", $idUser);
        $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            // If yes, we have to delete it
            $stmt = $conn->prepare(
                "DELETE FROM password_recovery_requests WHERE id_user = ?"
            );
            $stmt->bind_param("i", $idUser);
            $stmt->execute();
        }

        // Create a new password recovery request
        $now = time();
        $token = bin2hex(random_bytes(16));
        $stmt = $conn->prepare(
            "INSERT INTO password_recovery_requests(id_user,password_recovery_token,password_recovery_timestamp) VALUES (?,?,?)"
        );
        $stmt->bind_param("isi", $idUser, $token, $now);
        $stmt->execute();
        $conn->close();

        // Send the email
        if(SessionManager::sendPasswordRecoveryRequestEmail($email, $token))
            return $operationSuccessful;
        else
            return $genericError;
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

    private static function updatePasswordChangeInfo($conn, $idUser, $consecutiveFailedPasswordChanges, $lastPasswordChangeAttempt) {
        $stmt = $conn->prepare(
            "UPDATE users SET consecutive_failed_password_changes = ?, last_password_change_attempt = ? WHERE id_user = ?"
        );
        $stmt->bind_param("iii", $consecutiveFailedPasswordChanges, $lastPasswordChangeAttempt, $idUser);
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

    private static function sendPasswordRecoveryRequestEmail($email, $token) {
        require "./lib/PHPMailer/PHPMailer.php";
        require "./lib/PHPMailer/SMTP.php";

        $url = "https://localhost/newpassword.php?token=$token&email=$email";
        try {
            $mail = new PHPMailer\PHPMailer\PHPMailer;
            $mail->setFrom("noreply@bookshop.com");
            $mail->addAddress($email);
            $mail->Subject = "Password recovery request for bookshop account";
            $mail->Body = "You can reset your password clicking on the following link: $url";
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
