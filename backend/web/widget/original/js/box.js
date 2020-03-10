
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
    searchBoxWidget();
};

loadScript("//widget.chatbo.de/original/js/config.js", handler);

function afterClickOfButton(){

    //click for button and after submit
    var afterSubmit = data_chatbo_box.after_submit;
    var urlOpenAfterSubmision = data_chatbo_box.url_open_after_submission;
    var openThisUrl = data_chatbo_box.open_this_url;

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

// assay for url image
var generateImg = function(url_image){
    var html = '';
    html += '<div class="img-p place-i">';
    html += '<img src="'+url_image+'">';
    html += '</div>';

    return html;
}

function newDivAfter(){
    var box = document.querySelector('.chatbo-box-widget-embed[data-widget-id*="'+data_chatbo_box.widget_id+'"]');

    var htmlPageTakeoverAfter = '';
    if (data_chatbo_box.fit_container == 'true' || data_chatbo_box.fit_container == '1') {
        htmlPageTakeoverAfter += '<div class="pr-bl box-w opct-tr before-submit btn-button-type" style="background: '+data_chatbo_box.after_submit_color_background+'; width: 100%; max-width: 100%; left: 0;">';
    }
    else{
        htmlPageTakeoverAfter += '<div class="pr-bl box-w opct-tr before-submit btn-button-type" style="background: '+data_chatbo_box.after_submit_color_background+'; width: '+data_chatbo_box.width_container+'px; max-width:'+data_chatbo_box.width_container+'px;">';
    }
    htmlPageTakeoverAfter += '<div class="cont-st-p">';
    if (data_chatbo_box.after_submit_image_place == '0' && data_chatbo_box.after_submit_image !== '') {
        htmlPageTakeoverAfter += generateImg(data_chatbo_box.after_submit_image);
    }
    htmlPageTakeoverAfter += '<h2 class="ed-t" style="color: '+data_chatbo_box.after_submit_color_headline+';">'+data_chatbo_box.slidein_submit_title+'</h2>';
    if (data_chatbo_box.after_submit_image_place == '1' && data_chatbo_box.after_submit_image !== '') {
        htmlPageTakeoverAfter += generateImg(data_chatbo_box.after_submit_image);
    }
    htmlPageTakeoverAfter += '<p class="ed-t after-show-desc" style="color: '+data_chatbo_box.after_submit_color_desc+';">'+data_chatbo_box.slidein_submit_desc+'</p>';
    if (data_chatbo_box.after_submit_image_place == '2' && data_chatbo_box.after_submit_image !== '') {
        htmlPageTakeoverAfter += generateImg(data_chatbo_box.after_submit_image);
    }
    htmlPageTakeoverAfter += '<div class="s-m-i">';
    htmlPageTakeoverAfter += '<a href="https://m.me/'+data_chatbo_box.page_id+'" target="_blank" class="button-style-w" style="background-color: '+data_chatbo_box.after_submit_color_button_background+'; color: '+data_chatbo_box.after_submit_color_button_text+'">'+data_chatbo_box.slidein_submit_button_title+'</a>';
    htmlPageTakeoverAfter += '</div>';
    htmlPageTakeoverAfter += '</div>';
    htmlPageTakeoverAfter += '</div>';

    box.innerHTML = htmlPageTakeoverAfter;
    //document.body.appendChild(box);

    // After show description
    var showDescAfter = document.querySelector('.after-show-desc');
    if (data_chatbo_box.show_desc_after == 'true' || data_chatbo_box.show_desc_after == '1') {
        showDescAfter.style.display = 'block';
    }
    else{
        showDescAfter.parentNode.removeChild(showDescAfter);
    }
}


function initWidget(){

    var box = document.querySelector('.chatbo-box-widget-embed[data-widget-id*="'+data_chatbo_box.widget_id+'"]');
    if(box){
        var htmlPageTakeoverBeforeCheckbox = '';
        if (data_chatbo_box.fit_container == 'true' || data_chatbo_box.fit_container == '1') {
            htmlPageTakeoverBeforeCheckbox += '<div class="pr-bl box-w opct-tr before-submit btn-button-type" style="background: '+data_chatbo_box.color_background+'; width: 100%; max-width: 100%; left: 0;">';
        }
        else{
            htmlPageTakeoverBeforeCheckbox += '<div class="pr-bl box-w opct-tr before-submit btn-button-type" style="background: '+data_chatbo_box.color_background+'; width: '+data_chatbo_box.width_container+'px; max-width:'+data_chatbo_box.width_container+'px;">';
        }
        htmlPageTakeoverBeforeCheckbox += '<div class="cont-st-p">';
        if (data_chatbo_box.image_place == '0' && data_chatbo_box.image !== '') {
            htmlPageTakeoverBeforeCheckbox += generateImg(data_chatbo_box.image);
        }
        htmlPageTakeoverBeforeCheckbox += '<h2 class="ed-t place-i" style="color: '+data_chatbo_box.color_headline+';">'+data_chatbo_box.slidein_title+'</h2>';
        if (data_chatbo_box.image_place == '1' && data_chatbo_box.image !== '') {
            htmlPageTakeoverBeforeCheckbox += generateImg(data_chatbo_box.image);
        }
        htmlPageTakeoverBeforeCheckbox += '<p class="ed-t show-desc place-i" style="color: '+data_chatbo_box.color_desc+';">'+data_chatbo_box.slidein_desc+'</p>';
        if (data_chatbo_box.image_place == '2' && data_chatbo_box.image !== '') {
            htmlPageTakeoverBeforeCheckbox += generateImg(data_chatbo_box.image);
        }

        //if(data_chatbo_box.button_type != 'btn'){
        htmlPageTakeoverBeforeCheckbox += '<div class="s-m-i" style="max-width: 280px;">';
        if(getCookie("chatbo-widget-"+data_chatbo_box.page_id+"-"+data_chatbo_box.widget_id) == 'active'){
            htmlPageTakeoverBeforeCheckbox += '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_box.color_button_background+'; color: '+data_chatbo_box.color_button_text+'">'+data_chatbo_box.slidein_button_title+'</button>';
            htmlPageTakeoverBeforeCheckbox += '<div class="fb-messenger-checkbox"' +
                '                             origin="'+config_chatbo_v2.domain+'"' +
                '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                '                             page_id="'+data_chatbo_box.page_id+'"' +
                '                             user_ref="'+user_ref+'"' +
                '                             data-ref="'+data_chatbo_box.widget_id+'"' +
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
            htmlPageTakeoverBeforeCheckbox += '<button class="check-chatbo-btn1 button-style-w" id="check-chatbo-btn" style="background:'+data_chatbo_box.check_button_bg+';color:'+data_chatbo_box.check_button_color+';">'+data_chatbo_box.check_button_text+'</button>';
        }

        htmlPageTakeoverBeforeCheckbox += '</div>';

        htmlPageTakeoverBeforeCheckbox += '</div>';
        htmlPageTakeoverBeforeCheckbox += '</div>';

        box.innerHTML = htmlPageTakeoverBeforeCheckbox;

        if(getCookie("chatbo-widget-"+data_chatbo_box.page_id+"-"+data_chatbo_box.widget_id) != 'active'){
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
                    setCookie("chatbo-widget-"+data_chatbo_box.page_id+"-"+data_chatbo_box.widget_id, "active", {'expires':3600*24*30});
                    modal.style.display = "none";
                    var showPluginHtml = '<button class="button-style-w" id="checkbox-btn" onclick="confirmOptIn();" style="width: 280px;background-color:'+data_chatbo_box.color_button_background+'; color: '+data_chatbo_box.color_button_text+'">'+data_chatbo_box.slidein_button_title+'</button>';
                    showPluginHtml += '<div class="fb-messenger-checkbox"' +
                        '                             origin="'+config_chatbo_v2.domain+'"' +
                        '                             messenger_app_id="'+config_chatbo_v2.appId+'"' +
                        '                             page_id="'+data_chatbo_box.page_id+'"' +
                        '                             user_ref="'+user_ref+'"' +
                        '                             data-ref="'+data_chatbo_box.widget_id+'"' +
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


        // Show description
        var showDesc = document.querySelector('.cont-st-p .show-desc');
        if (data_chatbo_box.show_desc == 'true' || data_chatbo_box.show_desc == '1') {
            showDesc.style.display = 'block';
        }
        else{
            showDesc.parentNode.removeChild(showDesc);
        }
    }
}

function searchBoxWidget(){
    if(data_chatbo_box.status == '0'){
        console.log('Widget deactivated');
        return false;
    }

    setTimeout(function () {
        initWidget();
    }, 500);
}

function confirmOptIn() {
    FB.AppEvents.logEvent('MessengerCheckboxUserConfirmation', null, {
        'app_id': config_chatbo_v2.appId,
        'page_id': data_chatbo_box.page_id,
        'ref': data_chatbo_box.widget_id,
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

    fetch('https://api.chatbo.de/v2/fetch/insights/widget/'+data_chatbo_box.widget_id, {mode: 'no-cors'}).then(function(response){
        return response;
    });
}
