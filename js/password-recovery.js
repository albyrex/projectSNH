window.addEventListener("load", function f() {
    button_recovery.addEventListener("click", recovery);
});



function recovery(ev) {
    ev.preventDefault();
    let email_ = email.value;
    let question1_ = question1.value;
    let question2_ = question2.value;
    let question3_ = question3.value;

    let answers_ = JSON.stringify([question1_, question2_, question3_]);

    let parameters = createFormData(
        [
            {
                key: "email",
                value: email_
            },
            {
                key: "answers",
                value: answers_
            }
		]
    );
    doAjaxPost("api/requestPasswordRecovery.php", parameters, onOperationCompleted);
}



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
    if (response.errorCode != 0) {
        alert("Something went wrong. Check the email and the security answers you had inserted");
        return;
    }
    alert("You request has been sent correctly. Check your email inbox.");
    window.location.href = "login.php";
}
