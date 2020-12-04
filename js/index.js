


window.addEventListener("load", function f() {
    let parameters = createFormData(
        [
		{key:"function", value: "topDownloaded"}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBookTopDownload);

	doAjaxPost("api/userStatus.php", [], function(responseText) {
		let response = JSON.parse(responseText);
		if(response.body.status == 1) {
			let link_ad = document.createElement("a");
			link_ad.innerText = "Admin";
			link_ad.href = "admin.html";
			navbar.appendChild(link_ad);
		}
		let lout = document.createElement("a");
		lout.innerText = "Logout";
		lout.href = "javascript:logout()";
		navbar.appendChild(lout);
	});

	searchBook.addEventListener("change", function(ev) {
		let parameters = createFormData(
        [
		{key:"function", value: "searchByTitleOrAuthor"},
		{key:"searchString", value: ev.target.value},
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, searchByTitleOrAuthor);
	});
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
    var b_td = document.createElement("td");
    b_td.appendChild(b);

		t.innerText = book.title;
		a.innerText = book.author;

        if(book.icanbuy == 0) { //gia comprato
            b.innerText = "Download";
        }else{ //non valido
            b.innerText = book.price  + "€" + " - Buy";
        }

		b.setAttribute("idBook", book.id_book);
    b.setAttribute("icanbuy", book.icanbuy);
		b.addEventListener("click", goToBilling);
		x.appendChild(t);
		x.appendChild(a);
		x.appendChild(b_td);

		topdownloaded.appendChild(x)
	}
}

function goToBilling(ev) {
	ev.preventDefault();
	let idBook = ev.target.getAttribute("idBook");
    let icanbuy = ev.target.getAttribute("icanbuy");

    if(icanbuy == 0) { //gia comprato
    window.location.href = "api/downloadBook.php?idBook=" + idBook;
    }else if(icanbuy == 1) {  //ok devo comprare
    window.location.href = "./billing.html?idBook=" + idBook;
    }else{ //non valido
    window.location.href = "index.html";
    }
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
		var book = listbook[i];
		var x = document.createElement("tr");
		var t = document.createElement("td");
		var a = document.createElement("td");
		var b = document.createElement("button");
    var b_td = document.createElement("td");
    b_td.appendChild(b);

		t.innerText = book.title;
		a.innerText = book.author;

        if(book.icanbuy == 0) { //gia comprato
            b.innerText = "Download";
        }else{ //non valido
            b.innerText = book.price  + "€" + " - Buy";
        }

		b.setAttribute("idBook", book.id_book);
    b.setAttribute("icanbuy", book.icanbuy);
		b.addEventListener("click", goToBilling);
		x.appendChild(t);
		x.appendChild(a);
		x.appendChild(b_td);

		booklist.appendChild(x)
	}
}


function clearTable(tab) {
	while(tab.children.length > 1) {
		var lastchild = tab.children[tab.children.length -1];
		tab.removeChild(lastchild);
	}
}


function logout() {
	doAjaxPost("api/logout.php", [], function() {
		window.location.href = "login.html";
	});
}
