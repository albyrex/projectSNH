window.addEventListener("load", function f() {
    doAjaxPost("api/userStatus.php", [], function(responseText) {
        let response = JSON.parse(responseText);
        if (response.body.status != 1) {
            alert("No admin");
            window.location.href = "index.php";
            return;
        }

        search.addEventListener("change", searchBook);

    });
});


function populateBook(responseText) {
    let response = JSON.parse(responseText);
    if (response.errorCode < 0) {
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
        var p = document.createElement("td");
        var b = document.createElement("button");
        var b_td = document.createElement("td");
        b_td.appendChild(b);

        t.innerText = reverseHtmlSpecialChars(book.title);
        a.innerText = reverseHtmlSpecialChars(book.author);
        p.innerText = book.price + "€";
        b.innerText = "Remove";
        b.setAttribute("idBook", book.id_book);
        b.addEventListener("click", removeBook);
        x.appendChild(t);
        x.appendChild(a);
        x.appendChild(p);
        x.appendChild(b_td);

        booklist.appendChild(x);
    }
}


function searchBook(ev) {
    let parameters = createFormData(
        [
            {
                key: "function",
                value: "searchByTitleOrAuthor"
            },
            {
                key: "searchString",
                value: ev.target.value
            },
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, populateBook);
}


function removeBook(ev) {
    let idBook = ev.target.getAttribute("idBook");
    let parameters = createFormData(
        [
            {
                key: "function",
                value: "removeBookById"
            },
            {
                key: "idBook",
                value: idBook
            }
		]
    );
    doAjaxPost("api/admin.php", parameters, function(responseText) {
        let response = JSON.parse(responseText);
        if (response.errorCode == -400) {
            alert("Error requesting data.\nError message: " + response.body);
            return;
        } else if (response.errorCode == 0) {
            alert("Book removed");
            window.location.reload();
            return;
        }
    });
}


function clearTable(tab) {
    while (tab.children.length > 1) {
        var lastchild = tab.children[tab.children.length - 1];
        tab.removeChild(lastchild);
    }
}


function reverseHtmlSpecialChars(text) {
    var map = {
        '&amp;': '&',
        '&#038;': "&",
        '&lt;': '<',
        '&gt;': '>',
        '&quot;': '"',
        '&#039;': "'",
        '&#8217;': "’",
        '&#8216;': "‘",
        '&#8211;': "–",
        '&#8212;': "—",
        '&#8230;': "…",
        '&#8221;': '”'
    };

    return text.replace(/\&[\w\d\#]{2,5}\;/g, function(m) {
        return map[m];
    });
}
