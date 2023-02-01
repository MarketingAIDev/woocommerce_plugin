/*
    Button Editor Plugin
*/

(function () {
    const html = `
    <div id="button-editor-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
        <div class="link-editor-main-container">
        </div>
        <iframe id="buttoneditor-iframe" data-width="1440" style="width:100%;flex-grow:2;max-width:1440px;border:none;margin:0;box-sizing:border-box;background:#fff;" src="about:blank"></iframe>
    </div>
    `;
    const process = () => {
        if(!document.getElementById('button-editor-html')) {
            setTimeout(process, 0);
            return;        
        }
        var scriptPath = _cb.getScriptPath();
        document.querySelector('#buttoneditor-iframe').src = scriptPath + 'plugins/buttoneditor/buttoneditor.html';
        const lnk = document.querySelector('#button-editor-html .link-editor-main-container');
        lnk.setAttribute('callback', "linkUpdater");
    };

    _cb.addHtmlToLeftPanel(html);
    setTimeout(process, 0);
    
    window.linkUpdater = (link, text, target) => {
        if(!_cb.activeLink) return;
        _cb.activeLink.setAttribute("href", link);
        if (target) {
            _cb.activeLink.setAttribute("target", "_blank");
        } else {
            _cb.activeLink.removeAttribute("target");
        }
        if (!_cb.activeIcon) {
            _cb.activeLink.innerHTML = text;
        }
        _cb.activeLink.setAttribute("title", text);

    };

    getLinkInformation = () => {
        let text = "";
        if (!_cb.activeIcon) {
            text = _cb.activeLink.innerText;
        } else {
            text = _cb.activeLink.getAttribute("title");
        }
        return {
            url: _cb.activeLink.getAttribute("href"),
            text,
            target: !!_cb.activeLink.hasAttribute("target")
        };
    };

    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {

        let elm = e.target;

        var ret = oldget.apply(this, arguments);

        var elmDisplay = getStyle(elm, 'display');
        if ((elm.tagName.toLowerCase() === 'a' && (elmDisplay === 'inline-block' || elmDisplay === 'block'))) {
            setTimeout(() => {
                document.querySelector('#buttoneditor-iframe').contentWindow.processButtonEditor();
                const con = document.getElementById('button-editor-html').querySelector('.link-editor-main-container');
                const data = getLinkInformation();
                con.querySelector('.input-url').value = data.url;
                con.querySelector('.input-newwindow').value = data.target;
                con.querySelector('.input-text').value = data.text;
                if(elm.classList.contains('readonly-link')) {
                    con.style.display = 'none';
                } else {
                    con.style.display = 'block';
                }
            }, 0);
            document.getElementById('quick-settings-title').innerText = "Button Settings";
            _cb.showLeftSidePanel('button-editor-html');
        }
        return ret;
    };


    var getStyle = function (element, property) {
        return window.getComputedStyle ? window.getComputedStyle(element, null).getPropertyValue(property) : element.style[property.replace(/-([a-z])/g, function (g) { return g[1].toUpperCase(); })];
    }

})();

