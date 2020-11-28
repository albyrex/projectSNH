


window.addEventListener("load", function f() {
	button_recovery.addEventListener("click", recovery);
});



function recovery(ev) {
	ev.preventDefault();
	let email_ = email.value;
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
    doAjaxPost("api/password-recovery.php", parameters, onOperationCompleted);
}



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
}