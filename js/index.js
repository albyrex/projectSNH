


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
		let book = listbook[i];
		var x = document.createElement("tr"); 
		var t = document.createElement("td"); 
		var a = document.createElement("td");
		var b = document.createElement("button");
		
		t.innerText = book.title;
		a.innerText = book.author;
		b.innerText = book.price + " - Buy";
		b.setAttribute("idBook", book.id_book);
		b.addEventListener("click", goToBilling);		
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


function seachBook(ev) {
	let parameters = createFormData(
        [
		{key:"function", value: "searchByTitleOrAuthor"},
		{key:"searchString", value: ev.target.value},
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBook);
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
		var b = document.createElement("button");
		
		t.innerText = i.title;
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