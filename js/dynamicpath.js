function dynamicDataURL () {
    //dynamically detect the URL assignment
    var dataURL;
    if (window.location.hostname == "localhost") {
        dataURL = "http://localhost/stana/";
    } 
    else if (window.location.hostname == "www.codesword.com") {
        dataURL = "http://www.codesword.com/";
    }
    else {
        dataURL = "";
    };

    return dataURL;
}
