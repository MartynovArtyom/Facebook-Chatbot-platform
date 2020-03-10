
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
    user_ref = getRandomInt(1,999999999999);
    whenDisplay();
};

loadScript("//widget.chatbo.de/original/js/config.js", handler);

function afterClickOfButton(){

    var btnElementBefore = document.querySelector('.chatbo-button-widget-embed[data-widget-id*="'+data_chatbo_button.widget_id+'"]');
    btnElementBefore.innerHTML='';

    //click for button and after submit
    var afterSubmit = data_chatbo_button.after_submit;
    var urlOpenAfterSubmision = data_chatbo_button.url_open_after_submission;
    var openThisUrl = data_chatbo_button.open_this_url;

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

function newDivAfter(){
    var btnElementBefore = document.querySelector('.chatbo-button-widget-embed[data-widget-id*="'+data_chatbo_button.widget_id+'"]');

    var btnHtml = '<div class="s-m-i">';
    btnHtml += '<div style="text-align: left; margin-bottom: 10px; color:'+data_chatbo_button.after_submit_color_button_background+';">Erfolgreich!</div>'
    btnHtml += '<a href="https://m.me/'+data_chatbo_button.page_id+'" target="_blank" class="button-style-w" style="background-color: '+data_chatbo_button.after_submit_color_button_background+'; color: '+data_chatbo_button.after_submit_color_button_text+'">'+data_chatbo_button.submit_button_title+'</a>';
    btnHtml += '</div>';
    btnElementBefore.innerHTML = btnHtml;

}

function initWidget(){

    var btnElementBefore = document.querySelector('.chatbo-button-widget-embed[data-widget-id*="'+data_chatbo_button.widget_id+'"]');
    if(btnElementBefore){
        var btnHtml = '';
        if(getCookie("chatbo-widget-"+data_chatbo_button.page_id+"-"+data_chatbo_button.widget_id) == 'active'){
            if(data_chatbo_button.checkbox_position == 'side')
                btnHtml += '<div class="s-m-i button-side-checkbox">';
            else
                btnHtml += '<div class="s-m-i button-bottom-checkbox">';

            btnHtml += '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_button.color_button_background+'; color: '+data_chatbo_button.color_button_text+'">'+data_chatbo_button.button_title+'</button>';
            btnHtml += '<div class="fb-messenger-checkbox"' +
                '                             origin="'+config_chatbo_v2.domain+'"' +
                '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                '                             page_id="'+data_chatbo_button.page_id+'"' +
                '                             user_ref="'+user_ref+'"' +
                '                             data-ref="'+data_chatbo_button.widget_id+'"' +
                '                             prechecked="true"' +
                '                             allow_login="true"' +
                '                             size="xlarge">' +
                '                        </div>';
            btnHtml += '</div>';
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
            btnHtml += '<button class="check-chatbo-btn1 button-style-w" id="check-chatbo-btn" style="background:'+data_chatbo_button.check_button_bg+';color:'+data_chatbo_button.check_button_color+';">'+data_chatbo_button.check_button_text+'</button>';
        }

        btnElementBefore.innerHTML = btnHtml;

        if(getCookie("chatbo-widget-"+data_chatbo_button.page_id+"-"+data_chatbo_button.widget_id) != 'active'){
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
                    setCookie("chatbo-widget-"+data_chatbo_button.page_id+"-"+data_chatbo_button.widget_id, "active", {'expires':3600*24*30});
                    modal.style.display = "none";
                    var showPluginHtml = '';
                    if(data_chatbo_button.checkbox_position == 'side')
                        showPluginHtml += '<div class="s-m-i button-side-checkbox">';
                    else
                        showPluginHtml += '<div class="s-m-i button-bottom-checkbox">';

                    showPluginHtml += '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_button.color_button_background+'; color: '+data_chatbo_button.color_button_text+'">'+data_chatbo_button.button_title+'</button>';
                    showPluginHtml += '<div class="fb-messenger-checkbox"' +
                        '                             origin="'+config_chatbo_v2.domain+'"' +
                        '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                        '                             page_id="'+data_chatbo_button.page_id+'"' +
                        '                             user_ref="'+user_ref+'"' +
                        '                             data-ref="'+data_chatbo_button.widget_id+'"' +
                        '                             prechecked="true"' +
                        '                             allow_login="true"' +
                        '                             size="xlarge">' +
                        '                        </div>';
                    showPluginHtml += '</div>';
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
    }

}

function whenDisplay(){
    if(data_chatbo_button.status == '0'){
        console.log('Widget deactivated');
        return false;
    }

    setTimeout(function () {
        initWidget();
    }, 50);
}

function confirmOptIn() {
    FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
        'app_id': config_chatbo_v2.appId,
        'page_id': data_chatbo_button.page_id,
        'ref': data_chatbo_button.widget_id,
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
    fetch('https://api.chatbo.de/v2/fetch/insights/widget/'+data_chatbo_button.widget_id, {mode: 'no-cors'}).then(function(response){
        return response;
    });
}
