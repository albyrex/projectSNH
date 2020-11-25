import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';


window.addEventListener("load", function f() {
	button_registration.addEventListener("click", registration);
});



function registration(ev) {
	ev.preventDefault();
	let email_ = email.value;
	let password_ = password.value;
	let username_ = username.value;
	let question1_ = question1.value;
	let question2_ = question2.value;
	let question3_ = question3.value;
	
	let answers_ = JSON.stringify([question1_, question2_, question3_]);
	
	let parameters = createFormData(
        [
		{key:"email", value: email_},
		{key:"password", value: password_},
		{key:"username", value: username_},
		{key:"answers", value: answers_}
		]
    );
    doAjaxPost("api/signin.php", parameters, onOperationCompleted);
}



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode == 0 || response.errorCode == -2) {
		if(response.body == 0){
            window.location.replace("./login.html");
		}
	}else if(response.errorCode == -400){
		alert("Error: missing parameters");
	}else if(response.errorCode == -4){
		alert("User not valid");
	}else if(response.errorCode == -5){
		alert("Email not valid");
	}else if(response.errorCode == -2){
		alert("User already logged, please logout if you want to use registration");
	}
}