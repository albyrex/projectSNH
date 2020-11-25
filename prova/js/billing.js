import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';


function getParams(param) {
	let val = window.location.search.split(param + '=')[1]	
	return val;
}

window.addEventListener("load", function f() {
	let idBook = getParams("idBook"); 
	let parameters = createFormData(
        [
		{key:"function", value: "getBookById"},
		{key:"idBook", value: idBook}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, result);
});






function result(responseText) {
    let response = JSON.parse(responseText);
	if(response.errorCode == -400) {
		alert("Bad Request");
		return;
	}
	
	let res = response.body.icanbuy;
	
	let idBook = getParams("idBook");
	if(res == 0) { //gia comprato
		window.location.href = "/api/downloadBook.php?idBook=" + idBook;
	}else if(res == 1) {  //ok lo posso comprare
	
	}else if(res == 2) { //non valido
		window.location.href = "index.html";
	}
	
	let price_ = response.body.book.price;
	let title_ = response.body.book.title;
	let author_ = response.body.book.author;
	
	price.innerText = price_;
	title.innerText = title_;
	author.innerText = author_;
	
	button_pay.addEventListener("click", pay);
}


//da fare
function pay(ev) {
	ev.preventDefault();
	
	let idBook = getParams("idBook"); 
	let parameters =reon", value: "getBookById"},
		{key:"idBook", value: idBook}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, result);
}