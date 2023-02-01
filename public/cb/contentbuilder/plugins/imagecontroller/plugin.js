(function () {
    const html = `
    <div id="image-controller-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
        <button id="change-image-btn" class="classic-primary" style="width:80%; margin: 20px auto;">Change Image <i class="icon ion-arrow-down-b"> </i></button>
        <div class="link-editor-main-container" callback="updateLinkData" getData="sendDataToLinkEditor">
        </div>
        <div style="background: #f9f9f9;padding: 5px 20px;">Image Editor</div>
        <div id="image-editor-container">
        </div>
    </div>
    `;

    const innerProcess = () => {
        if (!document.querySelector('#image-controller-html .link-editor-main-container').querySelector('.input-text')) {
            setTimeout(() => innerProcess, 0);
            return;
        }
        document.querySelector('#image-controller-html .link-editor-main-container').querySelector('.input-text').setAttribute('placeholder', 'Alt Text');
    }

    const process = () => {
        if (!document.getElementById('image-controller-html')) {
            setTimeout(process, 0);
            return;
        }
        $('#imagedit-panel').appendTo($('#image-controller-html #image-editor-container'));

        document.getElementById('change-image-btn').addEventListener('click', e => {
            document.querySelector("#fileEmbedImage").click();
        });
        setTimeout(innerProcess, 0);
    };

    _cb.addHtmlToLeftPanel(html);
    setTimeout(process, 0);


    window.updateLinkData = (link, text, target) => {
        _cb.uo.saveForUndo();
        var img = _cb.activeImage;
        var lnk;
        if (
            img.parentNode.tagName.toLowerCase() === "a" &&
            img.parentNode.childElementCount === 1
        ) {
            lnk = img.parentNode;
        }
        // var src = modalImageLink.querySelector(".input-src").value;
        var title = text;
        var link = link;
        // if (src.indexOf("[Image Data]") === -1) {
        //   img.setAttribute("src", src);
        // }
        img.setAttribute("alt", title);
        if (link != "") {
            if (lnk) {
                lnk.setAttribute("href", link);
                lnk.setAttribute("title", title);
                if (target) {
                    lnk.setAttribute("target", "_blank");
                } else {
                    lnk.removeAttribute("target");
                }
                if (
                    link.toLowerCase().indexOf(".jpg") != -1 ||
                    link.toLowerCase().indexOf(".jpeg") != -1 ||
                    link.toLowerCase().indexOf(".png") != -1 ||
                    link.toLowerCase().indexOf(".gif") != -1
                ) {
                    if (!lnk.classList.contains()) {
                        lnk.classList.add("is-lightbox");
                    }
                } else {
                    lnk.classList.remove("is-lightbox");
                }
            } else {
                //Create link
                lnk = document.createElement("a");
                lnk.setAttribute("href", link);
                lnk.setAttribute("title", title);
                lnk.innerHTML = img.outerHTML;
                if (target) {
                    lnk.setAttribute("target", "_blank");
                } else {
                    lnk.removeAttribute("target");
                }
                if (
                    link.toLowerCase().indexOf(".jpg") != -1 ||
                    link.toLowerCase().indexOf(".jpeg") != -1 ||
                    link.toLowerCase().indexOf(".png") != -1 ||
                    link.toLowerCase().indexOf(".gif") != -1
                ) {
                    if (!lnk.classList.contains()) {
                        lnk.classList.add("is-lightbox");
                    }
                } else {
                    lnk.classList.remove("is-lightbox");
                }
                img.outerHTML = lnk.outerHTML;
            }
        } else {
            if (lnk) {
                lnk.outerHTML = lnk.innerHTML;
            }
        } //Check if image is part of module snippet. If so, refresh the (active) module (hide imageTool). If not, refresh imageTool position
        // _this2.refreshIfIsModule(img); //Trigger Change event
        _cb.opts.onChange(); //Trigger Render event
        _cb.opts.onRender();
        _cb.elmTool.refresh();
    };

    sendDataToLinkEditor = () => {
        var img = _cb.activeImage;
        var lnk;
        if (
            img.parentNode.tagName.toLowerCase() === "a" &&
            img.parentNode.childElementCount === 1
        ) {
            lnk = img.parentNode;
        }
        if (lnk) {
            return {
                url: lnk.getAttribute("href"),
                text: img.getAttribute('alt'),
                target: !!lnk.hasAttribute("target")
            };
        }

        return {
            url: "",
            text: img.getAttribute('alt'),
            target: ""
        };
    };

    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {

        let elm = e.target;
        var ret = oldget.apply(this, arguments);

        if (elm.tagName.toLowerCase() === 'img' && !elm.classList.contains('no-image-edit')) {
            document.getElementById('divImageTool').style.display = 'none';
            document.getElementById('quick-settings-title').innerText = "Image Settings";
            _cb.showLeftSidePanel('image-controller-html');
            setTimeout(() => {
                document.querySelector(".image-edit").click();
                const con = document.getElementById('image-controller-html').querySelector('.link-editor-main-container');
                const data = sendDataToLinkEditor();
                con.querySelector('.input-url').value = data.url;
                con.querySelector('.input-newwindow').value = data.target;
                con.querySelector('.input-text').value = data.text;
            }, 10);
        }else{
            if(elm.classList.contains('social-button-icon')){
                document.querySelector('.elementstyles').classList.remove('active')
                document.getElementById('divSnippetList').classList.remove('active')
            }
            
        }
        return ret;
    };
})();