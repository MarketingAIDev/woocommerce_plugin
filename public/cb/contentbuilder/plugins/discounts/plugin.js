(function () {
    const html = `
    <div id="discounts-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
        <iframe src="${_cb.getScriptPath()}plugins/discounts/render.html" style="width:100%;height:100%;position:absolute;top:0;left:0;border: none;"></iframe>
    </div>
    `;

    var button = '<button class="insertdiscounts-button" title="Discounts" style="font-size:14px;vertical-align:bottom;">' +
        '<i class="icon ion-cash"> </i>' +
        '</button>';

    _cb.addHtmlToLeftPanel(html);

    _cb.addButton('discounts', button, '.insertdiscounts-button', function () {
        document.getElementById('quick-settings-title').innerText = "Discounts";
        _cb.showLeftSidePanel('discounts-html');
    });
})();