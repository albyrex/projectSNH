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

$passwordPreSalt = "euidh"
$passwordPostSalt = "euiwhd"


class SessionManager {

    public static function isLogged() {
        return isset($_SESSION["name"]);
    }

    public static function isAdmin() {
        if(!isset($_SESSION["admin"]))
            return false;
        return $_SESSION["admin"] == 1;
    }

    public static function hashPassword($password) {
        $hashedPassword = hash("sha256", $passwordPreSalt . $password . $passwordPostSalt);
        if($hashedPassword == false)
            return "invalid hash"
        else
            return $hashedPassword;
    }

    public static function sessionStart($email, $password) {
        global $userNotFound, $wrongPassword, $loginSuccess;

        include_once "libSql.php";

        $conn = getDbConnection();
        $stmt = $conn->prepare("SELECT * FROM users WHERE BINARY email=?");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            $conn->close();
        } else {
            $conn->close();
            return $userNotFound;
        }

        if($row["password"] === hashPassword($password)) {
            if(session_status() == PHP_SESSION_NONE) {
                session_start();
            }
            $_SESSION["id_user"] = $row["id_user"];
            $_SESSION["email"] = $row["email"];
            $_SESSION["username"] = $row["username"];
            $_SESSION["admin"] = $row["admin"];
            return $loginSuccess;
        } else {
            return $wrongPassword;
        }
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
        - -2 in case of failure if the username is already in use
        - -3 in other cases of failure
    */
    public static function createUser($email, $username, $password) {
        include_once "libSql.php";

        $hashedPassword = hashPassword($password);

        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO users(email,username,password) VALUES (?,?,?)");
        $stmt->bind_param("sss", $email, $username, $hashedPassword);
        $success = $stmt->execute();
        if($success === false) {
            //User creation failed. Let's discover why.
            //Maybe the email is already in use
            /*$stmt = $conn->prepare("SELECT * FROM users WHERE BINARY email=?");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $conn->close();
                return -1;
            }*/
            //Maybe the username is already in use
            /*$stmt = $conn->prepare("SELECT * FROM users WHERE BINARY username=?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            if($result->num_rows > 0) {
                $conn->close();
                return -2;
            }*/
            $conn->close();
            return -3;
        }
        $conn->close();
        return 0;
    }

    public static function userCanDownload($idUser, $idBook) {
        include_once "libSql.php";

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

}

?>
