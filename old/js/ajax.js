export {doAjaxGet, doAjaxPost, createFormData};


/*
  doAjaxGet() e doAjaxPost() sono le funzioni che gli script devono chiamare e si basano
  su doAjax() per funzionare.
  doAjaxGet() prende come "parameters" la query dell'url get ad esempio:
     userId=1&username=Cappe
  doAjaxPost() invece prende come "formData" un'istanza di FormData.
*/
function doAjaxGet(page, parameters, successFunction) {
    doAjax("GET",page,parameters,successFunction);
}
function doAjaxPost(page, formData, successFunction) {
    doAjax("POST",page,formData,successFunction,"");
}

/*
  Funzione di utilità che crea un FormData in maniera semplificata.
  Serve solo a snellire il codice.
  Prende come unico parametro un array di oggetti {key, value}.
*/
function createFormData(arr) {
    var formData = new FormData();
    for(var i = 0; i < arr.length; i++){
        formData.append(arr[i].key,arr[i].value);
    }
    return formData;
}



/*
  getAjax() inizializza e restituisce il nuovo XMLHttpRequest, se possibile,
  altrimenti restituisce un oggetto equivalente adatto ai browser IE più vecchi.
  Se anche questa operazione fallisce restituisce null
*/
function getAjax() {
    try {
        return new XMLHttpRequest();
    }
    catch (e) {
        try {
            return new ActiveXObject("Msxml2.XMLHTTP");
        }
        catch (e) {
            try {
                return new ActiveXObject("Microsoft.XMLHTTP");
            }
            catch (e) {
                return null;
            }
        }
    }
}

/*
  Funzione che esegue la richiesta ajax.
  Questa funzione non deve essere richiamata da altre funzioni se non quelle
  di questo script.
*/
function doAjax(getOrPost, page, parameters, successFunction, contentType) {
    var req = getAjax();

    if(req == null){
        alert(
            "Errore: è stato impossibile inizializzare una richiesta ajax.\n" +
            "Molto probabilmente il browser web in uso non supporta tale procedura, " +
            "di conseguenza la pagina web non potrà essere mostrata nella sua interezza " +
            "o potrebbe presentare problemi di visualizzazione."
        );
        return;
    }

    if(successFunction != undefined){
        req.onreadystatechange = function() {
            if (this.readyState == 4) {
                successFunction(this.responseText);
            }
        };
    }

    getOrPost = getOrPost.toUpperCase();

    var toSend = null;

    if(getOrPost == "GET"){
        if(parameters != "")
            page = page + "?" + parameters;
    }else{
        toSend = parameters;
    }

    req.open(getOrPost, page, true);

    if(contentType == undefined && getOrPost == "POST")
        req.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
    else if(contentType != "" && getOrPost == "POST")
        req.setRequestHeader('Content-type', contentType);

    req.send(toSend);
}
