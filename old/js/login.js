import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';

let ui = {
    email_input: null,
	password_input: null
}

window.addEventListener("load", function f() {

	ui.email_input = document.getElementById("email");
	ui.password_input = document.getElementById("password");
    button_login.addEventListener("click", onButtonLoginClicked);
});



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode == 0 || response.errorCode == -2) {
		if(response.body == 0){
            window.location.replace("index.php");
		}else if(response.body == 1){
			window.location.replace("admin.html");
		}
	}else if(response.errorCode == -400){ //mancano parametri
		alert("Errore: parametri mancanti o non validi");
	}else if(response.errorCode == -4){ //errore parametri
		alert("Utente non esistente o password errata");
	}

}



function onButtonLoginClicked(ev) {
	ev.preventDefault();
	if(ui.email_input.value == "") {
        alert("Per favore inserisci una valida email");
        return;
    }
	if(ui.password_input.value == "") {
        alert("Per favore inserisci una valida password");
        return;
    }

    let parameters = createFormData([
        {key:"function", value:"login"},
		{key:"email", value:ui.email_input.value},
		{key:"password", value:ui.password_input.value}
    ]);
    doAjaxPost("api/login.php", parameters, onOperationCompleted);
}
