(function () {
    const html = `
    <div id="dynamic-chat-settings" style="display: flex;flex-direction: column;flex-grow: 2;justify-content: space-between;">
        <div id="dynamic-chat-settings-content">
            <table style="width: 100%;">
                <thead>
                    <tr>
                        <th></th>
                        <th style="font-family: sans-serif; font-size: 13px;">Background</th>
                        <th style="font-family: sans-serif; font-size: 13px;">Text</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-family: sans-serif; font-size: 13px;">Left Chat Bubble</td>
                        <td><div id="left-chat-bubble-background-color" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: #00bfff"></div></td>
                        <td><div id="left-chat-bubble-text-color" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: #00bfff"></div></td>
                    </tr>
                    <tr>
                        <td style="font-family: sans-serif; font-size: 13px;">Right Chat Bubble</td>
                        <td><div id="right-chat-bubble-background-color" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: #00bfff"></div></td>
                        <td><div id="right-chat-bubble-text-color" style="margin: 0 auto; border: 1px solid rgb(199, 199, 199); width:40px; height: 40px; background-color: #00bfff"></div></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div id="dynamic-chat-settings-note" style="padding: 10px; background: #ffc107; text-align:center;">The actual content will be different but styles will be the same.</div>
    </div>
    `;

    _cb.addHtmlToLeftPanel(html);

    const mainContainerClass = 'chat-main-container';
    let mainContainer = null;
    let settingsContainer = null;

    const leftChatBubbleBackgroundColorAttribute = 'leftChatBubbleBackgroundColor';
    const leftChatBubbleTextColorAttribute = 'leftChatBubbleTextColor';
    const rightChatBubbleBackgroundColorAttribute = 'rightChatBubbleBackgroundColor';
    const rightChatBubbleTextColorAttribute = 'rightChatBubbleTextColor';

    const prepareSettings = () => {
        const leftChatBubbleBackgroundColorSetting = settingsContainer.querySelector('#left-chat-bubble-background-color');
        const leftChatBubbleTextColorSetting = settingsContainer.querySelector('#left-chat-bubble-text-color');
        const rightChatBubbleBackgroundColorSetting = settingsContainer.querySelector('#right-chat-bubble-background-color');
        const rightChatBubbleTextColorSetting = settingsContainer.querySelector('#right-chat-bubble-text-color');

        let leftBgColor = window.getComputedStyle(mainContainer.querySelector('.left-chat-bubble')).backgroundColor;
        let rightBgColor = window.getComputedStyle(mainContainer.querySelector('.right-chat-bubble')).backgroundColor;
        let leftTextColor = window.getComputedStyle(mainContainer.querySelector('.left-chat-bubble')).color;
        let rightTextColor = window.getComputedStyle(mainContainer.querySelector('.right-chat-bubble')).color;

        leftChatBubbleBackgroundColorSetting.style.backgroundColor = leftBgColor;
        rightChatBubbleBackgroundColorSetting.style.backgroundColor = rightBgColor;
        leftChatBubbleTextColorSetting.style.backgroundColor = leftTextColor;
        rightChatBubbleTextColorSetting.style.backgroundColor = rightTextColor;

        mainContainer.setAttribute(leftChatBubbleBackgroundColorAttribute, leftBgColor);
        mainContainer.setAttribute(rightChatBubbleBackgroundColorAttribute, rightBgColor);
        mainContainer.setAttribute(leftChatBubbleTextColorAttribute, leftTextColor);
        mainContainer.setAttribute(rightChatBubbleTextColorAttribute, rightTextColor);

        leftChatBubbleBackgroundColorSetting.addEventListener('click', e => {
            _cb.uo.saveForUndo(true);
            var elm = e.target;
            _cb.colorPicker.open(function (color) {
                elm.style.backgroundColor = color; // preview
                mainContainer.setAttribute(leftChatBubbleBackgroundColorAttribute, color);

                const elms = mainContainer.querySelectorAll('.left-chat-bubble');
                Array.prototype.forEach.call(elms, elm => elm.style.backgroundColor = color);

            }, leftChatBubbleBackgroundColorSetting.style.backgroundColor, e.currentTarget);
        });

        rightChatBubbleBackgroundColorSetting.addEventListener('click', e => {
            _cb.uo.saveForUndo(true);
            var elm = e.target;
            _cb.colorPicker.open(function (color) {
                elm.style.backgroundColor = color; // preview
                mainContainer.setAttribute(rightChatBubbleBackgroundColorAttribute, color);

                const elms = mainContainer.querySelectorAll('.right-chat-bubble');
                Array.prototype.forEach.call(elms, elm => elm.style.backgroundColor = color);

            }, rightChatBubbleBackgroundColorSetting.style.backgroundColor, e.currentTarget);
        });

        leftChatBubbleTextColorSetting.addEventListener('click', e => {
            _cb.uo.saveForUndo(true);
            var elm = e.target;
            _cb.colorPicker.open(function (color) {
                elm.style.backgroundColor = color; // preview
                mainContainer.setAttribute(leftChatBubbleTextColorAttribute, color);

                const elms = mainContainer.querySelectorAll('.left-chat-bubble');
                Array.prototype.forEach.call(elms, elm => elm.style.color = color);

            }, leftChatBubbleTextColorSetting.style.backgroundColor, e.currentTarget);
        });

        rightChatBubbleTextColorSetting.addEventListener('click', e => {
            _cb.uo.saveForUndo(true);
            var elm = e.target;
            _cb.colorPicker.open(function (color) {
                elm.style.backgroundColor = color; // preview
                mainContainer.setAttribute(rightChatBubbleTextColorAttribute, color);

                const elms = mainContainer.querySelectorAll('.right-chat-bubble');
                Array.prototype.forEach.call(elms, elm => elm.style.color = color);

            }, rightChatBubbleTextColorSetting.style.backgroundColor, e.currentTarget);
        });
    };

    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {
        let elm = e.target;
        var ret = oldget.apply(this, arguments);

        while (elm && !elm.classList?.contains(mainContainerClass)) {
            elm = elm.parentNode;
        }

        if (elm) {
            mainContainer = elm;
            settingsContainer = document.getElementById('dynamic-chat-settings-content');
            document.getElementById('quick-settings-title').innerText = "Chat Settings";
            prepareSettings();
            _cb.showLeftSidePanel('dynamic-chat-settings');
        }

        return ret;
    };

})();