(function () {
    const html = `
    <div id="dynamic-abandoned-settings" style="display: flex;flex-direction: column;flex-grow: 2;justify-content: space-between;">
        <div id="dynamic-abandoned-settings-content">
        </div>
        <div id="dynamic-abandoned-settings-note" style="padding: 10px; background: #ffc107; text-align:center;">The actual content will be different but styles will be the same.</div>
    </div>
    `;

    _cb.addHtmlToLeftPanel(html);

    let products =
    {
        "items": [
            {
                "id": 4,
                "text": "sample product",
                "data": "{\"id\": 6861983219891, \"tags\": \"\", \"image\": {\"id\": 29593615106227, \"alt\": null, \"src\": \"https://cdn.shopify.com/s/files/1/0580/4359/6979/products/blogger-boz-579bedef5f9b589aa9915064.jpg?v=1629612431\", \"width\": 3722, \"height\": 2965, \"position\": 1, \"created_at\": \"2021-08-22T11:37:11+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2021-08-22T11:37:11+05:30\", \"variant_ids\": [], \"admin_graphql_api_id\": \"gid://shopify/ProductImage/29593615106227\"}, \"title\": \"sample product\", \"handle\": \"sample-product\", \"images\": [{\"id\": 29593615106227, \"alt\": null, \"src\": \"https://cdn.shopify.com/s/files/1/0580/4359/6979/products/blogger-boz-579bedef5f9b589aa9915064.jpg?v=1629612431\", \"width\": 3722, \"height\": 2965, \"position\": 1, \"created_at\": \"2021-08-22T11:37:11+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2021-08-22T11:37:11+05:30\", \"variant_ids\": [], \"admin_graphql_api_id\": \"gid://shopify/ProductImage/29593615106227\"}], \"status\": \"active\", \"vendor\": \"Emailwish tester store 1\", \"options\": [{\"id\": 8795617132723, \"name\": \"Title\", \"values\": [\"Default Title\"], \"position\": 1, \"product_id\": 6861983219891}], \"variants\": [{\"id\": 40414839505075, \"sku\": \"10\", \"grams\": 0, \"price\": \"10.00\", \"title\": \"Default Title\", \"weight\": 0, \"barcode\": \"\", \"option1\": \"Default Title\", \"option2\": null, \"option3\": null, \"taxable\": false, \"image_id\": null, \"position\": 1, \"created_at\": \"2021-08-22T11:37:09+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2022-02-04T11:23:51+05:30\", \"weight_unit\": \"kg\", \"compare_at_price\": null, \"inventory_policy\": \"continue\", \"inventory_item_id\": 42509750698163, \"requires_shipping\": true, \"inventory_quantity\": 16, \"fulfillment_service\": \"manual\", \"admin_graphql_api_id\": \"gid://shopify/ProductVariant/40414839505075\", \"inventory_management\": \"shopify\", \"old_inventory_quantity\": 16}], \"body_html\": \"lorem ipsum\", \"created_at\": \"2021-08-22T11:37:09+05:30\", \"updated_at\": \"2022-02-04T11:23:51+05:30\", \"product_type\": \"\", \"published_at\": \"2021-08-22T11:37:10+05:30\", \"published_scope\": \"web\", \"template_suffix\": \"\", \"admin_graphql_api_id\": \"gid://shopify/Product/6861983219891\"}"
            }
        ],
        "more": true
    };
    const mainContainerClass = 'abandoned-shopping-cart';
    const containerBodyTag = 'tbody';
    const dataIsDescription = 'data-isdescription';
    let mainContainer = null;
    let containerBody = null;
    let settingsBody = null;

    const buildDefaultTemplate = (e) => {
        mainContainer = e.querySelector('.' + mainContainerClass);
        if (mainContainer) {
            containerBody = mainContainer.querySelector(containerBodyTag);
        }
        if (containerBody) {
            let html = '';
            for (let i = 0; i < 1 && i < products.items.length; ++i) {
                const data = JSON.parse(products.items[i].data);
                html += `
                <tr>
                    <td>
                        <div class="item">
                            <table>
                                <tr>
                                    <td><img class="no-image-edit" src="${data.image.src}" width="100px">
                                    </td>
                                    <td class="mobile-info">
                                        <div class="item-title" contenteditable="false">${data.title}</div>
                                        <div class="item-info" contenteditable="false">${new DOMParser().parseFromString(data.body_html, "text/html").documentElement.querySelector('body').innerText.replace(/\n+/g, '\n')}</div>
                                        <div class="mobile-qty-price" contenteditable="false">1 x $${data.variants[0].price}</div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                    <td>
                        <div class="qty" contenteditable="false">1</div>
                    </td>
                    <td>
                        <div class="price" contenteditable="false">$${data.variants[0].price}</div>
                    </td>
                </tr>
                `;
            }
            containerBody.innerHTML = html;
            parent._cb.applyBehavior();
        }
    };

    fetch(parent._cb.settings.select2Products)
        .then(data => data.json())
        .then(data => {
            products = data;
            const carts = document.querySelectorAll('.' + mainContainerClass);
            Array.prototype.forEach.call(carts, c => {
                buildDefaultTemplate(c.parentNode);
            });
        });

    var oldSaveFn = _cb.opts.onBeforeSave;
    _cb.opts.onBeforeSave = function () {
        const ret = oldSaveFn.apply(this, arguments);
        const carts = document.querySelectorAll('.' + mainContainerClass);
        Array.prototype.forEach.call(carts, c => {
            const rows = c.querySelectorAll(containerBodyTag + ' tr');
            const body = c.querySelector(containerBodyTag);
            body.setAttribute('abandoned-body-starts-r1', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-table', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c1', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c1-img', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c2', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c2-title', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c2-info', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c1-item-r1-c2-price', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c2', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c2-qty', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c3', body.hasAttribute('style') ? body.getAttribute('style') : '');
            body.setAttribute('abandoned-body-starts-r1-c3-price', body.hasAttribute('style') ? body.getAttribute('style') : '');

            Array.prototype.forEach.call(rows, r => r.style.display = 'none');
        });
        return ret;
    }

    var oldASaveFn = _cb.opts.onAfterSave;
    _cb.opts.onAfterSave = function () {
        const ret = oldASaveFn.apply(this, arguments);
        const carts = document.querySelectorAll('.' + mainContainerClass);
        Array.prototype.forEach.call(carts, c => {
            const rows = c.querySelectorAll(containerBodyTag + ' tr');
            Array.prototype.forEach.call(rows, r => r.style.display = 'table-row');
        });
        return ret;
    }

    var oldFn = _cb.opts.onAfterSnippetAdded;
    _cb.opts.onAfterSnippetAdded = function (e) {
        var ret = oldFn.apply(this, arguments);

        buildDefaultTemplate(e);

        return ret;
    }

    const prepareSettings = () => {
        // const count = parseInt(mainContainer.getAttribute(dataCountAttr));
        // const source = mainContainer.getAttribute(productSourceAttr).split(',');
        // const product = mainContainer.getAttribute(productIdAttr).split(',');

        let html = '<table style="width: 100%; table-layout: fixed;">';

        // for (let i = 0; i < count; i++) {
        //     const at = source[i] == 0 ? `<option value="0" selected>From Automation Trigger</option>` : '<option value="0">From Automation Trigger</option>';
        //     const pi = source[i] == 1 ? `<option value="1" selected>From Product ID</option>` : '<option value="1">From Product ID</option>';
        //     html += `
        //     <tr style="display: none;"><td style="font-size: 14px;">Product Source ${i + 1}:</td>
        //         <td style="font-size: 14px;">
        //             <select id="input_source-${i}">
        //                 ${at}
        //                 ${pi}
        //             </select>
        //         </td>
        //     </tr>
        //     <tr><td style="font-size: 14px;">Select Product ${i + 1}:</td>
        //         <td style="font-size: 14px;">
        //             <div id="selectedProductTitleContainer-${i}" style="display: flex; justify-content: space-between; align-items: center; border: solid 1px rgb(196, 196, 196); padding: 3px;;">
        //                 <div id="selectedProductTitle-${i}">Select A Product</div>
        //                 <div style="font-size: 18px;"><i class="icon ion-android-arrow-dropdown"></i></div>
        //             </div>
        //         </td>
        //     </tr>
        //     <tr>
        //         <td colspan="2"><div style="width: 100%; border-top: 1px solid rgb(199, 199, 199);"></div></td>
        //     </tr>
        //     `;
        // }
        let desc = `<input type="checkbox" id="input_description" class="checkbox-switch-input"`;
        desc += containerBody.getAttribute(dataIsDescription) == '0' ? '>' : ' checked>';
        desc += `<label class="checkbox-switch" for="input_description">Toggle</label>`;
        html += `
            <tr><td style="font-size: 14px;">Include Description:</td>
                    <td style="display: flex;">
                        ${desc}
                    </td>
                </tr>
        `;
        // html += `
        // <div id="searchProductsContainer"
        //     style="display: none; flex-direction: column; justify-content: flex-start; width: 80%; border: solid 1px rgb(196, 196, 196); padding: 10px;position: absolute;right: 14px;top: 178px;background: #fff;max-height: 200px;overflow: auto;">
        //     <input type="search" placeholder="Select a Product" id="selectProdcutTextBox">
        //     <div id="product-items-container">

        //     </div>
        // </div>
        // `;
        settingsBody.innerHTML = html;

        // let isNote = false;
        // for (let i = 0; i < count; i++) {
        //     if (source[i] == 0) {
        //         settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'none';
        //         isNote = true;
        //     } else {
        //         settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'table-row';
        //         isNote = isNote;
        //     }

        //     document.getElementById(`selectedProductTitleContainer-${i}`).addEventListener('click', e => {
        //         document.getElementById('searchProductsContainer').style.display = 'flex';
        //         const rect = e.target.getBoundingClientRect();
        //         document.getElementById('searchProductsContainer').style.top = rect.bottom + 'px';
        //         searchForProducts(i);
        //     });

        //     let waitTime = null;
        //     document.getElementById(`selectProdcutTextBox`).addEventListener('input', e => {
        //         const query = e.target.value;
        //         if (waitTime !== null) {
        //             clearTimeout(waitTime);
        //         }
        //         waitTime = setTimeout(() => {
        //             waitTime = null;
        //             searchForProducts(i, query);
        //         }, 1500);
        //     });

        //     settingsBody.querySelector(`#input_source-${i}`).addEventListener('change', e => {
        //         source[i] = e.target.value;
        //         mainContainer.setAttribute(productSourceAttr, source.join());
        //         if (e.target.value == 1) {
        //             settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'table-row';
        //         } else {
        //             settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'none';
        //         }

        //         isNote = false;
        //         for (let j = 0; j < count && !isNote; j++) {
        //             if (settingsBody.querySelector(`#input_source-${j}`).value == 0) {
        //                 isNote = true;
        //             }
        //         }
        //         settingsNote.style.display = isNote ? 'block' : 'none';
        //     });

        // }

        //settingsNote.style.display = isNote ? 'block' : 'none';

        settingsBody.querySelector(`#input_description`).addEventListener('change', e => {
            if (e.target.checked) {
                containerBody.querySelector(`.item-info`).style.display = 'block';
                containerBody.setAttribute(dataIsDescription, '1');
            } else {
                containerBody.querySelector(`.item-info`).style.display = 'none';
                containerBody.setAttribute(dataIsDescription, '0');
            }
        });
    }

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
            containerBody = mainContainer.querySelector(containerBodyTag);
            settingsBody = document.getElementById('dynamic-abandoned-settings-content');

            prepareSettings();
            document.getElementById('quick-settings-title').innerText = "Cart Settings";
            _cb.showLeftSidePanel('dynamic-abandoned-settings');
        }

        return ret;
    };
})();