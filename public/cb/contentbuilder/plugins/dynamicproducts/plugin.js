(function () {
    const html = `
    <div id="dynamic-products-settings" style="display: flex;flex-direction: column;flex-grow: 2;justify-content: space-between;">
        <div id="dynamic-products-settings-content">
        </div>
        <div id="dynamic-products-settings-note" style="padding: 10px; background: #ffc107; text-align:center;">The actual content will be different but styles will be the same.</div>
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
    const mainContainerClass = 'dynamic-products';
    let mainContainer = null;
    let settingsBody = null;
    let settingsNote = null;
    const dataCountAttr = 'data-count';
    const productSourceAttr = 'product-source';
    const productIdAttr = 'product-id';
    const selectedProductText = 'selected-product-text';
    const dataBestSelling = 'data-best-selling';

    let currentProductSelected = 0;

    const updateProductDetails = (id, pid) => {
        console.log(pid);
        const product = products.items.find(p => p.id == id);
        if (product) {
            const data = JSON.parse(product.data);
            let ptTitle = mainContainer.getAttribute(selectedProductText) ? mainContainer.getAttribute(selectedProductText).split(','): [];
            ptTitle[pid] = data.title;
            mainContainer.setAttribute(selectedProductText, ptTitle.join(','));
            mainContainer.querySelector('.dynamic-products-image-' + pid).src = data.image.src;
            mainContainer.querySelector('.dynamic-products-title-' + pid).innerText = data.title;
            mainContainer.querySelector('.dynamic-products-price-' + pid).innerText = _cb.settings.storeShopCurrency + ' ' + data.variants[0].price;
            mainContainer.querySelector('.dynamic-products-description-' + pid).innerText = new DOMParser().parseFromString(data.body_html, "text/html").documentElement.querySelector('body').innerText.replace(/\n+/g, '\n');
            mainContainer.querySelector('.dynamic-products-button-' + pid).href = _cb.settings.storeDomain + "/products/" + data.handle;
        }
    }

    const fetchProductsList = (id, query) => {
        const url = "/ew_dynamic/select2_product" + (query === '' ? '' : '?q=' + query);
        fetch(url)
            .then(data => data.json())
            .then(data => {
                products = data;
                let html = '';
                products.items.forEach(p => {
                    html += `<div class="product-items" data-id="${p.id}">${p.text}</div>`;
                });
                document.getElementById('product-items-container').innerHTML = html;

                Array.prototype.forEach.call(document.querySelectorAll('.product-items'), p => {
                    p.addEventListener('click', e => {
                        const selectedProductTitle = document.getElementById(`selectedProductTitle-${id}`);
                        selectedProductTitle.setAttribute('data-id', e.target.getAttribute('data-id'));
                        selectedProductTitle.innerText = e.target.innerText;
                        document.getElementById('searchProductsContainer').style.display = 'none';
                        updateProductDetails(e.target.getAttribute('data-id'), id);
                    });
                });
            });
    }
    const fetchBestSelling = () => {
        const url = "/ew_dynamic/best_selling_products";
        fetch(url)
            .then(data => data.json())
            .then(data => {
                bestProducts = data.products.data;
                var count = mainContainer.getAttribute(dataCountAttr);
                for(i = 0; i < count; i++){
                    console.log(bestProducts.length)
                    if(bestProducts.length >= i){
                        //updateProductDetails(bestProducts[i].id, i);
                    }
                }
            });
    }

    const searchForProducts = (i, query = '') => {
        const index = i;
        const searchItemsContainer = document.getElementById('product-items-container');
        searchItemsContainer.innerHTML = `<div class="product-items">Searching for products...</div>`;
        fetchProductsList(index, query);
    }

    const prepareSettings = () => {
        const count = parseInt(mainContainer.getAttribute(dataCountAttr));
        const source = mainContainer.getAttribute(productSourceAttr).split(',');
        const product = mainContainer.getAttribute(productIdAttr).split(',');
        const bestSelling = parseInt(mainContainer.getAttribute(dataBestSelling));
        const selectedProdcutTitle = mainContainer.getAttribute(selectedProductText) ? mainContainer.getAttribute(selectedProductText).split(',') : [];
        let html = '<table style="width: 100%; table-layout: fixed;">';

        for (let i = 0; i < count; i++) {
            const at = source[i] == 0 ? `<option value="0" selected>From Automation Trigger</option>` : '<option value="0">From Automation Trigger</option>';
            const pi = source[i] == 1 ? `<option value="1" selected>From Product ID</option>` : '<option value="1">From Product ID</option>';
            
            html += `
            <tr style="display: none;"><td style="font-size: 14px;">Product Source ${i + 1}:</td>
                <td style="font-size: 14px;">
                    <select id="input_source-${i}">
                        ${at}
                        ${pi}
                    </select>
                </td>
            </tr>
            <tr class="select-product"><td style="font-size: 14px;">Select Product ${i + 1}:</td>
                <td style="font-size: 14px;">
                    <div id="selectedProductTitleContainer-${i}" style="justify-content: space-between; align-items: center; border: solid 1px rgb(196, 196, 196); padding: 3px;;">
                        <div id="selectedProductTitle-${i}" style="display:inline;padding-right:20px;padding: 15px 20px 15px 0;">`+ (selectedProdcutTitle[i] ? selectedProdcutTitle[i] : 'Select A Product') +`</div><div style="font-size: 18px;display:inline;padding: 15px 0;"><i class="icon ion-android-arrow-dropdown"></i></div>
                    </div>
                </td>
            </tr>
            <tr class="select-product">
                <td colspan="2"><div style="width: 100%; border-top: 1px solid rgb(199, 199, 199);"></div></td>
            </tr>
            `;
        }
        let desc = `<input type="checkbox" id="input_description" class="checkbox-switch-input"`;
        desc += mainContainer.querySelector(`.dynamic-products-description-0`).style.display == 'none' ? '>' : ' checked>';
        desc += `<label class="checkbox-switch" for="input_description">Toggle</label>`;
        html += `
            <tr><td style="font-size: 14px;">Include Description:</td>
                    <td style="display: flex;">
                        ${desc}
                    </td>
                </tr>
        `;
        let price = `<input type="checkbox" class="checkbox-switch-input" id="input_price"`;
        price += mainContainer.querySelector(`.dynamic-products-price-0`).style.display == 'none' ? '>' : ' checked>';
        price += `<label class="checkbox-switch" for="input_price">Toggle</label>`;
        html += `
            <tr><td style="font-size: 14px;">Include Price:</td>
                    <td style="display: flex;">
                        ${price}
                    </td>
                </tr>
        
        `;
        let best = `<input type="checkbox" class="checkbox-switch-input" id="input_best_selling"`;
        best += mainContainer.getAttribute('data-best-selling') == '1' ? 'checked>' : ' >';
        best += `<label class="checkbox-switch" for="input_best_selling">Toggle</label>`;
        html += `
            <tr><td style="font-size: 14px;">Best Selling:</td>
                    <td style="display: flex;">
                        ${best}
                    </td>
                </tr>
        </table>
        `;
        html += `
        <div id="searchProductsContainer"
            style="display: none; flex-direction: column; justify-content: flex-start; width: 80%; border: solid 1px rgb(196, 196, 196); padding: 10px;position: absolute;right: 14px;top: 178px;background: #fff;max-height: 200px;overflow: auto;">
            <input type="search" placeholder="Select a Product" id="selectProdcutTextBox">
            <div id="product-items-container">

            </div>
        </div>
        `;
        settingsBody.innerHTML = html;

        let isNote = false;
        for (let k = 0; k < count; k++) {
            const i = k;
            if (source[i] == 0) {
                settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.style.display = 'none';
                isNote = true;
            } else {
                if(bestSelling == 1){
                    settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.classList.add('greyed');
                }else{
                    settingsBody.querySelector(`#selectedProductTitleContainer-${i}`).parentNode.parentNode.classList.remove('greyed');
                }
                isNote = isNote;
            }

            document.getElementById(`selectedProductTitleContainer-${i}`).parentNode.addEventListener('click', e => {
                document.getElementById('searchProductsContainer').style.display = 'flex';
                const rect = document.getElementById(`selectedProductTitleContainer-${i}`).getBoundingClientRect();
                document.getElementById('searchProductsContainer').style.top = rect.bottom + 'px';

                currentProductSelected = i;
                searchForProducts(i);
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
                for (let j = 0; j < count && !isNote; j++) {
                    if (settingsBody.querySelector(`#input_source-${j}`).value == 0) {
                        isNote = true;
                    }
                }
                settingsNote.style.display = isNote ? 'block' : 'none';
            });

        }

        let waitTime = null;
        document.getElementById(`selectProdcutTextBox`).addEventListener('input', e => {
            const query = e.target.value;
            if (waitTime !== null) {
                clearTimeout(waitTime);
            }
            waitTime = setTimeout(() => {
                waitTime = null;
                searchForProducts(currentProductSelected, query);
            }, 1500);
        });

        settingsNote.style.display = isNote ? 'block' : 'none';

        settingsBody.querySelector(`#input_description`).addEventListener('change', e => {
            for (let i = 0; i < count; i++) {
                if (e.target.checked) {
                    mainContainer.querySelector(`.dynamic-products-description-${i}`).style.display = 'block';
                } else {
                    mainContainer.querySelector(`.dynamic-products-description-${i}`).style.display = 'none';
                }
            }
        });

        settingsBody.querySelector(`#input_price`).addEventListener('change', e => {
            for (let i = 0; i < count; i++) {
                if (e.target.checked) {
                    mainContainer.querySelector(`.dynamic-products-price-${i}`).style.display = 'inline-block';
                } else {
                    mainContainer.querySelector(`.dynamic-products-price-${i}`).style.display = 'none';
                }
            }
        });
        let fetchTryCount = 0;
        function fetchBestSellingPopulate(){
            fetchTryCount++;
            setTimeout(() => {
                if(product.length == 1 && fetchTryCount < 10 ){
                    if(products.items[0].id = "4"){
                        fetchBestSellingPopulate()
                    }else{
                        fetchBestSelling();
                    }
                    
                }else{
                    fetchBestSelling();
                }
            }, 100);
        }
        settingsBody.querySelector(`#input_best_selling`).addEventListener('change', e => {
                var dBs = true;
                if (e.target.checked) {
                  mainContainer.setAttribute(dataBestSelling, '1');
                  searchForProducts(1);
                  fetchBestSellingPopulate();
                } else {
                    mainContainer.setAttribute(dataBestSelling, '0');
                    dBs = false;
                }
                var bs = document.getElementsByClassName('select-product');
                for(var i = 0; i < bs.length; i++){
                    if(dBs){
                        
                        document.getElementsByClassName('select-product')[i].classList.add('greyed')
                    }else{
                        document.getElementsByClassName('select-product')[i].classList.remove('greyed')
                    }
                    
                }
        });

    }

    //Extend onContentClick
    var oldget = _cb.opts.onContentClick;
    _cb.opts.onContentClick = function (e) {
        let elm = e.target;
        var ret = oldget.apply(this, arguments);

        if (elm.className.includes('dynamic-products-button-')) {
            return ret;
        }
        while (elm && !elm.classList?.contains(mainContainerClass)) {
            elm = elm.parentNode;
        }

        if (elm) {
            mainContainer = elm;
            settingsBody = document.getElementById('dynamic-products-settings-content');
            settingsNote = document.getElementById('dynamic-products-settings-note');
            prepareSettings();
            document.getElementById('quick-settings-title').innerText = "Product(s) Settings";
            _cb.showLeftSidePanel('dynamic-products-settings');
        }

        return ret;
    };

    window.addEventListener('click', (event) => {
        if (event.target.parentNode) {
            const id = event.target.parentNode.id ?? "";
            const id2 = event.target.parentNode.parentNode.id ?? "";
            const el = event.target.parentNode;
            var bselm = 0;
            if(mainContainer){
                bselm = mainContainer.getAttribute(dataBestSelling) ?? 0;
            }
            
            if (bselm == 0 && (id.includes('selectedProductTitleContainer') || id2.includes('selectedProductTitleContainer') || id.includes('searchProductsContainer') || id.includes('product-items-container') || el.classList.contains('selecter'))) {
                return;
            } else {
                const pps = document.querySelectorAll('#searchProductsContainer');
                Array.prototype.forEach.call(pps, p => {
                    p.style.display = 'none';
                });
            }
        }
    });

    var oldgetCC = _cb.opts.onClearingControls;
    _cb.opts.onClearingControls = function (e) {
        const pps = document.querySelectorAll('#searchProductsContainer');
        Array.prototype.forEach.call(pps, p => p.style.display = 'none');
        var ret = oldgetCC.apply(this, arguments);
        return ret;
    }
})();