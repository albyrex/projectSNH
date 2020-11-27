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
$loginSuccess = 0;
$userUnderBruteforceProtection = -3;

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
        return 0;
    }

    public static function userCanDownload($idUser, $idBook) {
        include_once "dbAccess.php";

        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT id_user FROM payments WHERE id_user=? AND id_book=?");
        $stmt->bind_param("ii", $idUser, $idBook);
        $success = $stmt->execute();
        $result = $stmt->get_result();
        if($result->num_rows > 0) {
            return true;
        }
        return false;
    }

    public static function sessionStart($email, $password) {
        global $userNotFound, $wrongPassword, $loginSuccess;
        global $userUnderBruteforceProtection, $maxConsecutiveFailedLoginCount;

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
        $consecutiveFailedLoginCount = 0;
        $failedLoginTimestamp = 0;
        SessionManager::updateDbLoginInfo($conn, $email, 0, 0);
        $conn->close();
        if(session_status() == PHP_SESSION_NONE) {
            session_start();
        }
        $_SESSION["id_user"] = $row["id_user"];
        $_SESSION["email"] = $row["email"];
        $_SESSION["username"] = $row["username"];
        $_SESSION["admin"] = $row["admin"];
        return $loginSuccess;
    }

    private static function updateDbLoginInfo($conn, $email, $consecutiveFailedLoginCount, $failedLoginTimestamp) {
        $stmt = $conn->prepare(
            "UPDATE users SET consecutive_failed_login_count = ?, failed_login_timestamp = ? WHERE BINARY email = ?"
        );
        $stmt->bind_param("iis", $consecutiveFailedLoginCount, $failedLoginTimestamp, $email);
        $stmt->execute();
    }

}

?>
