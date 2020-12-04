function getParams(param) {
    let val = window.location.search.split(param + '=')[1]
    return val;
}

window.addEventListener("load", function f() {
    let idBook = getParams("idBook");
    let parameters = createFormData(
        [
            {
                key: "function",
                value: "getBookById"
            },
            {
                key: "idBook",
                value: idBook
            }
		]
    );
    doAjaxPost("api/bookInfo.php", parameters, result);
});



function result(responseText) {
    let response = JSON.parse(responseText);
    if (response.errorCode == -400) {
        alert("Bad Request");
        return;
    }

    let res = response.body.icanbuy;

    let idBook = getParams("idBook");
    if (res == 0) { //gia comprato
        window.location.href = "api/downloadBook.php?idBook=" + idBook;
    } else if (res == 1) { //ok lo posso comprare
        let price_ = response.body.book.price;
        let title_ = response.body.book.title;
        let author_ = response.body.book.author;

        price.innerText = price_ + "€";
        title.innerText = title_;
        author.innerText = author_;

        button_pay.addEventListener("click", pay);
    } else { //non valido
        window.location.href = "index.html";
    }
}



function pay(ev) {
    ev.preventDefault();
    let idBook = getParams("idBook");
    let parameters = createFormData(
        [
            {
                key: "idBook",
                value: idBook
            },
            {
                key: "cardname",
                value: cardname.value
            },
            {
                key: "cardnumber",
                value: cardnumber.value
            },
            {
                key: "cvv",
                value: cvv.value
            },
            {
                key: "monthyear",
                value: monthyear.value
            }
		]
    );
    doAjaxPost("api/buy.php", parameters, function(responseText) {
        let response = JSON.parse(responseText);
        if (response.errorCode == -400) {
            alert("Bad Request");
            return;
        } else if (response.errorCode == -2) {
            alert("User not logged");
            return;
        } else if (response.errorCode == -3) {
            alert("Error during payment");
            return;
        } else if (response.errorCode == 1) {
            alert("Book already bought");
            window.location.href = "profile.html";
            return;
        } else if (response.errorCode == 0) {
            alert("Book bought correctly");
            window.location.href = "api/downloadBook.php?idBook=" + idBook;
            return;
        }
    });
}
