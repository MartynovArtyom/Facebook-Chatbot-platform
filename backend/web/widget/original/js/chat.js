
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

    window.fbAsyncInit = function() {
        FB.init({
            appId: config_chatbo_v2.appId,
            autoLogAppEvents : true,
            xfbml            : true,
            version          : config_chatbo_v2.version
        });
    };
    initChat();
};

loadScript("//widget.chatbo.de/original/js/config.js", handler);

function initChat() {
    if(data_chatbo_chat.status == '0'){
        console.log('Widget deactivated');
    }
    else{
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
        }
        if(data_chatbo_chat.devices == '1'){
            if(isMobile.any() != null){
                return false;
            }
        }
        else if(data_chatbo_chat.devices == '2'){
            if(isMobile.any() == null){
                return false;
            }
        }

        if(getCookie("chatbo-widget-"+data_chatbo_chat.page_id+"-"+data_chatbo_chat.widget_id) == 'active'){
            runChat();
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

            var checkBtn = document.createElement("div");
            checkBtn.setAttribute('class', 'check-chatbo-btn');
            checkBtn.setAttribute('id', 'check-chatbo-btn');
            var checkBtnHTML = '<div class="chat-preview">';
            checkBtnHTML += '   <div class="chat-preview-close" id="chat-preview-close" style="color: '+data_chatbo_chat.check_welcome_text_color+';">&times;</div>';
            checkBtnHTML += '   <div class="chat-preview-box" id="chat-preview-box" style="background: '+data_chatbo_chat.check_box_bg+';">';
            checkBtnHTML += '       <div class="chat-preview-box-text" style="color: '+data_chatbo_chat.check_welcome_text_color+';">'+data_chatbo_chat.check_welcome_text+'</div>';
            checkBtnHTML += '       <div class="chat-preview-box-img"><img src="'+config_chatbo_v2.logo+'"></div>';
            checkBtnHTML += '   </div>';
            checkBtnHTML += '   <div class="chat-preview-button" id="chat-preview-button" style="background: '+data_chatbo_chat.check_button_bg+';">';
            checkBtnHTML += '       <div class="chat-preview-button-btn" style="color: '+data_chatbo_chat.check_button_color+';">'+data_chatbo_chat.check_button_text+'</div>';
            checkBtnHTML += '    </div></div></div>';
            checkBtn.innerHTML = checkBtnHTML;
            document.body.appendChild(checkBtn);

            var closeBadge = document.createElement("div");
            closeBadge.setAttribute('class','chatbo-close-badge');
            closeBadge.setAttribute('id','chatbo-close-badge');
            closeBadge.innerHTML = '<div tabindex="0" role="button" style="cursor: pointer; outline: none;"><svg width="60px" height="60px" viewBox="0 0 60 60"><svg x="0" y="0" width="60px" height="60px"><defs><linearGradient x1="50%" y1="0%" x2="50%" y2="100%" id="linearGradient-1"><stop stop-color="#00B2FF" offset="0%"></stop><stop stop-color="#006AFF" offset="100%"></stop></linearGradient></defs><g stroke="none" stroke-width="1" fill="none" fill-rule="evenodd"><g><circle fill="#FFFFFF" cx="30" cy="30" r="30"></circle><svg x="10" y="10"><g><rect id="container" x="0" y="0" width="40" height="40"></rect><g id="logo"><path d="M20,0 C8.7334,0 0,8.2528 0,19.4 C0,25.2307 2.3896,30.2691 6.2811,33.7492 C6.6078,34.0414 6.805,34.4513 6.8184,34.8894 L6.9273,38.4474 C6.9621,39.5819 8.1343,40.3205 9.1727,39.8621 L13.1424,38.1098 C13.4789,37.9612 13.856,37.9335 14.2106,38.0311 C16.0348,38.5327 17.9763,38.8 20,38.8 C31.2666,38.8 40,30.5472 40,19.4 C40,8.2528 31.2666,0 20,0" id="bubble" fill="url(#linearGradient-1)"></path><path d="M7.99009,25.07344 L13.86509,15.75264 C14.79959,14.26984 16.80079,13.90064 18.20299,14.95224 L22.87569,18.45674 C23.30439,18.77834 23.89429,18.77664 24.32119,18.45264 L30.63189,13.66324 C31.47419,13.02404 32.57369,14.03204 32.00999,14.92654 L26.13499,24.24744 C25.20039,25.73014 23.19919,26.09944 21.79709,25.04774 L17.12429,21.54314 C16.69559,21.22164 16.10569,21.22334 15.67879,21.54734 L9.36809,26.33674 C8.52579,26.97594 7.42629,25.96794 7.99009,25.07344" id="bolt" fill="#FFFFFF"></path></g></g></svg></g></g></svg></svg></div>';
            document.body.appendChild(closeBadge);

            var span = document.getElementById("close-chatbo-modal");
            var active = document.getElementById('chatbo-aktiv-btn');
            var previewBox = document.getElementById('chat-preview-box');
            var previewButton = document.getElementById('chat-preview-button');
            var previewClose = document.getElementById('chat-preview-close');

            previewClose.onclick = function () {
                checkBtn.style.display = "none";
                closeBadge.style.display = "block";
                setCookie("chatbo-widget-"+data_chatbo_chat.page_id+"-"+data_chatbo_chat.widget_id, "close", {'expires':3600*24*30});
            };

            closeBadge.onclick = function() {
                closeBadge.style.display = "none";
                checkBtn.style.display = "block";
                setCookie("chatbo-widget-"+data_chatbo_chat.page_id+"-"+data_chatbo_chat.widget_id, "open", {'expires':3600*24*30});
            };

            previewBox.onclick = function() {
                modal.style.display = "block";
            };
            previewButton.onclick = function() {
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
                    setCookie("chatbo-widget-"+data_chatbo_chat.page_id+"-"+data_chatbo_chat.widget_id, "active", {'expires':3600*24*30});
                    modal.style.display = "none";
                    checkBtn.outerHTML = "";
                    delete checkBtn;
                    runChat();
                }
                else{
                    errorPolicy.style.display = "block";
                }
            };

            window.onclick = function(event) {
                if (event.target == modal) {
                    modal.style.display = "none";
                }
            };

            if(getCookie("chatbo-widget-"+data_chatbo_chat.page_id+"-"+data_chatbo_chat.widget_id) == 'close'){
                checkBtn.style.display = "none";
                closeBadge.style.display = "block";
            }
            else{
                closeBadge.style.display = "none";
                checkBtn.style.display = "block";
            }
        }

    }
}

function runChat() {
    var chatDiv = document.createElement("div");

    chatDiv.setAttribute("class","fb-customerchat");
    chatDiv.setAttribute("page_id", data_chatbo_chat.page_id);
    chatDiv.setAttribute("ref", data_chatbo_chat.widget_id);

    document.body.appendChild(chatDiv);
    if(typeof FB !== 'undefined' && typeof FB.CustomerChat !== 'undefined'){
        FB.XFBML.parse();
    }
    else{
        setTimeout(function () {
            window.fbAsyncInit = function() {
                FB.init({
                    appId: config_chatbo_v2.appId,
                    autoLogAppEvents : true,
                    xfbml            : true,
                    version          : config_chatbo_v2.version
                });
            };

            (function(d, s, id) {
                var js, fjs = d.getElementsByTagName(s)[0];
                if (d.getElementById(id)) return;
                js = d.createElement(s); js.id = id;
                js.src = "//connect.facebook.net/de_DE/sdk/xfbml.customerchat.js";
                fjs.parentNode.insertBefore(js, fjs);
            }(document, 'script', 'facebook-jssdk'));

        }, 100);
    }
    fetch('https://api.chatbo.de/v2/fetch/insights/widget/'+data_chatbo_chat.widget_id, {mode: 'no-cors'}).then(function(response){
        return response;
    });
}

(function(d, s, id) {
    var js, fjs = d.getElementsByTagName(s)[0];
    if (d.getElementById(id)) return;
    js = d.createElement(s); js.id = id;
    js.src = "//connect.facebook.net/de_DE/sdk/xfbml.customerchat.js";
    fjs.parentNode.insertBefore(js, fjs);
}(document, 'script', 'facebook-jssdk'));
