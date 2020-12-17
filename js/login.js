window.addEventListener("load", function f() {
    button_login.addEventListener("click", login);
});



function login(ev) {
    ev.preventDefault();
    let email_ = email.value;
    let password_ = password.value;
    let parameters = createFormData(
        [
            {
                key: "email",
                value: email_
            },
            {
                key: "password",
                value: password_
            }
		]
    );
    doAjaxPost("api/login.php", parameters, onOperationCompleted);
}



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
    if (response.errorCode == 0) {
        if (response.body == 0) {
            window.location.replace("index.php");
		}
    } else if (response.errorCode == -2) {
        alert("User already logged, please logout if you want to login again");
    } else if (response.errorCode == -400) { //mancano parametri
        alert("Error: missing parameters");
    } else if (response.errorCode == -4) { //errore parametri
        alert("User not exists or wrong password");
    }
}
