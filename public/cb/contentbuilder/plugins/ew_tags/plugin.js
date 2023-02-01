(function () {
    const html = `
    <div id="ew-tags-html" style="    display: flex;flex-direction: column;flex-grow: 2;">
        <iframe src="${_cb.settings.ewTagsSource}" style="width:100%;height:100%;position:absolute;top:0;left:0;border: none;"></iframe>
    </div>
    `;

    _cb.addHtmlToLeftPanel(html);

    var button = '<button class="insertew_tag-button" title="Emailwish Tags" style="font-size:14px;vertical-align:bottom;">' +
        '<svg class="is-icon-flex" style="margin-top:-1px"><use xlink:href="#ion-code-working"></use></svg>' +
        '</button>';

    _cb.addButton('ew_tags', button, '.insertew_tag-button', function () {
        document.getElementById('quick-settings-title').innerText = "Tags";
        _cb.showLeftSidePanel('ew-tags-html');
    });

})();