<?php
/** @var Acelle\Model\Automation2 $automation */
/** @var Acelle\Model\Email $email */
?>
        <!DOCTYPE HTML>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>{{ $automation->name }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="description" content="">
    <link rel="shortcut icon" href="#"/>
    <link href="/cb/assets/minimalist-blocks/content.css?v=1.4c" rel="stylesheet" type="text/css"/>
    <link href="/cb/contentbuilder/contentbuilder.css" rel="stylesheet" type="text/css"/>
    <link href="/cb/assets/custom.css?v=1.4a" rel="stylesheet" type="text/css"/>
</head>
<body class="myautohidescroll1">

{!! $email->getContent() !!}

<!--<div class="is-tool" style="position:fixed;width:400px;height:50px;border:none;top:auto;bottom:30px;left:30px;right:auto;display:flex">
    <button id="btnDestroy" class="classic" style="height:50px;">Destroy</button>
    <button id="btnInit" class="classic" style="height:50px;">Init</button>
    <button id="btnConfig" class="classic" style="height:50px;">Config</button>
    <button id="btnGetHtml" class="classic" style="height:50px;">SAVE</button>
</div>-->

<div class="is-rte-tool"
     style="position:fixed;top:30px;left:30px;display:flex"
>
    <!-- <div style="display: flex; gap: 5px;">
        <button id="btnGetHtml" class="classic btn btn-secondary mt-20">Save</button>
        <button id="btnOpenCloseHtml" class="classic btn btn-secondary mt-20 white-cap-btn">Save & Close</button>
        <button id="btnClose" class="classic">Close</button>
    </div> -->
</div>
<div style="position:fixed;bottom:30px;right:320px;display:none">
    <div style="display:flex">
        <label>
            Background:
            <input type="color" id="bg_color_selector"/>
        </label>
    </div>
</div>

<script src="/cb/contentbuilder/contentbuilder.js?v=1.1" type="text/javascript"></script>
<script src="/cb_snippets" type="text/javascript"></script>
<script src="/assets/js/core/libraries/jquery.min.js" type="text/javascript"></script>
<script>
    let win = null;
    let builder = new ContentBuilder({
        assetPath: '/cb/assets/',
        builderMode: '',
        clearPreferences: true, //reset settings on load
        columnTool: true,
        container: '.container',
        elementHighlight: true,
        elementTool: true,
        fontAssetPath: '/cb/assets/fonts/',
        outlineMode: '',
        outlineStyle: '',
        rowcolOutline: true,
        rowTool: 'right',
        snippetsSidebarDisplay: 'always',
        snippetAddTool: true,
        snippetOpen: true,
        snippetData: '/cb/assets/minimalist-blocks/snippetlist.html',
        snippetPath: '/cb/assets/minimalist-blocks/',
        defaultSnippetCategory: 1001,
        isNewSnippetStyle: true,
        snippetCategories: [
            [101, "Text", "HeaderMajor"],
            [102, "Images", "ImagesMajor"],
            [119, "Buttons", "BuyButtonMajor"],
            [1001, "Products", "ProductsMajor"],
            [1002, "Orders", "OrdersMajor"],
            [1012, "Cart", "AbandonedCartMajor"],
            [1003, "Chats", "ChatMajor"],
            [1013, "Reviews", "FavoriteMajor"],
            [1, "Logo", "LogoBlockMajor"],
            [1101, "Social Media", "SocialPostMajor"],
            [103, "Profile", "ProfileMajor"],
            [106, "Process", "AutomationMajor"],
            // [120, "Basic", "ion-alert"],
            // [118, "Blog", "ion-at"],
            // [105, "Features", "ion-heart"],
            // [107, "Pricing", "ion-ios-pricetag"],
            // [108, "Skills", "ion-ribbon-a"],
            // [111, "Partners", "ion-android-contacts"],
            // [112, "As Featured On", "ion-wand"],
            // [114, "Coming Soon", "ion-speakerphone"],
            // [115, "Help, FAQ", "ion-help"],
        ],
        emailSnippetCategories: [
            [1001, "Products"],
            [1002, "Orders"],
            [1003, "Chats"],
            [1101, "Social Media"],
            [120, "Basic"],
            [118, "Blog"],
            [101, "Headline"],
            [119, "Buttons"],
            [102, "Images"],
            [103, "Profile"],
            [105, "Features"],
            [106, "Process"],
            [107, "Pricing"],
            [108, "Skills"],
            [111, "Partners"],
            [112, "As Featured On"],
            [114, "Coming Soon"],
            [115, "Help, FAQ"],
        ],
        defaultEmailSnippetCategory: 1001,
        toolbar: 'top',
        toolbarDisplay: 'auto',
        toolStyle: '',
        uploadAssetUrl: '<?= action([\Acelle\Http\Controllers\Automation2Controller::class, 'templateAsset'], ['uid' => $automation->uid, 'email_uid' => $email->uid])?>',
        ewTagsSource: '<?= action([\Acelle\Http\Controllers\Automation2Controller::class, 'tags'], ['uid' => $automation->uid])?>',
        pixabayAPIKey: '13394617-3e495505aaf754d5d0756fc91',
        unsplashAPIKey: 'cS31WjrFCX70z7TOcTvladCe8kDHcWzMtR-NZhCE3m8',
        emailwishImagesLink: '<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/images") ?>',
        linksAPI: '<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/query") ?>',
        linksAPIBlogs: '<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/blogs?query=") ?>',
        linksAPIPages: '<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/pages?query=") ?>',
        sendEmailAPI: "<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/send_email") ?>",
        discountCodesAPI: "<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/discount_codes") ?>",
        select2Products: "<?= \Illuminate\Support\Facades\URL::asset("/ew_dynamic/select2_product") ?>",
        storeDomain: '<?= $automation->customer ? $automation->customer->shopify_shop->primary_domain : ''?>',
        storeTheme: '<?= json_encode($automation->customer ? $automation->customer->shopify_shop->theme : []) ?>',
        storeShopCurrency: '<?= $automation->customer ? $automation->customer->shopify_shop->primary_currency: 'USD' ?>',
        //See readme.txt for more
    });

    /*const btnInit = document.querySelector('#btnInit');
    btnInit.addEventListener('click', function(){
        if(builder.builderStuff) return;

        builder = new ContentBuilder({ // Init
            container: '.container'
        });
    });

    const btnDestroy = document.querySelector('#btnDestroy');
    btnDestroy.addEventListener('click', function(){
        builder.destroy(); // Destroy
    });
    const btnConfig = document.querySelector('#btnConfig');
    btnConfig.addEventListener('click', function(){
        if(!builder.builderStuff) {
            alert('Builder is destroyed. Please click the Init button');
            return;
        }

        builder.viewConfig(); // open Configuration
    });*/

    function getHtml() {
        const bgColor = document.querySelector('.container_bg').style.backgroundColor;
        return '<div class="container_bg" style="background-color:' + bgColor + '"><div class="container">' + builder.html() + '</div></div>';
    }
    const process = () => {
    const btnGetHtml = document.querySelector('#btnGetHtml');
    if(!btnGetHtml) {
        setTimeout(process, 0);
        return;
    }
    btnGetHtml.addEventListener('click', function () {
        this.classList.add("slim-spinner")
        if (!builder.builderStuff) {
            alert('Builder is destroyed. Please click the Init button');
            return;
        }

        const saveURL = '{{ action('Automation2Controller@templateEdit', ['uid' =>$automation->uid, 'email_uid' => $email->uid]) }}';
        const csrf_token = "{{ csrf_token() }}";
        
        builder.opts.onBeforeSave();
        $.post(saveURL,
            {
                _token: csrf_token,
                content: getHtml(),
                version: "alpha"
            },
            function (data, status) {
                if (status === "success") {
                    btnGetHtml.classList.remove("slim-spinner")
                    window.ShowToast('The email saved successfully...');
                    builder.opts.onAfterSave();
                } else {
                    alert("Failed to save template! Please try again.")
                }
            });
    });

    const btnClose = document.querySelector('#btnClose');
    btnClose.addEventListener('click', function () {
        builder.util.closingBuilder(isClose=> {
            if(isClose) {
                if (window.frameElement) {
                    window.frameElement.parentNode.parentNode.removeChild(window.frameElement.parentNode)
                }
                else {
                    window.history.back();
                }
            }
        });
    });
        const btnChangeBgColor = document.querySelector('#backgroundColorChange-button');
        if (btnChangeBgColor) {
            console.log(builder);
            btnChangeBgColor.addEventListener('click', function () {
                builder.colorPicker.setColor(document.querySelector('.container_bg').style.backgroundColor);
                builder.colorPicker.open(clr => {
                    const containerBg = document.querySelector('.container_bg')
                    if (containerBg)
                        containerBg.style.backgroundColor = clr;
                }, null, btnChangeBgColor);
            });
        }
    };
    setTimeout(process, 0);

    const containerBg = document.querySelector('.container_bg')
    const bgColorSelector = document.querySelector('#bg_color_selector')
    bgColorSelector && bgColorSelector.addEventListener('input', function () {
        if (containerBg)
            containerBg.style.backgroundColor = bgColorSelector.value;
    });
    const rgb = containerBg.style.backgroundColor
    bgColorSelector.value = '#' + rgb.substr(4, rgb.indexOf(')') - 4).split(',').map((color) => parseInt(color).toString(16)).join('');
</script>

</body>
</html>