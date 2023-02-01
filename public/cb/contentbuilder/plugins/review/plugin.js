(function () {
    const html = `
    <div id="submit-review-product-settings" style="display: flex;flex-direction: column;flex-grow: 2;justify-content: space-between;">
        <div id="submit-review-product-settings-content">
        </div>
        <div id="submit-review-product-settings-note" style="padding: 10px; background: #ffc107; text-align:center;">The actual content will be different but styles will be the same.</div>
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
    const mainContainerClass = 'submit-review-product';
    let mainContainer = null;
    let settingsBody = null;
    let settingsNote = null;
    const dataCountAttr = 'data-count';
    const productSourceAttr = 'product-source';
    const productIdAttr = 'product-id';

    const updateProductDetails = (id, pid) => {
        const product = products.items.find(p => p.id == id);
        if (product) {
            const data = JSON.parse(product.data);
            mainContainer.setAttribute('data-product-title', data.title);
            mainContainer.setAttribute('data-product-id', data.id);
            mainContainer.querySelector('img').src = data.image.src;
            mainContainer.querySelector('h2').innerText = data.title;
            mainContainer.querySelector('a').href = _cb.settings.storeDomain + "/products/" + data.handle;
            // mainContainer.querySelector('.dynamic-products-image-' + pid).src = data.image.src;
            // mainContainer.querySelector('.dynamic-products-title-' + pid).innerText = data.title;
            // mainContainer.querySelector('.dynamic-products-price-' + pid).innerText = _cb.settings.storeShopCurrency + ' ' + data.variants[0].price;
            // mainContainer.querySelector('.dynamic-products-description-' + pid).innerText = new DOMParser().parseFromString(data.body_html, "text/html").documentElement.querySelector('body').innerText.replace(/\n+/g, '\n');
            // mainContainer.querySelector('.dynamic-products-button-' + pid).href = _cb.settings.storeDomain + "/products/" + data.handle;
        }
    }

    const fetchProductsList = (id, query) => {
        const url = parent._cb.settings.select2Products + (query === '' ? '' : '?q=' + query);
        fetch(url)
            .then(data => data.json())
            .then(data => {
                products = data;
                let html = '';
                products.items.forEach(p => {
                    html += `<div class="product-items-review" data-id="${p.id}">${p.text}</div>`;
                });
                document.getElementById('product-items-container-review').innerHTML = html;

                Array.prototype.forEach.call(document.querySelectorAll('.product-items-review'), p => {
                    p.addEventListener('click', e => {
                        const selectedProductTitle = document.getElementById(`selectedProductTitle-${id}`);
                        selectedProductTitle.setAttribute('data-id', e.target.getAttribute('data-id'));
                        selectedProductTitle.innerText = e.target.innerText;
                        document.getElementById('searchProductsContainerReview').style.display = 'none';
                        updateProductDetails(e.target.getAttribute('data-id'), id);
                    });
                });
            });
    }

    const searchForProducts = (i, query = '') => {
        const index = i;
        const searchItemsContainer = document.getElementById('product-items-container-review');
        searchItemsContainer.innerHTML = `<div class="product-items-review">Searching for products...</div>`;
        fetchProductsList(index, query);
    }

    const prepareSettings = () => {
        const count = parseInt(mainContainer.getAttribute(dataCountAttr));
        const source = mainContainer.getAttribute(productSourceAttr).split(',');

        let html = '<table style="width: 100%; table-layout: fixed;">';

        for (let i = 0; i < 1; i++) {
            const at = source[i] == 0 ? `<option value="0" selected>From Automation Trigger</option>` : '<option value="0">From Automation Trigger</option>';
            const pi = source[i] == 1 ? `<option value="1" selected>From Product ID</option>` : '<option value="1">From Product ID</option>';
            html += `
            <tr style="display: table-row;"><td style="font-size: 14px;">Product Source ${i + 1}:</td>
                <td style="font-size: 14px;">
                    <select id="input_source-${i}" style="width: 100%;">
                        ${at}
                        ${pi}
                    </select>
                </td>
            </tr>
            <tr><td style="font-size: 14px;">Select Product ${i + 1}:</td>
                <td style="font-size: 14px;">
                    <div id="selectedProductTitleContainer-${i}" style="display: flex; justify-content: space-between; align-items: center; border: solid 1px rgb(196, 196, 196); padding: 3px;;">
                        <div id="selectedProductTitle-${i}">`+ (mainContainer.hasAttribute('data-product-title') ? mainContainer.getAttribute('data-product-title') : 'Select A Product') + `</div>
                        <div style="font-size: 18px;"><i class="icon ion-android-arrow-dropdown"></i></div>
                    </div>
                </td>
            </tr>
            <tr>
                <td colspan="2"><div style="width: 100%; border-top: 1px solid rgb(199, 199, 199);"></div></td>
            </tr>
            `;
        }
        // let desc = `<input type="checkbox" id="input_description"`;
        // desc += mainContainer.querySelector(`.dynamic-products-description-0`).style.display == 'none' ? '>' : ' checked>';
        // html += `
        //     <tr><td style="font-size: 14px;">Include Description:</td>
        //             <td>
        //                 ${desc}
        //             </td>
        //         </tr>
        // `;
        // let price = `<input type="checkbox" id="input_price"`;
        // price += mainContainer.querySelector(`.dynamic-products-price-0`).style.display == 'none' ? '>' : ' checked>';
        // html += `
        //     <tr><td style="font-size: 14px;">Include Price:</td>
        //             <td>
        //                 ${price}
        //             </td>
        //         </tr>
        // </table>
        // `;
        html += '</table>';
        html += `
        <div id="searchProductsContainerReview"
            style="display: none; flex-direction: column; justify-content: flex-start; width: 80%; border: solid 1px rgb(196, 196, 196); padding: 10px;position: absolute;right: 14px;top: 178px;background: #fff;max-height: 200px;overflow: auto;">
            <input type="search" placeholder="Select a Product" id="selectProdcutTextBoxReview">
            <div id="product-items-container-review">

            </div>
        </div>
        `;
        settingsBody.innerHTML = html;

        let isNote = false;
        for (let i = 0; i < 1; i++) {
            if (source[i] == 0) {
                settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'none';
                isNote = true;
            } else {
                settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'table-row';
                isNote = isNote;
            }

            document.getElementById(`selectedProductTitleContainer-${i}`).addEventListener('click', e => {
                document.getElementById('searchProductsContainerReview').style.display = 'flex';
                const rect = e.target.getBoundingClientRect();
                document.getElementById('searchProductsContainerReview').style.top = rect.bottom + 'px';
                searchForProducts(i);
            });

            let waitTime = null;
            document.getElementById(`selectProdcutTextBoxReview`).addEventListener('input', e => {
                const query = e.target.value;
                if (waitTime !== null) {
                    clearTimeout(waitTime);
                }
                waitTime = setTimeout(() => {
                    waitTime = null;
                    searchForProducts(i, query);
                }, 1500);
            });

            settingsBody.querySelector(`#input_source-${i}`).addEventListener('change', e => {
                source[i] = e.target.value;
                mainContainer.setAttribute(productSourceAttr, source.join());
                if (e.target.value == 1) {
                    settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'table-row';
                } else {
                    settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'none';
                }

                isNote = false;
                for (let j = 0; j < 1 && !isNote; j++) {
                    if (settingsBody.querySelector(`#input_source-${j}`).value == 0) {
                        isNote = true;
                    }
                }
                settingsNote.style.display = isNote ? 'block' : 'none';
            });

        }

        settingsNote.style.display = isNote ? 'block' : 'none';

        // settingsBody.querySelector(`#input_description`).addEventListener('change', e => {
        //     for (let i = 0; i < count; i++) {
        //         if (e.target.checked) {
        //             mainContainer.querySelector(`.dynamic-products-description-${i}`).style.display = 'block';
        //         } else {
        //             mainContainer.querySelector(`.dynamic-products-description-${i}`).style.display = 'none';
        //         }
        //     }
        // });

        // settingsBody.querySelector(`#input_price`).addEventListener('change', e => {
        //     for (let i = 0; i < count; i++) {
        //         if (e.target.checked) {
        //             mainContainer.querySelector(`.dynamic-products-price-${i}`).style.display = 'inline-block';
        //         } else {
        //             mainContainer.querySelector(`.dynamic-products-price-${i}`).style.display = 'none';
        //         }
        //     }
        // });

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
            settingsBody = document.getElementById('submit-review-product-settings-content');
            settingsNote = document.getElementById('submit-review-product-settings-note');
            prepareSettings();
            document.getElementById('quick-settings-title').innerText = "Product Review Settings";
            _cb.showLeftSidePanel('submit-review-product-settings');
        }

        return ret;
    };

    window.addEventListener('click', (event) => {
        let node_id = event.target.id;
        let parent_id = event.target.parentNode ? event.target.parentNode.id : "";

        let skip = node_id.includes('selectedProductTitleContainer') || parent_id.includes('selectedProductTitleContainer')
            || node_id.includes('searchProductsContainerReview') || parent_id.includes('searchProductsContainerReview')
            || node_id.includes('product-items-container-review') || parent_id.includes('product-items-container-review');

        if (!skip) {
            const pps = document.querySelectorAll('#searchProductsContainerReview');
            Array.prototype.forEach.call(pps, p => {
                p.style.display = 'none';
            });
        }
    });

    let oldgetCC = _cb.opts.onClearingControls;
    _cb.opts.onClearingControls = function (e) {
        const pps = document.querySelectorAll('#searchProductsContainerReview');
        Array.prototype.forEach.call(pps, p => p.style.display = 'none');
        return oldgetCC.apply(this, arguments);
    }
})();