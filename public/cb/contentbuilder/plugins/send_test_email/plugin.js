(function () {
    var html = '<div id="sendtestemailFrom-html" class="sendtestemailFrom" style="width:100%;z-index:10004;left: 20px;">' +
        '<div style="width:100%;height:200px;background:#fff;position: relative;display: flex;flex-direction: column;align-items: center;padding: 0px;background:#f8f8f8;">' +
        '<div class="is-modal-bar" style="position: absolute;top: 0;left: 0;width: 100%;z-index:1;line-height:32px;height:32px;background:#f9f9f9; text-align: left;">' + _cb.out('Send Test Email') +
        '</div>' +
        '<div data-width="1440" style="width:100%;height:100%;max-width:1440px;border:none;border-top:32px solid transparent;margin:0;box-sizing:border-box;background:#fff;display: flex;justify-content: space-around;padding: 10px;flex-direction: column;"> ' +
        '<div style="display: flex;">' +
        '<label>Test Email Id: </label>' +
        '<input type="email" id="sendTestEmailId" value="" style="flex-grow: 2;">' +
        '</div>' +
        '<span style="width: 100%; text-align: center; color: red; font-size: 14px; display: none;" id="sendTestEmailError">Please enter valid email id</span>' +
        '<span style="width: 100%; text-align: center; color: green; font-size: 14px; display: none;" id="sendTestEmailSuccess">Email sent successfully</span>' +
        '<span style="width: 100%; text-align: center; color: #a67600; font-size: 14px; display: none;" id="sendTestEmailPleaseWait">Sending Email, please wait....</span>' +
        '<button class="classic-primary" type="button" style="width: 100%;" id="sendTestEmailButton">Send Email</button>' +
        '</div>' +
        '</div>' +
        '</div>';

    const validateEmail = (email) => {
        return String(email)
            .toLowerCase()
            .match(
                /^(([^<>()[\]\\.,;:\s@"]+(\.[^<>()[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/
            );
    };

    let timeOutId = null;

    const process = () => {
        console.log('Send Test Mail Process - Start');
        if (!document.getElementById('sendTestEmailButton')) {
            console.log('Send Test Mail Process - Not Found');
            setTimeout(process, 0);
            return;
        }
        console.log('Send Test Mail Process - Found');
        
        document.getElementById('btnSendTestEmail').addEventListener('click', e => {
            document.getElementById('quick-settings-title').innerText = "Send Test Email";
            _cb.showLeftSidePanel('sendtestemailFrom-html');
        });
        
        document.getElementById('sendTestEmailButton').addEventListener('click', e => {
            const email = document.getElementById('sendTestEmailId').value;
            if (email === "" || !validateEmail(email)) {
                document.getElementById('sendTestEmailError').style.display = 'block';
                // document.getElementById('sencyan').style.display = 'block';
                document.getElementById('sendTestEmailSuccess').style.display = 'none';
                return;
            }
            document.getElementById('sendTestEmailError').style.display = 'none';
            var basehref = "";
            var base = document.querySelectorAll("base[href]");
            if (base.length > 0) {
                basehref = '<base href="' + base[0].href + '" />';
            }

            var csslinks = "";
            var styles = document.querySelectorAll("link[href]");
            for (var i = 0; i < styles.length; i++) {
                if (
                    styles[i].href.indexOf(".css") != -1 &&
                    styles[i].href.indexOf("contentbox.css") == -1 &&
                    styles[i].href.indexOf("contentbuilder.css") == -1
                ) {
                    csslinks +=
                        '<link href="' +
                        styles[i].href +
                        '" rel="stylesheet" type="text/css" />';
                }
            }

            var jsincludes = "";
            var scripts = document.querySelectorAll("script[src]");
            for (var i = 0; i < scripts.length; i++) {
                if (
                    scripts[i].src.indexOf(".js") != -1 &&
                    scripts[i].src.indexOf("index.js") == -1 &&
                    scripts[i].src.indexOf("contentbox.js") == -1 &&
                    scripts[i].src.indexOf("contentbox.min.js") == -1 &&
                    scripts[i].src.indexOf("contentbuilder.js") == -1 &&
                    scripts[i].src.indexOf("contentbuilder.min.js") == -1 &&
                    scripts[i].src.indexOf("plugin.js") == -1 &&
                    scripts[i].src.indexOf("config.js") == -1 &&
                    scripts[i].src.indexOf("en.js") == -1 &&
                    scripts[i].src.indexOf("minimalist-blocks") == -1
                ) {
                    jsincludes +=
                        '<script src="' +
                        scripts[i].src +
                        '" type="text/javascript"></script>';
                }
            }
            let html = '';
            const clr = document.querySelector('.container_bg').style.backgroundColor;

            /* Get Page */
            if (!document.querySelector(".is-wrapper")) {
                var maxwidth = "800px";
                var maxw = window.getComputedStyle(document.querySelector(".is-builder")).getPropertyValue('max-width');
                if (!isNaN(parseInt(maxw))) maxwidth = maxw;

                var content = _cb.html();

                content = content.replaceAll('src="/cb/', `src="${window.location.origin}/cb/`);
                html = `<div style="background-color:${clr};"  class="container_bg" >
                    <div class="container"> 
                        ${content}
                    </div>
                </div>`;

            } else {
                // ContentBox
                var content = jQuery(".is-wrapper")
                    .data("contentbox")
                    .html();

                content = content.replaceAll('src="/cb/', `src="${window.location.origin}/cb/`);
                html = `<div style="background-color:${clr};"  class="container_bg" >
                    <div class="container"> 
                        ${content}
                    </div>
                </div>`;
            }

            document.getElementById('sendTestEmailPleaseWait').style.display = 'block';
            e.currentTarget.style.display = 'none';
            document.getElementById('sendTestEmailSuccess').style.display = 'none';
            fetch(_cb.settings.sendEmailAPI, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ recipient: email, content: html })
            })
                .then(res => {
                    console.log(res);
                    if (res.status !== 200) {
                        document.getElementById('sendTestEmailPleaseWait').style.display = 'none';
                        document.getElementById('sendTestEmailError').style.display = 'block';
                        document.getElementById('sendTestEmailError').innerText = 'Failed to send test email, please try again...';//res.statusText;
                        document.getElementById('sendTestEmailSuccess').style.display = 'none';
                        document.getElementById('sendTestEmailButton').style.display = 'block';
                    } else {
                        if(timeOutId) {
                            clearTimeout(timeOutId);
                        }
                        document.getElementById('sendTestEmailPleaseWait').style.display = 'none';
                        document.getElementById('sendTestEmailSuccess').style.display = 'block';
                        document.getElementById('sendTestEmailButton').style.display = 'block';
                        timeOutId = setTimeout(() => {
                            document.getElementById('sendTestEmailSuccess').style.display = 'none';
                            timeOutId = null;
                        }, 2000);
                    }
                }).catch(e => {
                    document.getElementById('sendTestEmailPleaseWait').style.display = 'none';
                    document.getElementById('sendTestEmailError').style.display = 'block';
                    document.getElementById('sendTestEmailError').innerText = e.message;
                    document.getElementById('sendTestEmailSuccess').style.display = 'none';
                    document.getElementById('sendTestEmailButton').style.display = 'block';
                });
        });
    };

    _cb.addHtmlToLeftPanel(html);
    setTimeout(process, 0);

})();
