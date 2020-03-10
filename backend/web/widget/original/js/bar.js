
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
    data_chatbo_bar.cookie_val_show = getCookie("sn-"+data_chatbo_bar.widget_id);
    data_chatbo_bar.cookie_val_close = getCookie("cn-"+data_chatbo_bar.widget_id);
    user_ref = getRandomInt(1,999999999999);
    whenDisplay();
};


loadScript("//widget.chatbo.de/original/js/config.js", handler);


function afterClickOfButton(){
    removeCookie("cn-"+data_chatbo_bar.widget_id, "closed");
    //click for button and after submit
    var afterSubmit = data_chatbo_bar.after_submit;
    var urlOpenAfterSubmision = data_chatbo_bar.url_open_after_submission;
    var openThisUrl = data_chatbo_bar.open_this_url;
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
    //hide_bar
    var hideBar = document.querySelector('.hide-bar1');
    var allDiv = document.querySelector('.bar-st.after-submit');
    hideBar.onclick = function(){
        allDiv.parentNode.removeChild(allDiv);
    }
}

function newDivAfter(){
    var beforeBar = document.getElementById('bar-before');
    beforeBar.style.display = 'none';
    var pushbarafter = document.createElement("div");
    // HTML BAR after
    var htmlBarAfter;
    htmlBarAfter = '<div class="bar-st m-h w-b-s after-submit" style="background-color: '+data_chatbo_bar.after_submit_color_background+';">';
    htmlBarAfter += '<i class="hide-bar hide-bar1">&times;</i>';
    htmlBarAfter += '<div class="bar-t">';
    htmlBarAfter += '<h2 class="ed-t" style="color: '+data_chatbo_bar.after_submit_color_headline+';">'+data_chatbo_bar.bar_submit_title+'</h2>';
    htmlBarAfter += '</div>';
    htmlBarAfter += '<div class="s-m-i">';
    htmlBarAfter += '<a href="https://m.me/'+data_chatbo_bar.page_id+'" target="_blank" class="button-style-w" style="background-color: '+data_chatbo_bar.after_submit_color_button_background+'; color: '+data_chatbo_bar.after_submit_color_button_text+'">'+data_chatbo_bar.bar_submit_button_title+'</a>';
    htmlBarAfter += '</div>';
    htmlBarAfter += '</div>';
    pushbarafter.innerHTML = htmlBarAfter;
    document.body.appendChild(pushbarafter);
}


function initWidget(){
    var pushbarcreate = document.createElement("div");
    var htmlBarBeforeCheckbox;
    htmlBarBeforeCheckbox = '<div class="bar-st m-h w-b-s before-submit checkbox-button-type access-facebook" id="bar-before" style="background-color:'+data_chatbo_bar.color_background+';">';
    htmlBarBeforeCheckbox += '<i class="hide-bar">&times;</i>';
    htmlBarBeforeCheckbox += '<div class="bar-t">';
    htmlBarBeforeCheckbox += '<h2 class="ed-t" style="color: '+data_chatbo_bar.color_headline+'">'+data_chatbo_bar.bar_title+'</h2>';
    htmlBarBeforeCheckbox += '</div>';
    htmlBarBeforeCheckbox += '<div class="s-m-i">';
    if(getCookie("chatbo-widget-"+data_chatbo_bar.page_id+"-"+data_chatbo_bar.widget_id) == 'active'){
        htmlBarBeforeCheckbox += '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="background-color:'+data_chatbo_bar.color_button_background+'; color: '+data_chatbo_bar.color_button_text+'">'+data_chatbo_bar.bar_button_title+'</button>';
        htmlBarBeforeCheckbox += '<div class="fb-messenger-checkbox"' +
            '                             origin="'+config_chatbo_v2.domain+'"' +
            '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
            '                             page_id="'+data_chatbo_bar.page_id+'"' +
            '                             user_ref="'+user_ref+'"' +
            '                             data-ref="'+data_chatbo_bar.widget_id+'"' +
            '                             prechecked="true"' +
            '                             allow_login="true"' +
            '                             size="standard" style="margin-top: -10px;">' +
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
        htmlBarBeforeCheckbox += '<button class="check-chatbo-btn1" id="check-chatbo-btn" style="background:'+data_chatbo_bar.check_button_bg+';color:'+data_chatbo_bar.check_button_color+';">'+data_chatbo_bar.check_button_text+'</button>';
    }
    htmlBarBeforeCheckbox += '</div>';
    htmlBarBeforeCheckbox += '</div>';
    pushbarcreate.innerHTML = htmlBarBeforeCheckbox;
    document.body.appendChild(pushbarcreate);
    if(getCookie("chatbo-widget-"+data_chatbo_bar.page_id+"-"+data_chatbo_bar.widget_id) != 'active'){
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
                setCookie("chatbo-widget-"+data_chatbo_bar.page_id+"-"+data_chatbo_bar.widget_id, "active", {'expires':3600*24*30});
                modal.style.display = "none";
                var showPluginHtml = '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="background-color:'+data_chatbo_bar.color_button_background+'; color: '+data_chatbo_bar.color_button_text+'">'+data_chatbo_bar.bar_button_title+'</button>';
                showPluginHtml += '<div class="fb-messenger-checkbox"' +
                    '                             origin="'+config_chatbo_v2.domain+'"' +
                    '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                    '                             page_id="'+data_chatbo_bar.page_id+'"' +
                    '                             user_ref="'+user_ref+'"' +
                    '                             data-ref="'+data_chatbo_bar.widget_id+'"' +
                    '                             prechecked="true"' +
                    '                             allow_login="true"' +
                    '                             size="standard" style="margin-top: -10px;">' +
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

    if(data_chatbo_bar.show_widget_after == 'never'){
        setCookie("sn-"+data_chatbo_bar.widget_id, "show", (365*24*60*60*1000));
    }
    else if(data_chatbo_bar.show_widget_after == 'days'){
        setCookie("sn-"+data_chatbo_bar.widget_id, "show", (data_chatbo_bar.show_widget_after_value*24*60*60*1000));
    }
    else if(data_chatbo_bar.show_widget_after == 'hours'){
        setCookie("sn-"+data_chatbo_bar.widget_id, "show", (data_chatbo_bar.show_widget_after_value*60*60*1000));
    }

    //hide_bar
    var hideBar = document.querySelector('.hide-bar');
    var allDiv = document.querySelector('.bar-st');
    if (data_chatbo_bar.hide_bar == false) {
        hideBar.style.display = 'none';
    }
    hideBar.onclick = function(){
        allDiv.parentNode.removeChild(allDiv);
        if(data_chatbo_bar.closed_user_show_after == 'never' || data_chatbo_bar.closed_user_show_after == 'always'){
            setCookie("cn-"+data_chatbo_bar.widget_id, "closed", (365*24*60*60*1000));
        }
        else if(data_chatbo_bar.closed_user_show_after == 'days'){
            setCookie("cn-"+data_chatbo_bar.widget_id, "closed", (data_chatbo_bar.closed_user_show_after_value*24*60*60*1000));
        }
        else if(data_chatbo_bar.closed_user_show_after == 'hours'){
            setCookie("cn-"+data_chatbo_bar.widget_id, "closed", (data_chatbo_bar.closed_user_show_after_value*60*60*1000));
        }
    }
}

//When does it display
function whenDisplay(){
    if(data_chatbo_bar.status == '0'){
        console.log('Widget deactivated');
        return false;
    }

    var itDisplay = data_chatbo_bar.it_display;
    var displaySeconds = data_chatbo_bar.display_seconds * 1000;
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

    if(data_chatbo_bar.show_widget_after != 'always'){
        if(data_chatbo_bar.cookie_val_show == 'show'){
            show = false;
        }
    }

    if(data_chatbo_bar.cookie_val_close == 'closed'){
        if(data_chatbo_bar.closed_user_show_after == 'always'){
            show = true;
        }
        else{
            if(data_chatbo_bar.cookie_val_close == 'closed'){
                show = false;
            }
        }
    }

    if(show){
        if(data_chatbo_bar.devices == '1'){
            if(isMobile.any() != null){
                return false;
            }
        }
        else if(data_chatbo_bar.devices == '2'){
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
        'page_id': data_chatbo_bar.page_id,
        'ref': data_chatbo_bar.widget_id,
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
    fetch('https://api.chatbo.de/v2/fetch/insights/widget/'+data_chatbo_bar.widget_id, {mode: 'no-cors'}).then(function(response){
        return response;
    });
}
