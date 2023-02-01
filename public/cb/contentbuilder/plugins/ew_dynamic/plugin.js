(function () {
    var html = `
    <div id="ew_dynamic_editor" class="is-modal1 ew_dynamic_editor" style="width: 100%;">
        <div style="width:505px;height:620px;background:#fff;position: relative;display: flex;flex-direction: column;align-items: center;padding: 0px;background:#f8f8f8;">
            <div class="is-modal-bar is-draggable" style="width: 100%;height:32px;background:#f9f9f9; text-align: left;"> Dynamic Widget Editor</div>
            <iframe data-width="1440" style="width:100%;height:100%;max-width:1440px;border:none;margin:0;box-sizing:border-box;background:#fff;" src="about:blank"></iframe>
        </div>
    </div>`;

    _cb.addHtmlToLeftPanel(html);

    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {
        let elm = e.target;
        var ret = oldget.apply(this, arguments);
        _cb.inspectedElement = elm;
        _cb.elmTool.elementPanel.showPanel();

        if (elm.classList.contains('ew_dynamic')) {

            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let scriptPath = _cb.getScriptPath();
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = scriptPath + 'plugins/ew_dynamic/ew_dynamic.html';
            }, 0);
        }

        if (elm.classList.contains('ew_dynamic_abandoned_cart')) {
            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = '/ew_dynamic/setup/abandoned_cart';
            }, 0);
        }

        if (elm.classList.contains('ew_dynamic_cart')) {
            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = '/ew_dynamic/setup/cart';
            }, 0);
        }

        if (elm.classList.contains('ew_dynamic_chat')) {
            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = '/ew_dynamic/setup/chat';
            }, 0);
        }

        if (elm.classList.contains('ew_dynamic_product')) {
            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = '/ew_dynamic/setup/product';
            }, 0);
        }

        if (elm.classList.contains('ew_dynamic_products3')) {
            _cb.showLeftSidePanel('ew_dynamic_editor');

            _cb.saveForUndo(true); // checkLater = true

            setTimeout(() => {
                let modal = document.querySelector('.is-modal1.ew_dynamic_editor');
                modal.querySelector('iframe').src = '/ew_dynamic/setup/products3';
            }, 0);
        }

        return ret;
    };
})();

