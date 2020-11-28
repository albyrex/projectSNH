

window.addEventListener("load", function f() {	    
    doAjaxPost("api/userStatus.php", [], setUsername);
	let parameters = createFormData(
        [
		{key:"function", value: "getUserBooks"}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBookDownload);
	
	button_changepwd.addEventListener("click", changePassword);
	
	newpwd1.addEventListener("change", function() {
		if(newpwd1.value == "") {
			new_pwd_strength.innerText = "";
			return;
		}
		let res = zxcvbn(newpwd1.value);
		new_pwd_strength.innerText = "Strength: " + res.score;
	});	
});


function changePassword(ev) {
	ev.preventDefault();
	let oldpwd_ = oldpwd.value;
	let newpwd1_ = newpwd1.value;
	let newpwd2_ = newpwd2.value;
	
	if(newpwd1_ != newpwd2_) {
		alert("The new passwords are not corresponding");
		return;
	}
	let res = zxcvbn(newpwd1_);
	if(res.score < 4) {
	 alert("The new password is not strong enough");
	 return;
	}
	
	if(oldpwd_ == newpwd1_) {
		alert("The old and the new passwords are the same");
		return;
	}
	
 	let parameters = createFormData(
        [
		{key:"oldpwd", value: oldpwd_},
		{key:"newpwd", value: newpwd1_}
		]
    );
    doAjaxPost("api/changePassword.php", parameters, function() {
		let response = JSON.parse(responseText);
		if(response.errorCode == - 2) {
		 alert("Wrong old password");
		 return;
		}
		if(response.errorCode < 0) {
        	alert("Error requesting data.\nError message: " + response.body);
        	return;
    	}
		alert("Password changed correctly");
		oldpwd.value = "";
		newpwd1.value = "";
		newpwd2.value = "";
		return;
	});
}

function setUsername(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode < 0) {
        alert("Error requesting data.\nError message: " + response.body);
        return;
    }
	
	username.innerText = response.body.username;
}




function populateBookDownload(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode < 0) {
        alert("Error requesting data.\nError message: " + response.body);
        return;
    }
	
	let listbook = response.body.bookList;
	
	clearTable(booklistdownload);
	
	for (var i = 0; i < listbook.length; ++i) {
		var x = document.createElement("tr"); 
		var t = document.createElement("td"); 
		var a = document.createElement("td");
		var b = document.createElement("button");
		
		t.innerText = i.title;
		a.innerText = i.author;
		b.innerText = "Download";
		b.setAttribute("idBook", i.idBook);
		b.setAttribute("onClick", directDownload);		
		x.appendChild(t);
		x.appendChild(a);
		x.appendChild(b);
		
		booklistdownload.appendChild(x)
	}	
}


function directDownload(ev) {
	var idBook = ev.target.getAttribute("idBook");
	window.location.href = "api/downloadBood.php?idBook=" + idBook
}


function logout() {
	doAjaxPost("api/logout.php", [], function() {
		window.location.href = "login.html";
	});
}


function clearTable(tab) {
	while(tab.children.length > 1) {
		var lastchild = tab.children[tab.children.length -1];
		tab.removeChild(lastchild);
	}
}