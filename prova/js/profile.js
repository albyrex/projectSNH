import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';





window.addEventListener("load", function f() {	    
    doAjaxPost("api/userStatus.php", [], setUsername);
	let parameters = createFormData(
        [
		{key:"function", value: "getUserBooks"}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBookDownload);
});






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
		var b = document.createElement("button")
		
		t.innerText = = i.title;
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
	window.location.href = "downloadBood.php?idBook=" + idBook
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