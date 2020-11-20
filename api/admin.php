<?php

/*
  Pagina che mette in atto le richieste di gestione del sito effettuate dagli utenti amministratori
  tramite la pagina admin.php.
  Legenda errorCode:
    - 0: tutto ok, la risposta è nel body
    - numero negativo: impossibile continuare, si veda il body che fa da error message
*/

include_once "sessionManager.php";
include_once "libSql.php";


/**
 * ATTENZIONE!
 * Se $DEBUG_MODE è true, tutti i controlli sull'utente vengono saltati e le
 * funzionalità di amministrazione saranno disponibili a tutti, anche a client
 * non loggati.
 */
$DEBUG_MODE = false;

if(!$DEBUG_MODE) {
    // Gestione del caso di errore in cui l'utente non sia loggato
    if(!SessionManager::isLogged()) {
        die("{\"errorCode\": -2, \"body\": \"User is not logged\"}");
    }
    // Gestione del caso di errore in cui l'utente loggato non sia admin
    if(!SessionManager::isAdmin()) {
        die("{\"errorCode\": -3, \"body\": \"User is not an admin\"}");
    }
}

if(!isset($_POST["function"]) || $_POST["function"] == "")
    die("{\"errorCode\": -4, \"body\": \"function parameter not specified\"}");
else if($_POST["function"] == "getInfo")
    getInfo();
else if($_POST["function"] == "updateRules")
    updateRules();
else if($_POST["function"] == "getRoles")
    getRoles();
else if($_POST["function"] == "updateEventSiteLink")
    updateEventSiteLink();
else if($_POST["function"] == "updateDescription")
    updateDescription();
else if($_POST["function"] == "getUserList")
    getUserList();
else if($_POST["function"] == "getUser")
    getUser();
else if($_POST["function"] == "getFaqList")
    getFaqList();
else if($_POST["function"] == "deleteFaq")
    deleteFaq();
else if($_POST["function"] == "updateFaq")
    updateFaq();
else if($_POST["function"] == "addFaq")
    addFaq();
else if($_POST["function"] == "addUser")
    addUser();
else if($_POST["function"] == "updateUser")
    updateUser();
else if($_POST["function"] == "deleteUser")
    deleteUser();
else if($_POST["function"] == "addRole")
    addRole();
else if($_POST["function"] == "updateRole")
    updateRole();
else if($_POST["function"] == "deleteRole")
    deleteRole();
else
    die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione


/**
 * Restituisce tutte le informazioni di cui ha bisogno la pagina admin
 */
function getInfo() {
    $result = new stdClass();
    $result->errorCode = 0;
    $body = new stdClass();

    $conn = getDbConnection();
    $r = $conn->query("SELECT * FROM resources WHERE resource_name = 'rules'");
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $body->rules = $row["value"];
    } else {
        $body->rules = "N/D";
    }

    $r = $conn->query("SELECT * FROM resources WHERE resource_name = 'eventSiteLink'");
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $body->eventSiteLink = $row["value"];
    } else {
        $body->eventSiteLink = "N/D";
    }

    $r = $conn->query("SELECT * FROM resources WHERE resource_name = 'description'");
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $body->description = $row["value"];
    } else {
        $body->description = "N/D";
    }

    $conn->close();

    $result->body = $body;
    echo json_encode($result);
}

/**
 * Restituisce la lista di tutti gli utenti
 */
function getUserList() {
    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->userList = array();

    $conn = getDbConnection();
    $r = $conn->query("SELECT * FROM users WHERE admin != 1");
    while($row = $r->fetch_object()) {
        array_push($result->body->userList, $row);
    }
    $conn->close();

    echo json_encode($result);
}

/**
 * Restituisce un solo utente. L'utente è identificato tramite nome e cognome
 * che arrivano come stringhe di oggetti json {"name":"Pippo","surname":"Pluto"}
 * nella variabile $_POST["user"]
 */
function getUser() {
    if(!isset($_POST["user"]) || $_POST["user"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $user_ = json_decode($_POST["user"]);

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();

    $conn = getDbConnection();
    $stmt = $conn->prepare("SELECT * FROM users JOIN roles ON users.role_name = roles.name WHERE users.name = ? AND users.surname = ?");
    $stmt->bind_param("ss", $user_->name, $user_->surname);
    $stmt->execute();
    $r = $stmt->get_result();
    if($r->num_rows > 0) {
        $row = $r->fetch_assoc();
        $result->body->user = $row;
    } else {
        $result->body->user = "N/D";
    }

    $conn->close();
    echo json_encode($result);
}

/**
 * Restituisce la lista di tutte le faq
 */
function getFaqList() {
    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->faqList = array();

    $conn = getDbConnection();
    $r = $conn->query("SELECT * FROM faqs");
    while($row = $r->fetch_object()) {
        array_push($result->body->faqList, $row);
    }
    $conn->close();

    echo json_encode($result);
}


/**
 * Restituisce la lista di tutti i ruoli
 */
function getRoles() {
    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = new stdClass();
    $result->body->roleList = array();

    $conn = getDbConnection();
    $r = $conn->query("SELECT * FROM roles");
    while($row = $r->fetch_object()) {
        array_push($result->body->roleList, $row);
    }
    $conn->close();

    echo json_encode($result);
}


/**
 * Aggiorna il regolamento prendendo il nuovo regolamento dalla variabile
 * $_POST["rules"]
 */
function updateRules() {
    if(!isset($_POST["rules"]) || $_POST["rules"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $rules = $_POST["rules"];

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE resources SET value = ? WHERE resource_name = 'rules'");
    $stmt->bind_param("s", $rules);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

/**
 * Aggiorna il link dell'evento prendendo quello nuovo dalla variabile
 * $_POST["eventSiteLink"]
 */
function updateEventSiteLink() {
    if(!isset($_POST["eventSiteLink"]) || $_POST["eventSiteLink"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $eventSiteLink = $_POST["eventSiteLink"];

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE resources SET value = ? WHERE resource_name = 'eventSiteLink'");
    $stmt->bind_param("s", $eventSiteLink);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

/**
 * Aggiorna la descrizione dell'evento prendendo quella nuova dalla variabile
 * $_POST["description"]
 */
function updateDescription() {
    if(!isset($_POST["description"]) || $_POST["description"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $description = $_POST["description"];

    $conn = getDbConnection();
    $stmt = $conn->prepare("UPDATE resources SET value = ? WHERE resource_name = 'description'");
    $stmt->bind_param("s", $description);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

/**
 * Elimina una faq in base all'id che trova in $_POST["faqId"]
 */
function deleteFaq() {
    if(!isset($_POST["faqId"]) || $_POST["faqId"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $faqId = $_POST["faqId"];

    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM faqs WHERE id = ?");
    $stmt->bind_param("i", $faqId);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}



function deleteUser() {
    if(!isset($_POST["user"]) || $_POST["user"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

	$user = json_decode($_POST["user"]);

    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM users WHERE name = ? AND surname = ?");
    $stmt->bind_param("ss", $user->name, $user->surname);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

/**
 * Crea una nuova faq. La faq è passata come stringa json in $_POST["faq"].
 */
function addFaq() {
    if(!isset($_POST["faq"]) || $_POST["faq"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione
    $faq = json_decode($_POST["faq"]);

    $conn = getDbConnection();
    $stmt = $conn->prepare("INSERT INTO faqs (question, answer) VALUES (?, ?)");
    $stmt->bind_param("ss", $faq->question, $faq->answer);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

/**
 * Aggiunge un nuovo utente. Le informazioni sono recuperate dalla variabile
 * $_POST["user"] che contiene la stringa json rappresentante l'utente.
 */
function addUser() {
    if(!isset($_POST["user"]) || $_POST["user"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    try {
        $user = json_decode($_POST["user"]);
        if(isset($user->roleName) && $user->roleName != "")
            $userRoleName = $user->roleName;
        else
            $userRoleName = NULL;

        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO users (name, surname, password, note, role_name, admin) VALUES (?, ?, ?, ?, ?, 0)");
        $stmt->bind_param(
            "sssss",
            $user->name,
            $user->surname,
            $user->password,
            $user->note,
            $userRoleName
        );
        $stmt->execute();

        $conn->close();

        $result = new stdClass();
        $result->errorCode = 0;
        $result->body = "done";
        echo json_encode($result);
    }
    catch (Exception $e) {
        die("{\"errorCode\": -10, \"body\": \"Parameters not correct\"}");
    }
}

/**
 * Aggiorna un utente esistente. Le informazioni sono recuperate dalla variabile
 * $_POST["user"] che contiene la stringa json rappresentante l'utente.
 */
function updateUser() {
    if(!isset($_POST["user"]) || $_POST["user"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    try {
        $user = json_decode($_POST["user"]);
        if(isset($user->roleName) && $user->roleName != "")
            $userRoleName = $user->roleName;
        else
            $userRoleName = NULL;

        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE users SET password = ?, note = ?, role_name = ? WHERE name = ? AND surname = ?");
        $stmt->bind_param(
            "sssss",
            $user->password,
            $user->note,
            $userRoleName,
            $user->name,
            $user->surname
        );
        $stmt->execute();

        $conn->close();

        $result = new stdClass();
        $result->errorCode = 0;
        $result->body = "done";
        echo json_encode($result);
    }
    catch (Exception $e) {
        die("{\"errorCode\": -10, \"body\": \"Parameters not correct\"}");
    }
}

/**
 * Crea un nuovo role. Il role è passato come stringa json in $_POST["role"].
 */
function addRole() {
    if(!isset($_POST["role"]) || $_POST["role"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    try {
        $role = json_decode($_POST["role"]);

        $conn = getDbConnection();
        $stmt = $conn->prepare("INSERT INTO roles (name, description, url_image) VALUES (?, ?, ?)");
        $stmt->bind_param("sss", $role->name, $role->description, $role->urlImage);
        $stmt->execute();

        $conn->close();

        $result = new stdClass();
        $result->errorCode = 0;
        $result->body = "done";
        echo json_encode($result);
    }
    catch (Exception $e) {
        die("{\"errorCode\": -10, \"body\": \"Parameters not correct\"}");
    }
}

/**
 * Aggiorna un role preesistente. Il role è passato come una stringa json in
 * $_POST["role"].
 */
function updateRole() {
    if(!isset($_POST["role"]) || $_POST["role"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    try {
        $role = json_decode($_POST["role"]);

        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE roles SET description = ?, url_image = ? WHERE name = ?");
        $stmt->bind_param(
            "sss",
            $role->description,
            $role->urlImage,
            $role->name
        );
        $stmt->execute();

        $conn->close();

        $result = new stdClass();
        $result->errorCode = 0;
        $result->body = "done";
        echo json_encode($result);
    }
    catch (Exception $e) {
        die("{\"errorCode\": -10, \"body\": \"Parameters not correct\"}");
    }
}





/**
 * Aggiorna una faq preesistente. La faq è passato come una stringa json in
 * $_POST["faq"].
 */
function updateFaq() {
    if(!isset($_POST["faq"]) || $_POST["faq"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    try {
        $role = json_decode($_POST["faq"]);

        $conn = getDbConnection();
        $stmt = $conn->prepare("UPDATE faqs SET question = ?, answer = ? WHERE id = ?");
        $stmt->bind_param(
            "ssi",
            $role->question,
            $role->answer,
            $role->id
        );
        $stmt->execute();

        $conn->close();

        $result = new stdClass();
        $result->errorCode = 0;
        $result->body = "done";
        echo json_encode($result);
    }
    catch (Exception $e) {
        die("{\"errorCode\": -10, \"body\": \"Parameters not correct\"}");
    }
}


/**
 * Rimuove un role preesistente identificato dalla variabile $_POST["roleName"]
 */
function deleteRole() {
    if(!isset($_POST["roleName"]) || $_POST["roleName"] == "")
        die("{\"errorCode\": -1, \"body\": \"General error\"}"); //Previene attacchi di enumerazione

    $roleName = $_POST["roleName"];

    $conn = getDbConnection();
    $stmt = $conn->prepare("DELETE FROM roles WHERE name = ?");
    $stmt->bind_param("s", $roleName);
    $stmt->execute();

    $conn->close();

    $result = new stdClass();
    $result->errorCode = 0;
    $result->body = "done";
    echo json_encode($result);
}

?>
