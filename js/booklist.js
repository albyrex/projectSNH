import { doAjaxGet, doAjaxPost, createFormData } from './ajax.js';

let ui = {
    booklist: null
}



window.addEventListener("load", function f() {	

    ui.booklist = document.getElementById("booklist");
    
    let parameters = createFormData(
        [
		{key:"function", value: "topDownloaded"}
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, loadInfo);
});


function loadInfo(responseText) {
    let response = JSON.parse(responseText);
    if(response.errorCode < 0) {
        alert("Error requesting data.\nError message: " + response.body);
        return;
    }    
		
	
	let listbook = response.body.bookList;
	var book_example = document.getElementById("book_example");
	book_example.id = "";
		
	for (var i = 0; i < listbook.length; ++i) {
		var clone = book_example.cloneNode(true);
		clone.id = "";
		clone.children[0].firstChild.innerText = listuser[i].name + " " + listuser[i].surname + " " + listuser[i].role_name;
		clone.getElementsByTagName("a")[0].href = "/update_user.html?name="+listuser[i].name+"&surname="+listuser[i].surname;
		ui.userlist.appendChild(clone);
	}
	book_example.parentNode.removeChild(book_example);
}


function onButtonAddCartClicked(id_) {
	
}


function onOperationCompleted(responseText) {
    alert("Operation returned: " + responseText);
}
