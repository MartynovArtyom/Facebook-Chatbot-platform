var config_chatbo_v2 = {
    domain: window.location.origin,
    appId: 295841470908247,
    version: "v3.3",
    logo: '//widget.chatbo.de/original/img/logo_chat.png',
};

// Css file.
var cssId = 'styleCSS';
if (!document.getElementById(cssId)) {
    var head = document.getElementsByTagName('head')[0];
    var link = document.createElement('link');
    link.id = cssId;
    link.rel = 'stylesheet';
    link.type = 'text/css';
    link.href = '//widget.chatbo.de/original/css/widget.css';
    head.appendChild(link);
}

function getRandomInt(min, max) {
    min = Math.ceil(min);
    max = Math.floor(max);
    return (Math.floor(Math.random() * (max - min)) + min); //The maximum is exclusive and the minimum is inclusive
}


function getCookie(name) {
    var matches = document.cookie.match(new RegExp(
        "(?:^|; )" + name.replace(/([\.$?*|{}\(\)\[\]\\\/\+^])/g, '\\$1') + "=([^;]*)"
    ));
    return matches ? decodeURIComponent(matches[1]) : '';
}
function extractDomain(url) {
    var domain;
    if (url.indexOf("://") > -1) {
        domain = url.split('/')[2];
    }
    else {
        domain = url.split('/')[0];
    }
    //find & remove port number
    domain = domain.split(':')[0];

    return domain;
}
// Set cookie.
function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays));
    var expires = "expires="+d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/; domain="+extractDomain(config_chatbo_v2.domain);
}
// Remove cookie.
function removeCookie(cname, cvalue) {
    var expires = "expires=Thu, 01 Jan 1970 00:00:01 GMT";
    document.cookie = cname + "=" + cvalue + "; " + expires+"; path=/; domain="+extractDomain(config_chatbo_v2.domain);
}
