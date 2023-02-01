(function () {
    var html = '<div id="imagetext-editor" class="imagetext-editor" style="width:100%;">' +
        '<div style="width:505px;height:450px;background:#fff;position: relative;display: flex;flex-direction: column;align-items: center;padding: 0px;">' +
        '<div class="is-modal-bar is-draggable" style="text-align:left;width: 100%;background:#f9f9f9;">' + _cb.out('Text On Image Editor') +
        '</div>' +
        '<div style="display: flex; flex-direction: column;align-items: center; padding-top: 50px;">' +
        '<div id="imagetext-editor-img" style="width:200px; height:200px;"></div>' +
        '<button class="classic-primary"  id="imagetext-editor-change-img" style="width: 200px; margin-top: 5px;">Change Image</button>' +
        '<div style="display: flex; width: 100%; justify-content: space-around; margin-top: 20px;">' +
        '<button data-title="Zoom In" id="imagetext-editor-zoom-in" class="classic btn btn-secondary mt-20 white-cap-btn" style="background: transparent;height: 60px;width: 60px;display: flex;justify-content: center;align-items: center;"><i class="icon ion-ios-minus-empty" style="font-size: 24px;"> </i></button>' +
        '<input type="range" id="imagetext-editor-slider" min="1" max="2" step="0.05" value="1" class="slider" id="zoom-slider" style="width: 250px;margin-top: 5px;">' +
        '<button data-title="Zoom Out" id="imagetext-editor-zoom-out" class="classic btn btn-secondary mt-20 white-cap-btn" style="background: transparent;height: 60px;width: 60px;display: flex;justify-content: center;align-items: center;"><i class="icon ion-ios-plus-empty" style="font-size: 24px;"> </i></button>' +
        '</div>' +
        '<p id="imagetext-editor-zoom-value">Zoom: 1</p>' +
        '</div>' +
        '</div>' +
        '</div>';

    let elm = null;

    document.getElementById('add_image_over_text').addEventListener('click', () => {
        const img = '/cb/assets/minimalist-blocks/images/photo-1459411552884-841db9b3cc2a-YO89Y1.jpg';

        const html = `
        <table width="100%" role="presentation" cellpadding="0" cellspacing="0" border="0">
            <tr>
                <td class="td-text-on-image" valign="center" align="center" style="background: url(${img}) #B5CFE3; background-size:100% 100%; background-position:center; background-repeat: no-repeat; height: 230px;">
                    <h1 style="position: relative;">This is text</h1>
                </td>
            </tr>
        </table>`;
        _cb.util.addContent(html, 'row');
    });

    _cb.addHtmlToLeftPanel(html);

    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {
        elm = e.target;

        var ret = oldget.apply(this, arguments);

        if (elm.classList.contains('td-text-on-image')) {
            document.getElementById('quick-settings-title').innerText = "Image Settings";
            _cb.showLeftSidePanel('imagetext-editor');
            setTimeout(() => {
                const modal = document.querySelector('.imagetext-editor');
                const img = modal.querySelector('#imagetext-editor-img');
                const imgButton = modal.querySelector('#imagetext-editor-change-img');
                const zoomIn = modal.querySelector('#imagetext-editor-zoom-in');
                const slider = modal.querySelector('#imagetext-editor-slider');
                const zoomOut = modal.querySelector('#imagetext-editor-zoom-out');
                const zoomValue = modal.querySelector('#imagetext-editor-zoom-value');

                const updateZoom = () => {
                    zoomValue.innerText = "Zoom: " + slider.value;
                    const value = parseFloat(slider.value);
                    const size = `${value * 100}% ${value * 100}% `;
                    elm.style.backgroundSize = size;
                    img.style.backgroundSize = size;
                };

                imgButton.addEventListener('click', e => {
                    _cb.elmTool.imageSelectPanel.showPanel(url => {
                        img.style.backgroundImage = "url('" + url + "')";
                        elm.style.backgroundImage = "url('" + url + "')";
                    });
                });

                zoomIn.addEventListener('click', e => {
                    slider.value = parseFloat(slider.value) - 0.05;
                    updateZoom();
                });
                zoomOut.addEventListener('click', e => {
                    slider.value = parseFloat(slider.value) + 0.05;
                    updateZoom();
                });

                slider.addEventListener('input', e => {
                    updateZoom();
                });

                img.style.backgroundImage = elm.style.backgroundImage;
                img.style.backgroundSize = elm.style.backgroundSize;
                img.style.backgroundPosition = elm.style.backgroundPosition;
                img.style.backgroundRepeat = elm.style.backgroundRepeat;
                slider.value = parseFloat(elm.style.backgroundSize) / 100;
                zoomValue.innerText = "Zoom: " + slider.value;
            }, 0);
        }

        return ret;
    };
})();
