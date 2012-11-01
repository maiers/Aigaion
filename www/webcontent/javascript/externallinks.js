function externalLinks() {
    var links = document.getElementsByTagName('a');
    for (var i=0;i < links.length;i++) {
        if (links[i].className == 'open_extern') {
            links[i].onclick = function() {
                window.open(this.href);
                return false;
            };
        }
    }
}

window.onload = externalLinks;

