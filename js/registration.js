window.addEventListener("load", function f() {
    button_registration.addEventListener("click", registration);

    password.addEventListener("keydown", function() {
        if (password.value == "") {
            pwd_strength.innerText = "";
            return;
        }
        let res = zxcvbn(password.value, [email.value]);
        pwd_strength.innerText = "Strength: " + res.score;
    	var s = "";
		for(var i in res.feedback.suggestions) {
			s += res.feedback.suggestions[i]+ "\n";
		}
		
		sugg.innerText = s;
	});
});



function registration(ev) {
    ev.preventDefault();
    let email_ = email.value;
    let password_ = password.value;
    let question1_ = question1.value;
    let question2_ = question2.value;
    let question3_ = question3.value;

	if (question1_ == "" || question2_ == "" ||question3_ == "" ){
		alert("Insert some security answer");
		return;
	}
	
    let answers_ = JSON.stringify([question1_, question2_, question3_]);

    let res = zxcvbn(password_, [email_]);
    if (res.score < 4) {
		var s = "";
		for(var i in res.feedback.suggestions) {
			s += res.feedback.suggestions[i]+ "\n";
		}
        alert(s);
        return;
    }

    let parameters = createFormData(
        [
            {
                key: "email",
                value: email_
            },
            {
                key: "password",
                value: password_
            },
            {
                key: "answers",
                value: answers_
            }
		]
    );
    doAjaxPost("api/signin.php", parameters, onOperationCompleted);
}



function onOperationCompleted(responseText) {
    let response = JSON.parse(responseText);
    if (response.errorCode == 0 || response.errorCode == -2) {
        alert("User registration succeded");
        window.location.replace("login.php");
    } else if (response.errorCode == -400) {
        alert("Error: missing parameters");
    } else if (response.errorCode == -4) {
        alert("User not valid");
    } else if (response.errorCode == -5) {
        alert("Email not valid");
    } else if (response.errorCode == -2) {
        alert("User already logged, please logout if you want to use registration");
    }
}
