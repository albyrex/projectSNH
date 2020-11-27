import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';


window.addEventListener("load", function f() {	    
    let parameters = createFormData(
        [
		{key:"function", value: "topDownloaded"}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBookTopDownload);
	
	searchBook.setAttribute("change", searchBook);	
});


function populateBookTopDownload(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode < 0) {
        alert("Error requesting data.\nError message: " + response.body);
        return;
    }
	
	let listbook = response.body.bookList;
	
	clearTable(topdownloaded);
	
	for (var i = 0; i < listbook.length; ++i) {
		var x = document.createElement("tr"); 
		var t = document.createElement("td"); 
		var a = document.createElement("td");
		var b = document.createElement("button")
		
		t.innerText = = i.title;
		a.innerText = i.author;
		b.innerText = i.price + " - Buy";
		b.setAttribute("idBook", i.idBook);
		b.setAttribute("onClick", goToBilling);		
		x.appendChild(t);
		x.appendChild(a);
		x.appendChild(b);
		
		topdownloaded.appendChild(x)
	}	
}

function goToBilling(ev) {
	ev.preventDefault();
	let idBook = ev.target.getAttribute("idBook");
	window.location.href = "./billing.html?idBook=" + idBook;
	return;
}


function searchBook(str) {
	let parameters = createFormData(
        [
		{key:"function", value: "...."}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, searchBook);	
}


function searchByTitleOrAuthor(responseText) {
let response = JSON.parse(responseText);
    if(response.errorCode < 0) {
        alert("Error requesting data.\nError message: " + response.body);
        return;
    }
	
	let listbook = response.body.bookList;
	
	clearTable(booklist);
	
	for (var i = 0; i < listbook.length; ++i) {
		var x = document.createElement("tr"); 
		var t = document.createElement("td"); 
		var a = document.createElement("td");
		var b = document.createElement("button")
		
		t.innerText = = i.title;
		a.innerText = i.author;
		b.innerText = i.price + " - Buy";
		b.setAttribute("idBook", i.idBook);
		b.setAttribute("onClick", goToBilling);		
		x.appendChild(t);
		x.appendChild(a);
		x.appendChild(b);
		
		booklist.appendChild(x)
	}	
}


function clearTable(tab) {
	while(tab.children.length > 1) {
		var lastchild = tab.children[tab.children.length -1];
		tab.removeChild(lastchild);
	}
}