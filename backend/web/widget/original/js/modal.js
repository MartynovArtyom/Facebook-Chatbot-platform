
var user_ref;

function loadScript(url, callback)
{
    // Adding the script tag to the head as suggested before
    var head = document.head;
    var script = document.createElement('script');
    script.type = 'text/javascript';
    script.src = url;

    // Then bind the event to the callback function.
    // There are several events for cross browser compatibility.
    script.onreadystatechange = callback;
    script.onload = callback;

    // Fire the loading
    head.appendChild(script);
}

var handler = function(){
    data_chatbo_modal.cookie_val_show = getCookie("sn-"+data_chatbo_modal.widget_id);
    data_chatbo_modal.cookie_val_close = getCookie("cn-"+data_chatbo_modal.widget_id);
    user_ref = getRandomInt(1,999999999999);
    whenDisplay();
};

loadScript("//widget.chatbo.de/original/js/config.js", handler);

function afterClickOfButton(){

    removeCookie("cn-"+data_chatbo_modal.widget_id, "closed");
    //click for button and after submit
    var beforeSubmit = document.getElementById('pr-bl-before-submit');
    beforeSubmit.parentNode.removeChild(beforeSubmit);
    var afterSubmit = data_chatbo_modal.after_submit;
    var urlOpenAfterSubmision = data_chatbo_modal.url_open_after_submission;
    var openThisUrl = data_chatbo_modal.open_this_url;

    if (afterSubmit == 'redirect') {
        if (openThisUrl != '0') {
            window.location.href = urlOpenAfterSubmision;
        }
        else{
            window.open(urlOpenAfterSubmision);
        }
    }
    else{
        newDivAfter();
    }
}

var generateImg = function(url_image){
    var html = '';
    html += '<div class="img-p place-i">';
    html += '<img src="'+url_image+'">';
    html += '</div>';

    return html;
};

function newDivAfter(){
    var pushSlideInAfter = document.createElement("div");
    pushSlideInAfter.className += " modal-widget";
    // HTML SLIDE-IN after
    var htmlSlideInAfter = '';
    htmlSlideInAfter += '<div class="pr-bl m-m opct-tr before-submit btn-button-type" style="background: '+data_chatbo_modal.after_submit_color_background+'">';
    htmlSlideInAfter += '<div class="cont-st-p">';
    htmlSlideInAfter += '<i class="hide-bar">&times;</i>';
    if (data_chatbo_modal.after_submit_image_place == '0' && data_chatbo_modal.after_submit_image !== '') {
        htmlSlideInAfter += generateImg(data_chatbo_modal.after_submit_image);
    }
    htmlSlideInAfter += '<h2 class="ed-t" style="color: '+data_chatbo_modal.after_submit_color_headline+';">'+data_chatbo_modal.slidein_submit_title+'</h2>';
    if (data_chatbo_modal.after_submit_image_place == '1' && data_chatbo_modal.after_submit_image !== '') {
        htmlSlideInAfter += generateImg(data_chatbo_modal.after_submit_image);
    }
    htmlSlideInAfter += '<p class="ed-t after-show-desc" style="color: '+data_chatbo_modal.after_submit_color_desc+';">'+data_chatbo_modal.slidein_submit_desc+'</p>';
    if (data_chatbo_modal.after_submit_image_place == '2' && data_chatbo_modal.after_submit_image !== '') {
        htmlSlideInAfter += generateImg(data_chatbo_modal.after_submit_image);
    }
    htmlSlideInAfter += '<div class="s-m-i">';
    htmlSlideInAfter += '<a href="https://m.me/'+data_chatbo_modal.page_id+'" target="_blank" class="button-style-w" style="background-color: '+data_chatbo_modal.after_submit_color_button_background+'; color: '+data_chatbo_modal.after_submit_color_button_text+'">'+data_chatbo_modal.slidein_submit_button_title+'</a>';
    htmlSlideInAfter += '</div>';
    htmlSlideInAfter += '</div>';
    htmlSlideInAfter += '</div>';
    pushSlideInAfter.innerHTML = htmlSlideInAfter;
    document.body.appendChild(pushSlideInAfter);

    // After show description
    var showDescAfter = document.querySelector('.after-show-desc');
    if (data_chatbo_modal.show_desc_after == 'true' || data_chatbo_modal.show_desc_after == '1') {
        showDescAfter.style.display = 'block';
    }
    else{
        showDescAfter.parentNode.removeChild(showDescAfter);
    }

    //hide_bar
    var hideBar = document.querySelector('.hide-bar');
    hideBar.onclick = function(){
        pushSlideInAfter.parentNode.removeChild(pushSlideInAfter);
    }
}


function initWidget(){

    var pushSlideInCreate = document.createElement("div");
    pushSlideInCreate.className += ' modal-widget';
    pushSlideInCreate.id = 'pr-bl-before-submit';

    // HTML SLIDE-IN checkbox
    var htmlSlideInBeforeCheckbox = '';
    htmlSlideInBeforeCheckbox += '<div class="pr-bl m-m opct-tr before-submit checkbox-button-type" id="" style="background-color: '+data_chatbo_modal.color_background+'">';
    htmlSlideInBeforeCheckbox += '<div class="cont-st-p">';
    htmlSlideInBeforeCheckbox += '<i class="hide-bar">&times;</i>';
    if (data_chatbo_modal.image_place == '0' && data_chatbo_modal.image !== '') {
        htmlSlideInBeforeCheckbox += generateImg(data_chatbo_modal.image);
    }
    htmlSlideInBeforeCheckbox += '<h2 class="ed-t place-i" style="color: '+data_chatbo_modal.color_headline+';">'+data_chatbo_modal.slidein_title+'</h2>';
    if (data_chatbo_modal.image_place == '1' && data_chatbo_modal.image !== '') {
        htmlSlideInBeforeCheckbox += generateImg(data_chatbo_modal.image);
    }
    htmlSlideInBeforeCheckbox += '<p class="ed-t show-desc place-i" style="color: '+data_chatbo_modal.color_desc+';">'+data_chatbo_modal.slidein_desc+'</p>';
    if (data_chatbo_modal.image_place == '2' && data_chatbo_modal.image !== '') {
        htmlSlideInBeforeCheckbox += generateImg(data_chatbo_modal.image);
    }
    htmlSlideInBeforeCheckbox += '<div class="s-m-i">';
    if(getCookie("chatbo-widget-"+data_chatbo_modal.page_id+"-"+data_chatbo_modal.widget_id) == 'active'){
        htmlSlideInBeforeCheckbox += '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_modal.color_button_background+'; color: '+data_chatbo_modal.color_button_text+'">'+data_chatbo_modal.slidein_button_title+'</button>';
        htmlSlideInBeforeCheckbox += '<div class="fb-messenger-checkbox"' +
            '                             origin="'+config_chatbo_v2.domain+'"' +
            '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
            '                             page_id="'+data_chatbo_modal.page_id+'"' +
            '                             user_ref="'+user_ref+'"' +
            '                             data-ref="'+data_chatbo_modal.widget_id+'"' +
            '                             prechecked="true"' +
            '                             allow_login="true"' +
            '                             size="xlarge">' +
            '                        </div>';
    }
    else{
        var modal = document.createElement("div");
        modal.setAttribute('class','chatbo-modal-check');
        modal.setAttribute('id','chatbo-modal-check');
        var modalHTML = '<div class="modal-content">';
        modalHTML += '<span class="close" id="close-chatbo-modal">&times;</span>';
        modalHTML += '<div class="modal-c">'
        modalHTML += '  <div class="modal-c-img">';
        modalHTML += '      <img src="'+config_chatbo_v2.logo+'">';
        modalHTML += '  </div>';
        modalHTML += '  <div class="modal-c-text">';
        modalHTML += '      <p>2 Klicks für mehr Datenschutz: Erst wenn Sie hier klicken, wird der Button aktiv und Sie können den Facebook Chat starten. Schon beim Aktivieren werden Daten an Dritte übertragen. Mit Ihrem Klick stimmen Sie unserer Datenschutzerklärung zu, die Sie am Ende der Seite einsehen können.</p>';
        modalHTML += '  </div>';
        modalHTML += '</div>';
        modalHTML += '<p><input type="checkbox" id="check-policy"> <label for="check-policy">Ich akzepziere die Datenschutzbestimmungen</label></p>';
        modalHTML += '<p id="error-policy" style="color:#ff6249;display: none;">Die Datenschutzbestimmung ist bitte vorher zu akzeptieren, danke.</p>';
        modalHTML += '<div class="modal-btn-div">';
        modalHTML += '<button class="show" id="chatbo-aktiv-btn">Weiter</button>';
        modalHTML += '</div>';
        modalHTML += '</div>';
        modal.innerHTML = modalHTML;
        document.body.appendChild(modal);
        htmlSlideInBeforeCheckbox += '<button class="check-chatbo-btn1" id="check-chatbo-btn" style="background:'+data_chatbo_modal.check_button_bg+';color:'+data_chatbo_modal.check_button_color+';">'+data_chatbo_modal.check_button_text+'</button>';
    }

    htmlSlideInBeforeCheckbox += '</div>';

    htmlSlideInBeforeCheckbox += '</div>';
    htmlSlideInBeforeCheckbox += '</div>';

    pushSlideInCreate.innerHTML = htmlSlideInBeforeCheckbox;
    document.body.appendChild(pushSlideInCreate);

    // Show description
    var showDesc = document.querySelector('.cont-st-p .show-desc');
    if (data_chatbo_modal.show_desc == 'true' || data_chatbo_modal.show_desc == '1') {
        showDesc.style.display = 'block';
    }
    else{
        showDesc.parentNode.removeChild(showDesc);
    }

    if(getCookie("chatbo-widget-"+data_chatbo_modal.page_id+"-"+data_chatbo_modal.widget_id) != 'active'){
        var checkBtn = document.getElementById("check-chatbo-btn");

        var span = document.getElementById("close-chatbo-modal");
        var active = document.getElementById('chatbo-aktiv-btn');

        checkBtn.onclick = function() {
            modal.style.display = "block";
        };

        // When the user clicks on <span> (x), close the modal
        span.onclick = function() {
            modal.style.display = "none";
        };

        active.onclick = function () {
            var errorPolicy = document.getElementById("error-policy");
            if(document.getElementById("check-policy").checked == true){
                errorPolicy.style.display = "none";
                setCookie("chatbo-widget-"+data_chatbo_modal.page_id+"-"+data_chatbo_modal.widget_id, "active", {'expires':3600*24*30});
                modal.style.display = "none";
                var showPluginHtml = '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_modal.color_button_background+'; color: '+data_chatbo_modal.color_button_text+'">'+data_chatbo_modal.slidein_button_title+'</button>';
                showPluginHtml += '<div class="fb-messenger-checkbox"' +
                    '                             origin="'+config_chatbo_v2.domain+'"' +
                    '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                    '                             page_id="'+data_chatbo_modal.page_id+'"' +
                    '                             user_ref="'+user_ref+'"' +
                    '                             data-ref="'+data_chatbo_modal.widget_id+'"' +
                    '                             prechecked="true"' +
                    '                             allow_login="true"' +
                    '                             size="xlarge">' +
                    '                        </div>';
                checkBtn.parentElement.innerHTML = showPluginHtml;
                runFB();
            }
            else{
                errorPolicy.style.display = "block";
            }
        };

        window.onclick = function(event) {
            if (event.target == modal) {
                modal.style.display = "none";
            }
        }
    }
    else{
        runFB();
    }

    if(data_chatbo_modal.show_widget_after == 'never'){
        setCookie("sn-"+data_chatbo_modal.widget_id, "show", (365*24*60*60*1000));
    }
    else if(data_chatbo_modal.show_widget_after == 'days'){
        setCookie("sn-"+data_chatbo_modal.widget_id, "show", (data_chatbo_modal.show_widget_after_value*24*60*60*1000));
    }
    else if(data_chatbo_modal.show_widget_after == 'hours'){
        setCookie("sn-"+data_chatbo_modal.widget_id, "show", (data_chatbo_modal.show_widget_after_value*60*60*1000));
    }

    //hide_bar
    var hideBar = document.querySelector('.hide-bar');
    hideBar.onclick = function(){
        pushSlideInCreate.parentNode.removeChild(pushSlideInCreate);
        if(data_chatbo_modal.closed_user_show_after == 'never' || data_chatbo_modal.closed_user_show_after == 'always'){
            setCookie("cn-"+data_chatbo_modal.widget_id, "closed", (365*24*60*60*1000));
        }
        else if(data_chatbo_modal.closed_user_show_after == 'days'){
            setCookie("cn-"+data_chatbo_modal.widget_id, "closed", (data_chatbo_modal.closed_user_show_after_value*24*60*60*1000));
        }
        else if(data_chatbo_modal.closed_user_show_after == 'hours'){
            setCookie("cn-"+data_chatbo_modal.widget_id, "closed", (data_chatbo_modal.closed_user_show_after_value*60*60*1000));
        }
    }
}

//When does it display
function whenDisplay(){
    if(data_chatbo_modal.status == '0'){
        console.log('Widget deactivated');
        return false;
    }

    var itDisplay = data_chatbo_modal.it_display;
    var displaySeconds = data_chatbo_modal.display_seconds * 1000;

    //device
    var isMobile = {
        Android: function() {
            return navigator.userAgent.match(/Android/i);
        },
        BlackBerry: function() {
            return navigator.userAgent.match(/BlackBerry/i);
        },
        iOS: function() {
            return navigator.userAgent.match(/iPhone|iPad|iPod/i);
        },
        Opera: function() {
            return navigator.userAgent.match(/Opera Mini/i);
        },
        Windows: function() {
            return navigator.userAgent.match(/IEMobile/i);
        },
        any: function() {
            return (isMobile.Android() || isMobile.BlackBerry() || isMobile.iOS() || isMobile.Opera() || isMobile.Windows());
        }
    };

    var show = true;

    if(data_chatbo_modal.show_widget_after != 'always'){
        if(data_chatbo_modal.cookie_val_show == 'show'){
            show = false;
        }
    }

    if(data_chatbo_modal.cookie_val_close == 'closed'){
        if(data_chatbo_modal.closed_user_show_after == 'always'){
            show = true;
        }
        else{
            if(data_chatbo_modal.cookie_val_close == 'closed'){
                show = false;
            }
        }
    }

    if(show){
        if(data_chatbo_modal.devices == '1'){
            if(isMobile.any() != null){
                return false;
            }
        }
        else if(data_chatbo_modal.devices == '2'){
            if(isMobile.any() == null){
                return false;
            }
        }

        if (itDisplay == '0') {
            setTimeout(function () {
                initWidget();
            }, 500);
        }
        else{
            setTimeout(function () {
                initWidget();
            }, displaySeconds);
        }
    }
}


function confirmOptIn() {
    FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
        'app_id': config_chatbo_v2.appId,
        'page_id': data_chatbo_modal.page_id,
        'ref': data_chatbo_modal.widget_id,
        'user_ref': user_ref
    });
    afterClickOfButton();
}


function runFB(){
    if(typeof FB !== 'undefined'){
        FB.XFBML.parse();
        //CheckBox
        FB.Event.subscribe('messenger_checkbox', function(e) {
            if (e.event == 'checkbox') {
                var checkboxState = e.state;
                var btn = document.getElementById('checkbox-btn');
                if(checkboxState == 'unchecked'){
                    btn.disabled = true;
                }
                else{
                    btn.disabled = false;
                }
            }
        });
    }
    else{
        (function(d, s, id){
            var js, fjs = d.getElementsByTagName(s)[0];
            if (d.getElementById(id)) { return; }
            js = d.createElement(s); js.id = id;
            js.src = "//connect.facebook.net/de_DE/sdk.js";
            fjs.parentNode.insertBefore(js, fjs);
        }(document, 'script', 'facebook-jssdk'));

        window.fbAsyncInit = function() {
            FB.init({
                appId: config_chatbo_v2.appId,
                xfbml: true,
                version: config_chatbo_v2.version
            });

            //CheckBox
            FB.Event.subscribe('messenger_checkbox', function(e) {
                if (e.event == 'checkbox') {
                    var checkboxState = e.state;
                    var btn = document.getElementById('checkbox-btn');
                    if(checkboxState == 'unchecked'){
                        btn.disabled = true;
                    }
                    else{
                        btn.disabled = false;
                    }
                }
            });


        };
    }
    fetch('https://api.chatbo.de/v2/fetch/insights/widget/'+data_chatbo_modal.widget_id, {mode: 'no-cors'}).then(function(response){
        return response;
    });
}
