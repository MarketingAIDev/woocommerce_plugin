<?php
function encodeURIComponent($str)
{
    $revert = array('%21' => '!', '%2A' => '*', '%27' => "'", '%28' => '(', '%29' => ')');
    return strtr(rawurlencode($str), $revert);
}

function _tabs($n)
{
    $html = '';
    for ($i = 1; $i <= $n; $i++) {
        $html .= '\t';
    }
    return '\n' . $html;
}

/** @var \Acelle\Model\Customer $selected_customer */
$signature = $selected_customer->signature;
$sig_full_name = $signature ? $signature->full_name : "";
$sig_designation = $signature ? $signature->designation : "";
$sig_website = $signature ? $signature->website : "";
$sig_phone = $signature ? $signature->phone : "";
$sig_logo_url = $signature && $signature->logo_url ? $signature->logo_url : "/cb/assets/minimalist-blocks/images/logoplaceholder.png";
$sig_facebook = $signature ? $signature->facebook : "";
$sig_linkedin = $signature ? $signature->linkedin : "";
$sig_twitter = $signature ? $signature->twitter : "";
$sig_instagram = $signature ? $signature->instagram : "";
$sig_skype = $signature ? $signature->skype : "";
$sig_yt = $signature ? $signature->youtube : "";
$sig_tt = $signature ? $signature->tiktok : "";
$sig_pinterest = $signature ? $signature->pinterest : "";
$sig_email = "mailto:".$selected_customer->email();

$products = [
    /* PRODUCTS */
    [
        'thumbnail' => 'preview/ew_dynamic_product.png',
        'category' => '1001',
        'html' =>
            '\n<table class="dynamic-products" data-count="1" product-source="1" product-id="1" style="text-align: center;background-color:#ffffff;color:#000000; width: 100%;">' .
            '\n	<tr style="padding: 0;">' .
            '\n		<td style="padding: 0">' .
            '\n			<img class="dynamic-products-image-0 no-image-edit" contenteditable="false"' .
            '\n				src="/cb/assets/minimalist-blocks/images/placeholder-images-product-6_large.png"' .
            '\n				alt="" style="width: 100%;">' .
            '\n		</td>' .
            '\n	</tr>' .
            '\n	<tr style="padding: 0">' .
            '\n		<td style="padding: 0">' .
            '\n			<h3 contenteditable="false" class="dynamic-products-title-0" style="max-width: 100%; font-size: 21px; line-height: 23px; font-weight: 600;">sample product' .
            '\n         </h3>' .
            '\n		</td>' .
            '\n	</tr>' .
            '\n	<tr style="padding: 0">' .
            '\n		<td style="padding: 0">' .
            '\n			<h3 contenteditable="false" class="dynamic-products-price-0" contenteditable="false" style="font-weight: 300; max-width: 100%; font-size: 16px; line-height: 27px;">USD 109.00' .
            '\n         </h3>' .
            '\n		</td>' .
            '\n	</tr>' .
            '\n	<tr style="padding: 0">' .
            '\n		<td style="padding: 0">' .
            '\n			<p class="dynamic-products-description-0" contenteditable="false" style="display: none; max-width: 100%; margin-top: 0px; margin-bottom: 30px; font-size: 12px; line-height: 18px;">Desdsd</p>' .
            '\n		</td>' .
            '\n	</tr>' .
            '\n	<tr style="padding: 0">' .
            '\n		<td style="padding: 0">' .
            '\n			<a contenteditable="false"' .
            '\n				class="dynamic-products-button-0 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link"' .
            '\n				href="#" style="display: inline-block; text-decoration: none; transition: all 0.16s ease 0s; border-style: solid; cursor: pointer; background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 9px 33px; line-height: 21px; text-transform: uppercase; font-weight: 600; font-size: 12px; letter-spacing: 3px; max-width: 100%;">Buy Now</a>' .
            '\n		</td>' .
            '\n	</tr>' .
            '\n</table>'
    ],
    [
        'thumbnail' => 'preview/products-03.jpg',
        'category' => '1001',
        'html' =>
        '\n<div class="row clearfix" style="text-align: center;">' .
        '\n	<div class="column full" style="text-align: center;">' .
        '\n		<h1 style="font-weight: 600; max-width: 100%; font-size: 27px; line-height: 37px; padding: 100px 16px 0px;">OUR PRODUCTS</h1>' .
        '\n	</div>' .
        '\n</div>' .
        '\n<div class="dynamic-products row clearfix" data-count="3" product-source="1,1,1" product-id="1,1,1" style="text-align: center;">' .
        '\n	<div class="column third">' .
        '\n		<img class="dynamic-products-image-0 no-image-edit" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-5_large.png" alt="" loading="lazy">' .
        '\n		<h5 class="dynamic-products-title-0" style="font-weight: 600; max-width: 100%; font-size: 18px; line-height: 25px; letter-spacing: 3px; margin-top: 0px;" contenteditable="false">Product 1</h5>' .
        '\n		<h6 class="dynamic-products-price-0" contenteditable="false" style="font-weight: 400; color: rgb(136, 136, 136); max-width: 100%; font-size: 15px; line-height: 21px; letter-spacing: 3px;">USD 10.00</h6>' .
        '\n		<p class="dynamic-products-description-0" contenteditable="false" style="display: none; max-width: 100%; margin-top: 0px; margin-bottom: 35px; font-size: 11px; line-height: 18px;">Product 1 description</p>' .
        '\n		<a contenteditable="false" class="dynamic-products-button-0 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" ' .
        '\n				href="#" style="display: inline-block; text-decoration: none; transition: all 0.16s ease 0s; border-style: solid; cursor: pointer; background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 10px 29px; line-height: 18px; text-transform: uppercase; font-weight: 600; font-size: 13px; letter-spacing: 3px; max-width: 100%; margin-top: 0px; margin-bottom: 25px;">Buy Now</a>' .
        '\n	</div>' .
        '\n	<div class="column third">' .
        '\n		<img class="dynamic-products-image-1 no-image-edit" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-4_large.png" alt="" loading="lazy">' .
        '\n		<h5 class="dynamic-products-title-1" style="font-weight: 600; max-width: 100%; font-size: 18px; line-height: 25px; letter-spacing: 3px; margin-top: 0px;" contenteditable="false">Product 2</h5>' .
        '\n		<h6 class="dynamic-products-price-1" contenteditable="false" style="font-weight: 400; color: rgb(136, 136, 136); max-width: 100%; font-size: 15px; line-height: 21px; letter-spacing: 3px;">USD 20.00</h6>' .
        '\n		<p class="dynamic-products-description-1" contenteditable="false" style="display: none; max-width: 100%; margin-top: 0px; margin-bottom: 35px; font-size: 11px; line-height: 18px;">Product 2 description</p>' .
        '\n		<a contenteditable="false" class="dynamic-products-button-1 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" ' .
        '\n				href="#" style="display: inline-block; text-decoration: none; transition: all 0.16s ease 0s; border-style: solid; cursor: pointer; background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 10px 29px; line-height: 18px; text-transform: uppercase; font-weight: 600; font-size: 13px; letter-spacing: 3px; max-width: 100%; margin-top: 0px; margin-bottom: 25px;">Buy Now</a>' .
        '\n	</div>' .
        '\n	<div class="column third">' .
        '\n		<img class="dynamic-products-image-2 no-image-edit" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-2_large.png" alt="" loading="lazy">' .
        '\n		<h5 class="dynamic-products-title-2" style="font-weight: 600; max-width: 100%; font-size: 18px; line-height: 25px; letter-spacing: 3px; margin-top: 0px;" contenteditable="false">Product 3</h5>' .
        '\n		<h6 class="dynamic-products-price-2" contenteditable="false" style="font-weight: 400; color: rgb(136, 136, 136); max-width: 100%; font-size: 15px; line-height: 21px; letter-spacing: 3px;">USD 30.00</h6>' .
        '\n		<p class="dynamic-products-description-2" contenteditable="false" style="display: none; max-width: 100%; margin-top: 0px; margin-bottom: 35px; font-size: 11px; line-height: 18px;">Product 3 description</p>' .
        '\n		<a contenteditable="false" class="dynamic-products-button-2 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" ' .
        '\n				href="#" style="display: inline-block; text-decoration: none; transition: all 0.16s ease 0s; border-style: solid; cursor: pointer; background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 10px 29px; line-height: 18px; text-transform: uppercase; font-weight: 600; font-size: 13px; letter-spacing: 3px; max-width: 100%; margin-top: 0px; margin-bottom: 25px;">Buy Now</a>' .
        '\n	</div>' .
        '\n</div>'
    ],
    // [
    //     'thumbnail' => 'preview/products-03.jpg',
    //     'category' => '1001',
    //     'html' =>
    //     '\n<table class="dynamic-products" data-count="2" product-source="1,1" product-id="1,1" style="background-color: rgb(255, 255, 255); color: rgb(0, 0, 0); width: 100%; max-width: 100%;">' .
    //     '\n    <tbody style="max-width: 100%;">' .
    //     '\n        <tr style="padding: 0px; max-width: 100%;">' .
    //     '\n            <td width="50%" style="padding: 0px; max-width: 100%;vertical-align: baseline;">' .
    //     '\n				<h5 class="dynamic-products-title-0 size-28" style="max-width: 100%;">sample product</h5>' .
    //     '\n				<p class="dynamic-products-price-0" style="font-weight: 600; max-width: 100%;">USD 109.00</p>' .
    //     '\n                <p class="dynamic-products-description-0" style="max-width: 100%; display: none;">Desdsd</p>' .
    //     '\n                <a class="dynamic-products-button-0 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" href="#" style="display:inline-block;text-decoration:none;transition: all 0.16s ease;border-style:solid;cursor:pointer;background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 13px 28px; line-height: 21px; text-transform: uppercase; font-weight: 400; font-size: 14px; letter-spacing: 3px;">Buy Now</a>' .
    //     '\n            </td>' .
    //     '\n            <td style="padding: 0px; max-width: 100%;padding-left: 20px;">' .
    //     '\n                <img class="dynamic-products-image-0 no-image-edit" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-3_large.png" alt="" style="width: 100%; max-width: 100%;">' .
    //     '\n            </td>' .
    //     '\n        </tr>' .
    //     '\n        <tr style="padding: 0px; max-width: 100%;">' .
    //     '\n            <td style="padding: 0px; max-width: 100%;">' .
    //     '\n                <img class="dynamic-products-image-1 no-image-edit" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-5_large.png" alt="" style="width: 100%; max-width: 100%;">' .
    //     '\n            </td>' .
    //     '\n            <td style="padding: 0px; max-width: 100%;vertical-align: baseline;padding-left: 20px;">' .
    //     '\n                <h5 class="dynamic-products-title-1 size-28" style="max-width: 100%;">sample product</h5>' .
    //     '\n				<p class="dynamic-products-price-1" style="font-weight: 600; max-width: 100%;">USD 109.00</p>' .
    //     '\n                <p class="dynamic-products-description-1" style="max-width: 100%; display: none;">Desdsd</p>' .
    //     '\n                <a class="dynamic-products-button-1 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" href="#" style="display:inline-block;text-decoration:none;transition: all 0.16s ease;border-style:solid;cursor:pointer;background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 13px 28px; line-height: 21px; text-transform: uppercase; font-weight: 400; font-size: 14px; letter-spacing: 3px;">Buy Now</a>' .
    //     '\n            </td>' .
    //     '\n        </tr>' .
    //     '\n    </tbody>' .
    //     '\n</table>'
    // ],
    [
        'thumbnail' => 'preview/products-01.png',
        'category' => '1001',
        'html' =>
        '\n<table class="dynamic-products" data-count="2" product-source="1,1" product-id="1,1" style="background-color:#ffffff;color:#000000; width: 100%;text-align: center;">' .
        '\n	<tr>' .
        '\n		<td colspan="2"><h1 class="size-38" style="font-weight: 400;">OUR PRODUCTS</h1></td>' .
        '\n	</tr>' .
        '\n	<tr style="padding: 0">' .
        '\n		<td width="50%" style="padding: 0">' .
        '\n			<img class="dynamic-products-image-0 no-image-edit" contenteditable="false" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-6_large.png" alt="" style="width: 100%;">' .
        '\n		</td>' .
        '\n		<td style="padding: 0">' .
        '\n			<img class="dynamic-products-image-1 no-image-edit" contenteditable="false" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-4_large.png" alt="" style="width: 100%;">' .
        '\n		</td>' .
        '\n	</tr>' .
        '\n	<tr style="padding: 0">' .
        '\n		<td style="padding: 0">' .
        '\n			<h5 contenteditable="false" class="dynamic-products-title-0">sample product</h5>' .
        '\n		</td>' .
        '\n		<td style="padding: 0">' .
        '\n			<h5 contenteditable="false" class="dynamic-products-title-1">sample product</h5>' .
        '\n		</td>' .
        '\n	</tr>' .
        '\n	<tr style="padding: 0">' .
        '\n		<td style="padding: 0">' .
        '\n			<h5 contenteditable="false" class="dynamic-products-price-0" contenteditable="false" style="font-weight: 600;">USD 109.00</h5>' .
        '\n		</td>' .
        '\n		<td style="padding: 0">' .
        '\n			<h5 contenteditable="false" class="dynamic-products-price-1" contenteditable="false" style="font-weight: 600;">USD 109.00</h5>' .
        '\n		</td>' .
        '\n	</tr>' .
        '\n	<tr style="padding: 0">' .
        '\n		<td style="padding: 0">' .
        '\n			<p class="dynamic-products-description-0" contenteditable="false" style="display: none;">Desdsd</p>' .
        '\n		</td>' .
        '\n		<td style="padding: 0">' .
        '\n			<p class="dynamic-products-description-1" contenteditable="false" style="display: none;">Desdsd</p>' .
        '\n		</td>' .
        '\n	</tr>' .
        '\n	<tr style="padding: 0">' .
        '\n		<td style="padding: 0">' .
        '\n			<a contenteditable="false" class="dynamic-products-button-0 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" href="#" style="display:inline-block;text-decoration:none;transition: all 0.16s ease;border-style:solid;cursor:pointer;background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 13px 28px; line-height: 21px; text-transform: uppercase; font-weight: 400; font-size: 14px; letter-spacing: 3px;">Buy Now</a>' .
        '\n		</td>' .
        '\n		<td style="padding: 0">' .
        '\n			<a contenteditable="false" class="dynamic-products-button-1 is-btn is-btn-small is-btn-ghost1 is-upper edit readonly-link" href="#" style="display:inline-block;text-decoration:none;transition: all 0.16s ease;border-style:solid;cursor:pointer;background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 13px 28px; line-height: 21px; text-transform: uppercase; font-weight: 400; font-size: 14px; letter-spacing: 3px;">Buy Now</a>' .
        '\n		</td>' .
        '\n	</tr>' .
        '\n</table>'
    ],
    [
        'thumbnail' => 'preview/products-19.png',
        'category' => '1001',
        'html' =>
        '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="letter-spacing: 3px; text-align: center;">SERVICES WE PROVIDE</h1>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="list">' .
                '<img src="/cb/assets/icons/ion-checkmark.png">' .
                '<h3>Creative Designs</h3>' .
                '<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
                '<img src="/cb/assets/icons/ion-checkmark.png">' .
                '<h3>Web Development</h3>' .
                '<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="list">' .
                '<img src="/cb/assets/icons/ion-checkmark.png">' .
                '<h3>Brand Building&nbsp;</h3>' .
                '<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
                '<img src="/cb/assets/icons/ion-checkmark.png">' .
                '<h3>Friendly Support</h3>' .
                '<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
        '</div>'
    ],
    [
        'thumbnail' => 'preview/products-18.png',
        'category' => '1001',
        'html' =>
        '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="text-align: center; letter-spacing: 3px;">OUR SERVICES</h1>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-monitor-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service One</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service Two</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service Three</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
        '</div>' .
        '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-compose-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service Four</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-world-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service Five</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-ios-calendar-outline.png" style="width: 48px;">' .
            '<h4 style="letter-spacing: 1px;">Service Six</h4>' .
            '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
        '</div>'
    ],
    [
        'thumbnail' => 'preview/products-08.png',
        'category' => '1001',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite is-upper center">SERVICES WE PROVIDE</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<p class="size-64 is-title1-64 is-title-bold">1</p>' .
            '\n<h3 class="size-21" style="line-height: 1.5">MODERN IDEAS</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<p class="size-64 is-title1-64 is-title-bold">2</p>' .
            '\n<h3 class="size-21" style="line-height: 1.5">WEB DEVELOPMENT</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<p class="size-64 is-title1-64 is-title-bold">3</p>' .
            '\n<h3 class="size-21" style="line-height: 1.5">SUPERIOR SUPPORTS</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],
    [
        'thumbnail' => 'preview/products-20.png',
        'category' => '1001',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 3px;">SERVICES WE OFFER</h1>' .
            '\n<p style="border-bottom: 2.5px solid #333; width: 70px; display: inline-block; margin-top: 25px"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column center third">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-monitor-outline.png" style="width: 32px; margin: 0;margin-top: 12px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-24" style="margin-top:1.5em">Creative Design</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column center third">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-compose-outline.png" style="width: 32px; margin: 0;margin-top: 12px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-24" style="margin-top:1.5em">Web Development</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column center third">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 32px; margin: 0;margin-top: 12px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-24" style="margin-top:1.5em">24/7 Supports</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ]
];

$orders = [
    [
        'thumbnail' => 'preview/order.png',
        'category' => '1002',
        'html' =>
        '\n<div class="abandoned-shopping-cart">' .
        '\n	<table class="cart-items">' .
        '\n		<thead>' .
        '\n			<tr>' .
        '\n				<th width="70%">Item</th>' .
        '\n				<th width="10%">Qty</th>' .
        '\n				<th width="20%">Price</th>' .
        '\n			</tr>' .
        '\n		</thead>' .
        '\n		<tbody class="abandoned-body-starts" data-isdescription="1" abandoned-body-starts-r1="" abandoned-body-starts-r1-c1="" abandoned-body-starts-r1-c1-item="" abandoned-body-starts-r1-c1-item-table="" abandoned-body-starts-r1-c1-item-r1="" abandoned-body-starts-r1-c1-item-r1-c1="" abandoned-body-starts-r1-c1-item-r1-c1-img="" abandoned-body-starts-r1-c1-item-r1-c2="" abandoned-body-starts-r1-c1-item-r1-c2-title="" abandoned-body-starts-r1-c1-item-r1-c2-info="" abandoned-body-starts-r1-c1-item-r1-c2-price="" abandoned-body-starts-r1-c2="" abandoned-body-starts-r1-c2-qty="" abandoned-body-starts-r1-c3="" abandoned-body-starts-r1-c3-price="">' .
        '\n			<tr class="abandoned-body-starts-r1">' .
        '\n				<td class="abandoned-body-starts-r1-c1">' .
        '\n					<div class="item abandoned-body-starts-r1-c1-item">' .
        '\n						<table class="abandoned-body-starts-r1-c1-item-table">' .
        '\n							<tr class="abandoned-body-starts-r1-c1-item-r1">' .
        '\n								<td class="abandoned-body-starts-r1-c1-item-r1-c1">' .
        '\n									<img class="abandoned-body-starts-r1-c1-item-r1-c1-img" src="/cb/assets/minimalist-blocks/images/placeholder.png" width="100px">' .
        '\n								</td>' .
        '\n								<td class="mobile-info abandoned-body-starts-r1-c1-item-r1-c2">' .
        '\n									<div class="item-title abandoned-body-starts-r1-c1-item-r1-c2-title">Title</div>' .
        '\n									<div class="item-info abandoned-body-starts-r1-c1-item-r1-c2-info">Info</div>' .
        '\n									<div class="mobile-qty-price abandoned-body-starts-r1-c1-item-r1-c2-price">1 x $1.00</div>' .
        '\n								</td>' .
        '\n							</tr>' .
        '\n						</table>' .
        '\n					</div>' .
        '\n				</td>' .
        '\n				<td class="abandoned-body-starts-r1-c2">' .
        '\n					<div class="qty abandoned-body-starts-r1-c2-qty">1</div>' .
        '\n				</td>' .
        '\n				<td class="abandoned-body-starts-r1-c3">' .
        '\n					<div class="price abandoned-body-starts-r1-c3-price">$1.00</div>' .
        '\n				</td>' .
        '\n			</tr>' .
        '\n		</tbody>' .
        '\n	</table>' .
        '\n	<div class="footer">' .
        '\n		<div>Subtotal: <span contenteditable="false">$1.00</span></div>' .
        '\n		<div>Tax: <span contenteditable="false">$1.00</span></div>' .
        '\n		<div>Shipping: <span contenteditable="false">$1.00</span></div>' .
        '\n		<div class="total">Total: <span contenteditable="false">$1.00</span></div>' .
        '\n	</div>' .
        '\n</div>'
    ]
];

$cart = [
    [
        'thumbnail' => 'preview/cart.png',
        'category' => '1012',
        'html' =>
        '\n<div class="abandoned-shopping-cart">' .
        '\n	<table class="cart-items">' .
        '\n		<thead>' .
        '\n			<tr>' .
        '\n				<th width="70%">Item</th>' .
        '\n				<th width="10%">Qty</th>' .
        '\n				<th width="20%">Price</th>' .
        '\n			</tr>' .
        '\n		</thead>' .
        '\n		<tbody class="abandoned-body-starts" data-isdescription="1" abandoned-body-starts-r1="" abandoned-body-starts-r1-c1="" abandoned-body-starts-r1-c1-item="" abandoned-body-starts-r1-c1-item-table="" abandoned-body-starts-r1-c1-item-r1="" abandoned-body-starts-r1-c1-item-r1-c1="" abandoned-body-starts-r1-c1-item-r1-c1-img="" abandoned-body-starts-r1-c1-item-r1-c2="" abandoned-body-starts-r1-c1-item-r1-c2-title="" abandoned-body-starts-r1-c1-item-r1-c2-info="" abandoned-body-starts-r1-c1-item-r1-c2-price="" abandoned-body-starts-r1-c2="" abandoned-body-starts-r1-c2-qty="" abandoned-body-starts-r1-c3="" abandoned-body-starts-r1-c3-price="">' .
        '\n			<tr class="abandoned-body-starts-r1">' .
        '\n				<td class="abandoned-body-starts-r1-c1">' .
        '\n					<div class="item abandoned-body-starts-r1-c1-item">' .
        '\n						<table class="abandoned-body-starts-r1-c1-item-table">' .
        '\n							<tr class="abandoned-body-starts-r1-c1-item-r1">' .
        '\n								<td class="abandoned-body-starts-r1-c1-item-r1-c1">' .
        '\n									<img class="abandoned-body-starts-r1-c1-item-r1-c1-img" src="/cb/assets/minimalist-blocks/images/placeholder.png" width="100px">' .
        '\n								</td>' .
        '\n								<td class="mobile-info abandoned-body-starts-r1-c1-item-r1-c2">' .
        '\n									<div class="item-title abandoned-body-starts-r1-c1-item-r1-c2-title">Title</div>' .
        '\n									<div class="item-info abandoned-body-starts-r1-c1-item-r1-c2-info">Info</div>' .
        '\n									<div class="mobile-qty-price abandoned-body-starts-r1-c1-item-r1-c2-price">1 x $1.00</div>' .
        '\n								</td>' .
        '\n							</tr>' .
        '\n						</table>' .
        '\n					</div>' .
        '\n				</td>' .
        '\n				<td class="abandoned-body-starts-r1-c2">' .
        '\n					<div class="qty abandoned-body-starts-r1-c2-qty">1</div>' .
        '\n				</td>' .
        '\n				<td class="abandoned-body-starts-r1-c3">' .
        '\n					<div class="price abandoned-body-starts-r1-c3-price">$1.00</div>' .
        '\n				</td>' .
        '\n			</tr>' .
        '\n		</tbody>' .
        '\n	</table>' .
        '\n	<div class="gotocart">' .
        '\n		<a class="gotocart-button" href="{ABANDONED_CHECKOUT_LINK}"' .
        '\n			style="display:inline-block;text-decoration:none;transition: all 0.16s ease;border-style:solid;cursor:pointer;background-color: rgb(23, 23, 23); color: rgb(255, 255, 255); border-color: rgb(23, 23, 23); border-width: 2px; border-radius: 0px; padding: 13px 28px; line-height: 21px; text-transform: uppercase; font-weight: 400; font-size: 14px; letter-spacing: 3px;">Go' .
        '\n			To Cart</a>' .
        '\n	</div>' .
        '\n</div>\n\n'
    ]
];

$chats = [
    [
        'thumbnail' => 'preview/thumbnail chat.jpg',
        'category' => '1003',
        'html' =>
        '<div class="row clearfix">' .
        '	<div class="column full" style="text-align: center;">' .
        '       <div class="chat-top-bar default" style="width: 100%; height: 5px; background: #50416C;"></div>' .
        '	</div>' .
        '</div>' .
        '<div class="row clearfix">' .
        '	<div class="column full" style="text-align: center;">' .
        '		<img class="chat-agent-profile" src="/cb/assets/minimalist-blocks/images/placeholder-images-product-5_large.png" alt="" loading="lazy" style="border-radius: 50%;height: 100px;width: 100px;">' .
        '	</div>' .
        '</div>' .
        '<div class="row clearfix">' .
        '    <div class="column full">' .
        '        <h1 class="chat-headline" style="font-size: 20px; font-weight: bold; text-align: center;">Thanks for chatting with {In_Firstname}</h1>' .
        '    </div>' .
        '</div>' .
        '<div class="row clearfix">' .
        '    <div class="column full">' .
        '        <p style="text-align: center;">Here is your chat transcript from <a href="{In_Website}">{In_Website}</a>' .
        '        </p>' .
        '    </div>' .
        '</div>' .
        '<div class="row clearfix">' .
        '    <div class="column full" data-noedit="">' .
        '        <div class="spacer height-60" style="height: 40px;"></div>' .
        '    </div>' .
        '</div>' .
        '<div class="chat-main-container row clearfix" style="margin-left: 0 !important;">' .
        '	<div class="column full chat-dynamic-content-container" data-noedit="">' .
        '		<div class="row clearfix" style="margin-left: 0 !important;">' .
        '			<div class="column full" data-noedit="">' .
        '				<div class="left-chat-bubble" style="background-color:{IN_THEME_SECONDARY_BACKGROUND_COLOR};color:{IN_THEME_SECONDARY_TEXT_COLOR};">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nam sapiente fugiat suscipit dolore molestiae eos facilis sequi magni quidem voluptas eum ratione quaerat eligendi, consequatur itaque fuga cum inventore tempora?</div>' .
        '				<div class="chat-bubble-time"><span class="chat-bubble-time-title">{In_Firstname} </span><span class="bubble-time">1:22pm</span></div>' .
        '			</div>' .
        '		</div>' .
        '		<div class="row clearfix" style="margin-left: 0 !important;">' .
        '			<div class="column full" data-noedit="">' .
        '				<div class="right-chat-bubble" style="background-color:{IN_THEME_PRIMARY_BACKGROUND_COLOR};color:{IN_THEME_PRIMARY_TEXT_COLOR};">Lorem ipsum dolor sit amet consectetur adipisicing elit. Nam sapiente fugiat suscipit dolore molestiae eos facilis sequi magni quidem voluptas eum ratione quaerat eligendi, consequatur itaque fuga cum inventore tempora?</div>' .
        '				<div class="chat-bubble-time right"><span class="chat-bubble-time-title">You </span><span class="bubble-time">1:22pm</span></div>' .
        '			</div>' .
        '		</div>' .
        '	</div>' .
        '</div>'
    ],
];

$signatures = [
    // [
    //     'thumbnail' => 'preview/signature-01.png',
    //     'category' => '1101',
    //     'html' =>
    //         '<div class="is-social">' .
    //         '<h4>' . $sig_full_name . '</h4>' .
    //         '<h5>' . $sig_designation . '</h5>' .
    //         '<h6>' . $sig_website . ' | ' . $sig_phone . '</h6>' .
    //         '<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident minus doloremque officiis laboriosam laudantium consequuntur facilis fugiat quo, ut modi at voluptatem, quae enim, perspiciatis consequatur repellendus repellat tempore ratione.</p>' .
    //         '<div style="width: 100%;"><img src="' . $sig_logo_url . '"  style="max-width: 140px; width: 150px;" alt="Signature Image"/></div>' .
    //         '</div>'
    // ],
    // [
    //     'thumbnail' => 'preview/signature-04.png',
    //     'category' => '1101',
    //     'html' =>
    //         '<div class="is-rounded-button-medium social-media-buttons-holder" style="margin:1em 0;">' .
    //         '<h4>' . $sig_full_name . '</h4>' .
    //         '<h5>' . $sig_designation . '</h5>' .
    //         '<h6>' . $sig_website . ' | ' . $sig_phone . '</h6>' .
    //         '<div style="width: 100%;"><img src="' . $sig_logo_url . '"  style="max-width: 140px; width: 150px;" alt="Signature Image"/></div>' .
    //         '<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident minus doloremque officiis laboriosam laudantium consequuntur facilis fugiat quo, ut modi at voluptatem, quae enim, perspiciatis consequatur repellendus repellat tempore ratione.</p>' .
    //         '<p></p>' .
    //         '<div style="text-align:center" class="social-buttons-container">' .
    //         '<a class="social-button-link" data-title="Twitter"  href="' . $sig_twitter . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/tw-round.png"  src="/cb/assets/social-icons/tw-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Facebook" href="' . $sig_facebook . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/fb-round.png" src="/cb/assets/social-icons/fb-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Instagram" href="' . $sig_instagram . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/insta-round.png" src="/cb/assets/social-icons/insta-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="LinkedIn" href="' . $sig_linkedin . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/linkedin-round.png" src="/cb/assets/social-icons/linkedin-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Skype" href="' . $sig_skype . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/skype-round.png" src="/cb/assets/social-icons/skype-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Mail" href="' . $sig_email . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/mail-round.png" src="/cb/assets/social-icons/mail-round.png" style="margin: 0;"></a>' .
    //         '</div>' .
    //         '<p></p>' .
    //         '</div>&nbsp;'
    // ],
    // [
    //     'thumbnail' => 'preview/signature-04.png',
    //     'category' => '1101',
    //     'html' =>
    //         '<div class="is-rounded-button-medium social-media-buttons-holder" style="margin:1em 0;">' .
    //         '<h6 style="text-align:center">' . $sig_website . '</h6>' .
    //         '<p>Lorem ipsum dolor sit amet consectetur adipisicing elit. Provident minus doloremque officiis laboriosam laudantium consequuntur facilis fugiat quo, ut modi at voluptatem, quae enim, perspiciatis consequatur repellendus repellat tempore ratione.</p>' .
    //         '<p></p>' .
    //         '<div style="text-align:center" class="social-buttons-container">' .
    //         '<a class="social-button-link" data-title="Twitter"  href="' . $sig_twitter . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/tw-round.png"  src="/cb/assets/social-icons/tw-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Facebook" href="' . $sig_facebook . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/fb-round.png" src="/cb/assets/social-icons/fb-round.png" style="margin: 0;"></a>' .
    //         '<a class="social-button-link" data-title="Instagram" href="' . $sig_instagram . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/insta-round.png" src="/cb/assets/social-icons/insta-round.png" style="margin: 0;"></a>' .
    //         '</div>' .
    //         '<p></p>' .
    //         '</div>&nbsp;'
    // ],
    [
        'thumbnail' => 'preview/signature-05.png',
        'category' => '1101',
        'html' =>
            '<p class="dragme" style="max-width: 100%; float: none; font-size: 12px;text-align:center;">{In_Website}</p>' .
            '<p class="dragme" style="max-width: 100%; float: none; font-size: 12px;text-align:center;">{In_Address}</p>' .
            '<div style="margin:1em 0;text-align:center" class="is-rounded-button-medium social-media-buttons-holder social-buttons-container">' .
            '<a class="social-button-link" data-title="Twitter"  href="' . $sig_twitter . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/tw-round.png"  src="/cb/assets/social-icons/tw-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="Facebook" href="' . $sig_facebook . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/fb-round.png" src="/cb/assets/social-icons/fb-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="Instagram" href="' . $sig_instagram . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/insta-round.png" src="/cb/assets/social-icons/insta-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="LinkedIn" href="' . $sig_linkedin . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/linkedin-round.png" src="/cb/assets/social-icons/linkedin-round.png" style="margin: 0;">' .
            '<a class="social-button-link" data-title="Skype" href="' . $sig_skype . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/skype-round.png" src="/cb/assets/social-icons/skype-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="Mail" href="' . $sig_email . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/mail-round.png" src="/cb/assets/social-icons/mail-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="pinterest" href="' . $sig_pinterest . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/pintrest-round.png" src="/cb/assets/social-icons/pintrest-round.png" style="margin: 0;">' .
            '<a class="social-button-link" data-title="youtube" href="' . $sig_yt . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/yt-round.png" src="/cb/assets/social-icons/yt-round.png" style="margin: 0;"></a>' .
            '<a class="social-button-link" data-title="tiktok" href="' . $sig_tt . '"><img class="social-button-icon no-image-edit" data-bg="#00bfff" data-icon="#ffffff" data-bgnew="#00bfff" data-iconnew="#ffffff" data-iconsrc="/cb/assets/social-icons/small/tt-round.png" src="/cb/assets/social-icons/tt-round.png" style="margin: 0;"></a>' .
            '</div>&nbsp;' .
            '<p></p>'
    ],
];

$basic = [
    [
        'thumbnail' => 'preview/basic-01.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            '<h1>Beautiful Content. Responsive.</h1>' .
            '<p><i>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</i></p>' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-02.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-03.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1>Heading 1 Text Goes Here.</h1>' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-04.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h2>Heading 2 Text Goes Here.</h2>' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-05.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<img src="/cb/assets/minimalist-blocks/images/oleg-laptev-545268-unsplash-VD7ll2.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-06.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/jon-lalin-731093-unsplash-(1)-tdmMt1.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-07.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/adam-birkett-209727-unsplash-(2)-H2BMm1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],
    [
        'thumbnail' => 'preview/basic-08.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-09.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            '<h1>Lorem Ipsum is simply dummy text of the printing industry</h1>' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-10.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            '<p>This is a special report</p>' .
            '<h1>Lorem Ipsum is simply dummy text of the printing industry</h1>' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-11.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h2 class="size-48">Lorem Ipsum is simply dummy text</h2>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-12.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-13.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<hr>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/basic-14.png',
        'category' => '120',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="list">' .
            '<img src="/cb/assets/icons/ion-checkmark.png">' .
            '<h3>List Item</h3>' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
            '<img src="/cb/assets/icons/ion-checkmark.png">' .
            '<h3>List Item</h3>' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/signature-04.png',
        'category' => '120',
        'html' =>
            '<div class="is-rounded-button-medium social-media-buttons-holder" style="margin:1em 0;">' .
            '<h6 style="text-align:center">' . $sig_website . '</h6>' .
            '<div style="text-align:center" class="social-buttons-container">' .
            '<a class="social-button-link" data-title="Twitter"  href="' . $sig_twitter . '" style="background-color: #00bfff;" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-twitter-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Facebook" href="' . $sig_facebook . '" style="background-color: #128BDB" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-facebook-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Instagram" href="' . $sig_instagram . '" style="background-color: #00bfff" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-instagram-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '</div>' .
            '</div>&nbsp;'
    ],
    [
        'thumbnail' => 'preview/signature-05.png',
        'category' => '120',
        'html' =>
            '<div style="margin:1em 0;text-align:center" class="is-rounded-button-medium social-media-buttons-holder social-buttons-container">' .
            '<a class="social-button-link" data-title="Twitter"  href="' . $sig_twitter . '" style="background-color: #00bfff;" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-twitter-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Facebook" href="' . $sig_facebook . '" style="background-color: #128BDB" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-facebook-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Instagram" href="' . $sig_instagram . '" style="background-color: #00bfff" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-instagram-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="LinkedIn" href="' . $sig_linkedin . '" style="background-color: #128BDB" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-linkedin-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Skype" href="' . $sig_skype . '" style="background-color: #00bfff" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-social-skype-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '<a class="social-button-link" data-title="Mail" href="' . $sig_email . '" style="background-color: #DF311F" style="padding: 5px;"><img class="social-button-icon no-image-edit"  src="/cb/assets/icons/ion-android-drafts-white.png" style="width: 24px; height: 24px;display: inline-block; vertical-align: middle;  margin: 10px;"></a>' .
            '</div>&nbsp;'
    ],
];

$articles = [

    [
        'thumbnail' => 'preview/article-02.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-46">Flying High</h1>' .
            '\n<p style="border-bottom: 2px solid #e74c3c; width: 60px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type.</p>' .
            '\n<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type.</p>' .
            '\n<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],
    [
        'thumbnail' => 'preview/article-04.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<p class="size-16">A BEAUTIFUL DAY IN OCTOBER</p>' .
            _tabs(1) . '<h1 class="size-50">Time to think, time to create.</h1>' .
            '\n</div>' .
            '\n<p class="size-16">— By David Anderson</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-07.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-96" style="text-align: center; color: rgb(204, 204, 204); line-height: 1.2">Sunday Lovely Sunday.</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center;"><i style="color: rgb(204, 204, 204);">By Jennifer Anderson</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-08.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="color: rgb(136, 136, 136);">WORDS FROM ANDREW JONES</p>' .
            '\n<h1 class="size-60">Home is wherever I\\\'m with you.</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-09.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-32 is-title5-32 is-title-lite"><i>Simplify Things</i></h1>' .
            '\n<p style="color: rgb(136, 136, 136);">Natasha Williams</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Vivamus leo ante, consectetur sit amet.&nbsp;Lorem ipsum dolor sit amet, consectetur adipiscing elit.&nbsp;</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-10.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<p class="size-18">EMILLIA JONES</p>' .
            _tabs(1) . '<h1 class="size-96" style="line-height: 1.3">Hello, Summer.</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem Ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-13.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-64">Slow Living</h1>' .
            '\n</div>' .
            '\n<p>Vivian C. Bailey</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '\n<p>Vivamus leo ante, consectetur sit amet vulputate vel, sit amet lectus.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-16.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-42" style="text-align: center;">Simple, Versatile, Functional</h1>' .
            _tabs(1) . '<p class="size-18" style="text-align: center; line-height: 2.2">JANE SMITH</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-23.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-32 is-title5-32 is-title-lite" style="width:100%;max-width: 340px;">New Style</h1>' .
            '\n<p>By David Smith</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-25.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-21" style="text-align: center; letter-spacing: 4px;"><i>the</i></p>' .
            '\n<h1 class="size-68" style="text-align: center; letter-spacing: 18px;">OCEAN</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: center">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus leo ante, consectetur sit amet vulputate vel, dapibus sit amet lectus.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: center"><i>Spencer Lane</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-27.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-96">A Little Story</h1>' .
            '\n</div>' .
            '\n<p style="border-bottom: 3px solid #333; width: 80px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: right;">JOHN ANDERSON</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-30.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="text-align: center;">My Summer</h1>' .
            '\n<p style="text-align: center;"><i><span style="color: rgb(136, 136, 136);">"Lorem Ipsum is simply dummy text of the printing and typesetting industry."<br>Jane Clark</span></i></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '\n<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-31.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h1>Simple, clean, bright</h1>' .
            '\n<p class="size-16">— Samantha Holmes</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-33.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<p class="size-18">EMMA STAUFER</p>' .
            _tabs(1) . '<h1 class="size-48" style="text-transform: none">Back to December</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-35.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-60">Happiness.</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet. Vimamus ante.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet.&nbsp;Vimamus ante.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;"><i>Bryan Lewis</i></p>' .
            '</div>' .
            '</div>'
    ],


    [
        'thumbnail' => 'preview/article-39.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="padding-20">' .
            '<h1 class="size-48 is-title5-48 is-title-lite">Spring in March</h1>' .
            '</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="padding-20">' .
            _tabs(1) . '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur elit.</p>' .
            _tabs(1) . '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            _tabs(1) . '<div class="spacer height-20"></div>' .
            _tabs(1) . '<p class="size-16" style="text-align: right;">Irene Johnson</p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-40.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-80">Twenty Four Minutes</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center;">William Norris</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-41.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-50" style="text-align: right; letter-spacing: 6px;">Early Morning Riser</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: right;"><i>Jeff Watkins</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-43.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-32" style="text-align: center; letter-spacing: 3px;">BEAUTY OF NATURE</h1>' .
            '\n<hr>' .
            '\n<p class="size-14" style="text-align: center; letter-spacing: 4px;">DAVID ANDERSON</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-46.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-32 is-title3-32 is-title-lite" style="text-align: center;">October & November</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '\n<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: right;"><i>Sarah Anderson</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-49.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-46">just chillin\\\'</h1>' .
            '\n<p class="size-16"><i style="color: rgb(136, 136, 136);">"Lorem Ipsum is simply dummy text." — Anne Marry</i></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-50.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center;">Michelle Duncan</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-54" style="text-align: center;">IN LOVE WITH YOUR LIFE</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus leo ante, consectetur sit amet vulputate vel, dapibus sit amet lectus.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-51.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-32 is-title4-32 is-title-lite" style="display:inline-block">Behind you.</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-14" style="text-align: justify;"><i>Brenda Waller</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-52.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="text-align: center; letter-spacing: 10px;">CLEAN</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center;"><i>Words from Michael Williams</i></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet. Vivamus ante.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet. Vivamus ante.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-54.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div style="width:100%;max-width:350px;">' .
            _tabs(1) . '<p>A STORY.</p>' .
            _tabs(1) . '<h1 class="size-38">THE WHEELS ARE SPINNING</h1>' .
            _tabs(1) . '<p class="size-16" style="color: rgb(136, 136, 136);">Casey Lansford</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-55.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite" style="text-align: center;">Brave</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus leo ante, consectetur sit amet vulputate vel, dapibus sit amet.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-14" style="text-align: center; letter-spacing: 5px;">RUTH WATTERS</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-57.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-54" style="text-align: left; letter-spacing: 2px;">Keep everything Simple</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<p class="size-16" style="text-align: justify;"><i><span style="color: rgb(147, 147, 147);">Words from:<br> Brandon Lamberth</span></i></p>' .
            '</div>' .
            '<div class="column two-third">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-58.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-64 is-title5-64 is-title-bold">hello...</h1>' .
            '\n<p class="size-16" style="text-transform: uppercase; letter-spacing: 4px;">Lorem Ipsum is simply dummy text of the printing industry</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: justify; letter-spacing: 2px;">Samantha Holmes</p>' .
            '</div>' .
            '</div>'
    ],


    [
        'thumbnail' => 'preview/article-59.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="color: rgb(198, 198, 198); text-align: center;font-weight:bold">Go explore.</h1>' .
            '\n<p class="size-14" style="text-align: center;"><i>Russel Y. Trevino </i></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-60.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-transform: uppercase; letter-spacing: 4px;">Heart-warming story from<br>Wilhelmina Bradley</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-54">Best friend</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit.</p>' .
            '</div>' .
            '</div>'

    ],

    [
        'thumbnail' => 'preview/article-61.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display" style="width:100%;max-width:400px;margin: 0 auto">' .
            _tabs(1) . '<h1 class="size-42" style="letter-spacing: 5px;">WORDS FROM HEART</h1>' .
            _tabs(1) . '<p class="size-14" style="color: rgb(136, 136, 136); letter-spacing: 4px;">Stephen Garcia</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<p style="text-align: justify;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/article-62.png',
        'category' => '118',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center; letter-spacing: 2px;">A STORY</p>' .
            '\n<h1 class="size-54" style="text-align: center; letter-spacing: 2px;">Dancing in Harmony</h1>' .
            '\n<p style="text-align: center;"><i>"Lorem Ipsum is simply dummy text of the printing."</i></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="text-align: center; text-transform: uppercase; letter-spacing: 3px;">Annie Baldwin </p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-80"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p style="text-align: justify;">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book. Lorem ipsum dolor sit amet, consectetur adipiscing elit. Vivamus leo ante.</p>' .
            '</div>' .
            '</div>'
    ],

];

$headers = [
    [
        'thumbnail' => 'preview/paragraph-01.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<p>Lorem ipsum dolor sit amet consectetur, adipisicing elit. Tempore modi fuga repudiandae odit maxime nulla unde voluptas. Ex consequuntur debitis omnis iusto voluptas pariatur. Reiciendis provident corporis est velit aliquid.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-02.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-32 is-title4-48" style="letter-spacing:5px;overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">STUNNING</h1>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-07.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="margin-bottom:0;overflow-wrap: anywhere;letter-spacing: 1px;">Outstanding</h1>' .
            _tabs(1) . '<p style="margin-top:0;overflow-wrap: anywhere;">Lorem Ipsum is dummy text of the printing and typesetting industry</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Contact Us</a>' .
            '</div>' .
            '</div>'
    ],
    [
        'thumbnail' => 'preview/header-09.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Calm, Pure, and lovely</h1>' .
            _tabs(1) . '<p style="overflow-wrap: anywhere;">Lorem Ipsum has been the industry\\\'s standard text ever since the 1500s, when an unknown printer took a galley of type</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Shop Now</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-23.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32 is-title1-80 is-title-lite" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Pure & Healthy</h1>' .
            '\n</div>' .
            '\n<p class="size-21" style="overflow-wrap: anywhere;">Lorem Ipsum has been the industry\\\'s standard text ever since the 1500s, when an unknown printer took a galley of type</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Our Products</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-25.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<div class="display">' .
            _tabs(1) . '<p class="size-16 is-info2" style="overflow-wrap: anywhere;">Welcome to our coffee shop</p>' .
            _tabs(1) . '<h1 class="size-32" style="text-transform: none;overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Smell it, taste it.</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30">Browse Menu</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-26.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Planning a memorable trip? You came to the right place.</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper">CONTACT US</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-27.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<p class="size-16" style="overflow-wrap: anywhere;">We are Creative Agency in New York</p>' .
            _tabs(1) . '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;"><b>CLEAN. SIMPLE.</b></h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper">Get A Quote</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-28.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Good for Health, Good for You</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div style="margin: 10px 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30">All Products</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-32.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Stitch Studio</h1>' .
            _tabs(1) . '<p style="overflow-wrap: anywhere;">Join Our Sewing Classes & Craft Workshops</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Contact Us</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-34.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="letter-spacing: 10px; overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Unique.</h1>' .
            _tabs(1) . '<p>PLAN YOUR SPECIAL DAY</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small" style="font-weight: 200">Book a Consultation</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-38.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-32 is-title-lite" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">DREAM HOME</h1>' .
            '\n<p class="size-18" style="overflow-wrap: anywhere;">BEAUTIFY YOUR HOME WITH MODERN FURNITURE SET.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper">New Arrivals</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-41.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">We Create and Design Beautiful Websites</h1>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Our Works</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-42.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="letter-spacing: 8px;overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Tasty</h1>' .
            _tabs(1) . '<p class="size-16" style="overflow-wrap: anywhere;">Healthy & Natural Food</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full right">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30">Our Menu</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-43.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="letter-spacing: 7px;overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Mike Watson</h1>' .
            _tabs(1) . '<p class="size-16" style="overflow-wrap: anywhere;">Expert in Public Interior Design</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">View Portfolio</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/header-46.png',
        'category' => '101',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-32" style="text-transform: none;overflow-wrap: anywhere;font-weight: 800;letter-spacing: 1px;">Monday to Friday</h1>' .
            _tabs(1) . '<p class="size-16" style="overflow-wrap: anywhere;">We make shopping way easier and convenient for you</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper">View Collection</a>' .
            '</div>' .
            '</div>'
    ],

];

$photos = [

    [
        'thumbnail' => 'preview/imgtext-01.png',
        'category' => '102',
        'html' =>
        '<table width="100%" role="presentation" cellpadding="0" cellspacing="0" border="0" style="max-width: 100%;">' .
            '<tbody style="max-width: 100%;">' .
                '<tr style="max-width: 100%;">' .
                    '<td class="td-text-on-image" valign="center" align="center" style="background: url(&quot;/cb/assets/minimalist-blocks/images/jon-lalin-731093-unsplash-(1)-tdmMt1.jpg&quot;) center center / 100% 100% no-repeat rgb(181, 207, 227); height: 311px; max-width: 100%; width: 530px;">' .
                        '<h1 style="position: relative; max-width: 100%; color: #000); font-size: 53px; font-weight: bold; top: 93px;">This is text</h1>' .
                    '</td>' .
                '</tr>' .
            '</tbody>' .
        '</table>'
    ],
    [
        'thumbnail' => 'preview/photos-51.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/jon-lalin-731093-unsplash-(1)-tdmMt1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/adam-birkett-209727-unsplash-(2)-H2BMm1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-52.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/chuttersnap-413002-unsplash-83HqE1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/caroline-bertolini-270870-unsplash-1j5FB2.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/theo-roland-740436-unsplash-WqnWJ3.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-50.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<img src="/cb/assets/minimalist-blocks/images/oleg-laptev-545268-unsplash-VD7ll2.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-48.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix" style="padding: 0;">' .
            '<div class="column half" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/jon-lalin-731093-unsplash-(1)-tdmMt1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column half" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/adam-birkett-209727-unsplash-(2)-H2BMm1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-49.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix" style="padding: 0;">' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/chuttersnap-413002-unsplash-83HqE1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/caroline-bertolini-270870-unsplash-1j5FB2.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/theo-roland-740436-unsplash-WqnWJ3.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-46.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix" style="padding: 0;">' .
            '<div class="column full" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/oleg-laptev-545268-unsplash-VD7ll2.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-53.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix" style="padding: 0;">' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/chuttersnap-413002-unsplash-83HqE1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/caroline-bertolini-270870-unsplash-1j5FB2.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column third" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/theo-roland-740436-unsplash-WqnWJ3.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix" style="padding: 0;">' .
            '<div class="column half" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/jon-lalin-731093-unsplash-(1)-tdmMt1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '<div class="column half" style="padding: 0;">' .
            '<img src="/cb/assets/minimalist-blocks/images/adam-birkett-209727-unsplash-(2)-H2BMm1.jpg" alt="" style="margin: 0;float: left;">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-14.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite" style="text-align: center;">PORTFOLIO</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/pawel-czerwinski-1080345-unsplash-Zxz1W1.jpg" alt="">' .
            '\n<h3 class="size-21">IMAGE CAPTION</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text</p>' .
            '</div>' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/agata-create-1137058-unsplash-(1)-UvBs02.jpg" alt="">' .
            '\n<h3 class="size-21">IMAGE CAPTION</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-15.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite" style="text-align: center;">Gallery</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/monika-grabkowska-742426-unsplash-AtCtH1.jpg" alt="">' .
            '\n<h3 class="size-18">CAPTION</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Lorem Ipsum is dummy text.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/mira-bozhko-456995-YiVKC1.jpg" alt="">' .
            '\n<h3 class="size-18">CAPTION</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Lorem Ipsum is dummy text.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/lauren-mancke-63448-unsplash-AtCtH2.jpg" alt="">' .
            '\n<h3 class="size-18">CAPTION</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Lorem Ipsum is dummy text.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-16.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<img src="/cb/assets/minimalist-blocks/images/susanne-schwarz-1142929-unsplash-IZGK11.jpg" alt="">' .
            '\n<h3 class="size-21">IMAGE CAPTION</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-17.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-54" style="letter-spacing: 6px;">GALLERY</h1>' .
            '\n<p style="border-bottom: 2.5px solid #b5b5b5;width: 60px;display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/art-materials-close-up-color-pencils-1484263-jT5E21.jpg" alt="" style="border-radius: 500px;">' .
            '\n<p class="size-14">Lorem Ipsum is dummy text</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/oleg-laptev-546607-unsplash-SKGb82.jpg" alt="" style="border-radius: 500px;">' .
            '\n<p class="size-14">Lorem Ipsum is dummy text</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/michal-grosicki-XG2yA3.jpg" alt="" style="border-radius: 500px;">' .
            '\n<p class="size-14">Lorem Ipsum is dummy text</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-18.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="text-align: center;letter-spacing: 2px;color: rgb(191, 191, 191);font-weight: bold;">Gallery.</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/remi-muller-359340-unsplash-JOL3q1.jpg" alt="">' .
            '\n<p><i>Lorem Ipsum is dummy text</i></p>' .
            '</div>' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/dlanor-s-591314-unsplash-maNC32.jpg" alt="">' .
            '\n<p><i>Lorem Ipsum is dummy text</i></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-19.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-38" style="letter-spacing: 1px;">Creative Things We\\\'ve Done</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/agata-create-1132088-unsplash-(1)-adQTO1.jpg" alt="">' .
            '\n<p>IMAGE CAPTION</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/lucrezia-carnelos-1127196-unsplash-Y7ahO2.jpg" alt="">' .
            '\n<p>IMAGE CAPTION</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/bright-bulb-close-up-1166643-oof1G3.jpg" alt="">' .
            '\n<p>IMAGE CAPTION</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-20.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<img src="/cb/assets/minimalist-blocks/images/omar-lopez-32084-8ciiC1.jpg" alt="">' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is dummy text of the printing industry</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-21.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-46" style="letter-spacing: 5px;">CREATIVE THINGS WE HAVE CREATED LATELY</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/-ORebV1.jpg" alt="">' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/ian-dooley-298771-unsplash-Hu2RU3.jpg" alt="">' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/daniel-klopper-1142809-unsplash-pToHm2.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-22.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h3 class="size-24" style="font-style: normal; letter-spacing: 2px;">Caption.</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<img src="/cb/assets/minimalist-blocks/images/anthony-tran-1076077-vcoLP1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-23.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-1200191-unsplash-Ms1O81.jpg" alt="">' .
            '</div>' .
            '<div class="column third">' .
            '<h4 class="size-21" style="letter-spacing: 2px;">IMAGE CAPTION</h4>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-24.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h4 class="size-21" style="letter-spacing: 2px;">IMAGE CAPTION</h4>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/iman-soleimany-zadeh-1205567-unsplash-AUqMZ2.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-25.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/maksym-zakharyak-688728-unsplash-p9w092.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<div class="spacer height-60"></div>' .
            '\n<h4 class="size-21" style="letter-spacing: 2px; text-align: right;">IMAGE CAPTION</h4>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p style="text-align: right; font-style: normal;">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-26.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="spacer height-60"></div>' .
            '\n<h4 class="size-21" style="letter-spacing: 2px;">IMAGE CAPTION</h4>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/rodion-kutsaev-24833-unsplash-HEuVp1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-27.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/monica-galentino-102655-unsplash-gfbdC1.jpg" alt="">' .
            '</div>' .
            '<div class="column third">' .
            '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-28.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante.</p>' .
            '</div>' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-1197453-unsplash-a7ozj2.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-29.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-XstZ21.jpg" alt="">' .
            '\n<img src="/cb/assets/minimalist-blocks/images/-budQW2.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="font-style: normal; letter-spacing: 2px;">Caption.</h3>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-30.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 style="font-style: normal; letter-spacing: 2px;">Caption.</h3>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/sarah-dorweiler-211779-unsplash-dN96G1.jpg" alt="">' .
            '\n<img src="/cb/assets/minimalist-blocks/images/kris-atomic-39874-unsplash-vpMhe2.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-35.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-1197458-unsplash-J52N31.jpg" alt="">' .
            '</div>' .
            '<div class="column half right">' .
            '<h3 style="text-align: right; letter-spacing: 3px;">Image Caption</h3>' .
            '\n<p style="text-align: right;">Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante sit amet.</p>' .
            '\n<p style="border-bottom: 2px solid #000; width: 60px; display: inline-block;"></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-36.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 3px;">Image Caption</h3>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante sit amet.</p>' .
            '\n<p style="border-bottom: 2px solid #000; width: 60px; display: inline-block;"></p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-5F3zm1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-37.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div style="padding-right:30px">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/-4r9Fa1.jpg" alt="">' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="spacer height-80"></div>' .
            '\n<h3 class="size-48" style="text-align: right; font-weight: bold;">Goodbye, things.</h3>' .
            '\n<div class="spacer height-80"></div>' .
            '\n<p style="text-align: right;">Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-38.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="spacer height-80"></div>' .
            '\n<h3 class="size-48" style="font-weight: bold;">Behold the Beauty.</h3>' .
            '\n<div class="spacer height-80"></div>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<div style="padding-left:30px">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/-lbizY1.jpg" alt="">' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-39.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-ocaLR1.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<div class="spacer height-40"></div>' .
            '\n<h3 class="size-38" style="text-align: center; letter-spacing: 3px;">WORK <b>01</b></h3>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p style="text-align: center;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="spacer height-40"></div>' .
            '\n<h3 class="size-38" style="text-align: center; letter-spacing: 3px;">WORK <b>02</b></h3>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<p style="text-align: center;">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-mHAa32.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-40.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/-OJKzv1.jpg" alt="">' .
            '</div>' .
            '<div class="column third">' .
            '<div class="spacer height-180"></div>' .
            '\n<h3 class="size-24" style="letter-spacing: 2px;">Image Caption</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/photos-41.png',
        'category' => '102',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="spacer height-180"></div>' .
            '\n<h3 class="size-24" style="letter-spacing: 2px;">Image Caption</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column two-third">' .
            '<img src="/cb/assets/minimalist-blocks/images/-I81sR1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],


];

$profiles = [

    [
        'thumbnail' => 'preview/profile-01.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 2px;">MEET OUR TEAM</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/bangkit-ristant-395541-e0mhz1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">VINCENT NELSON</h3>' .
            '\n<p style="color: #b7b7b7">WEB DESIGNER</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/redd-angelo-427759-33bDf2.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">NATHAN WILLIAMS</h3>' .
            '\n<p style="color: #b7b7b7">WEB DEVELOPER</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/mads-schmidt-rasmussen-186319-8AVbA1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">THOMAS CALVIN</h3>' .
            '\n<p style="color: #b7b7b7">ACCOUNT MANAGER</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-02.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 2px;">HIGHLY QUALIFIED TEAM</h1>' .
            '\n<p style="border-bottom: 2px solid #e74c3c; width: 60px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/samuel-zeller-413072-Tx4ai1.jpg" alt="">' .
            '\n<h3 class="size-21 is-title-lite">JENNIFER ASH</h3>' .
            '\n<div class="social-media-buttons-holder"><div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div></div>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/michael-236683-Tx4ai2.jpg" alt="">' .
            '\n<h3 class="size-21 is-title-lite">MICHAEL ISON</h3>' .
            '\n<div class="social-media-buttons-holder"><div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/michael-221247-Hspxi3.jpg" alt="">' .
            '\n<h3 class="size-21 is-title-lite">JOHN CONWAY</h3>' .
            '\n<div class="social-media-buttons-holder"><div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-03.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 3px;">MEET THE EXPERTS</h1>' .
            '\n<p style="color: rgb(136, 136, 136);">Here are our awesome team.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/working-2618559_1920-kr4Af1.jpg" alt="">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">Sarah Doe</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Founder</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-632454-unsplash-ecHZN1.jpg" alt="">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">David Anderson</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Programmer</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/girl-2618562_1920-KAzoZ2.jpg" alt="">' .
            '\n<h3 class="size-21" style="letter-spacing: 2px;">Jennifer Clarke</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Web Designer</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-04.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="font-size: 56px !important; letter-spacing: 3px; width: 400px; max-width: 100%; line-height: 1.2;">Meet <br>our amazing team.</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/luke-ellis-craven-365822-wrHAg1.jpg" alt="">' .
            '\n<h5 class="size-21" style="letter-spacing: 2px;">Nathan Williams <span style="color: rgb(136, 136, 136);">/ Founder</span></h5>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/yoann-boyer-276971-S8TZu2.jpg" alt="">' .
            '\n<h5 class="size-21" style="letter-spacing: 2px;">Sarah Smith <span style="color: rgb(136, 136, 136);">/ Developer</span></h5>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/minimalist-blocks/images/pablo-hermoso-429590-(1)-fP4pI3.jpg" alt="">' .
            '\n<h5 class="size-21" style="letter-spacing: 2px;">Jane Doe <span style="color: rgb(136, 136, 136);">/ Web Designer</span></h5>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-05.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            _tabs(1) . '<h1 style="text-transform: none">Meet The Team</h1>' .
            '\n</div>' .
            '\n<p style="letter-spacing: 9px;">Here are our awesome team.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/adult-1868750_1920-EzivE1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21">David Smith</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">CEO & Founder</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/noah-buscher-502067-(1)-rT7Vn1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21">Milla Clarke</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Project Manager</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/berwin-coroza-495276-tXRrf2.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21">John Rugg</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Developer</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/mark-skeet-537093-6gF113.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21">Sarah Ashley</h3>' .
            '\n<p class="size-14" style="color: rgb(136, 136, 136);">Web Designer</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-07.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-46" style="letter-spacing: 2px;">OUR TEAM</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/girl-690119-c7s4t1.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<h2>Your Name</h2>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/photo-1437915160026-6c59da36ede2-AakHA2.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<h2>Your Name</h2>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled it to make a type specimen book.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-08.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 4px;">MEET THE EXPERTS</h1>' .
            '\n<p>Here are our awesome team</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card max-390 is-light-text" style="width:calc(100%);background: #1c93ad;">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/bernard-osei-608155-unsplash-BsEPC1.jpg" alt="" class="margin-0">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-21 margin-0" style="letter-spacing: 1px;">JANE FOSTER</h3>' .
            _tabs(2) . '<p class="size-16">Lorem Ipsum is simply dummy text.</p>' .
            _tabs(2) . '<div class="social-media-buttons-holder">' .
            _tabs(2) . '<div class="is-social edit size-14 social-buttons-container">' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card max-390 is-light-text" style="width:calc(100%);background: #e0527e;">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/-HAWqm1.jpg" alt="" class="margin-0">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-21 margin-0" style="letter-spacing: 1px;">MICHELLE DOE</h3>' .
            _tabs(2) . '<p class="size-16">Lorem Ipsum is simply dummy text.</p>' .
            _tabs(2) . '<div class="social-media-buttons-holder">' .
            _tabs(2) . '<div class="is-social edit size-14 social-buttons-container">' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card max-390 is-light-text" style="width:calc(100%);background: #e17055">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/girl-from-behind-1741699-(1)-PqduN2.jpg" alt="" class="margin-0">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-21 margin-0" style="letter-spacing: 1px;">JANE WILLIAMS</h3>' .
            _tabs(2) . '<p class="size-16">Lorem Ipsum is simply dummy text.</p>' .
            _tabs(2) . '<div class="social-media-buttons-holder">' .
            _tabs(2) . '<div class="is-social edit size-14 social-buttons-container">' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(3) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-12.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-32 is-title1-32 is-title-lite" style="line-height:1"><b>OUR TEAM</b></h1>' .
            '\n<p style="border-bottom: 2.5px solid #e74c3c; width: 60px; display: inline-block; margin-top: 0"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-632454-unsplash-oLoHS1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21 is-title-lite">JOHN ANDERSON</h3>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-12 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/-tfqUv1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21 is-title-lite">DAVID CLARK</h3>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-12 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/rawpixel-1054600-unsplash-PylVl2.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-21 is-title-lite">NATASHA KERR</h3>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-12 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-13.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-21 is-info1">Discover More</p>' .
            '\n<h1 class="size-48 is-title1-48 is-title-lite">ABOUT ME</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/sarah-noltner-687653-unsplash-6Agfw1.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">Jeniffer Phillips</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-14.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 style="letter-spacing: 4px;">ABOUT ME</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/luke-porter-520986-unsplash-kjuDr1.jpg" alt="" style="border-radius: 500px;">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">David Stuart</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-15.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 2px;">MEET OUR TEAM</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/nicole-honeywill-546846-unsplash-(1)-84PNj2.jpg" alt="" style="border-radius: 500px;">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">Laura Clark</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/alex-iby-480498-unsplash-JOL3q3.jpg" alt="" style="border-radius: 500px;">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">Michael Smith</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-16.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-38" style="letter-spacing: 2px;">OUR PASSIONATE TEAM</h1>' .
            '\n<p style="border-bottom: 2px solid #000;width: 70px;display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/nordwood-themes-166423-unsplash-(1)-4WJ2H1.jpg" alt="">' .
            '\n<h3 class="size-24" style="letter-spacing: 1px;">Roy Krueger</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/-9Htn91.jpg" alt="">' .
            '\n<h3 class="size-24" style="letter-spacing: 1px;">Amanda Barnet</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-17.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">MEET THE TEAM</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/parker-johnson-1100877-unsplash-(1)-rufBy1.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">Patricia Young</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">Angela Griffin</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/beautiful-close-up-color-1078058-Qywhs1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-18.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-18 is-info1">Hi World!</p>' .
            '\n<h1 class="size-48 is-title1-48 is-title-lite">I\\\'M AUDREY SMITH</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 style="letter-spacing: 1px;">I design beautiful and functional stuff</h3>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Lorem Ipsum is simply dummy text.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/daniel-apodaca-584113-unsplash-(1)-U9Iby1.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/profile-19.png',
        'category' => '103',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">MEET OUR TEAM</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/alex-shaw-1116446-unsplash-JWfd61.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-24" style="letter-spacing: 1px;">Yolanda Ludwig</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half center">' .
            '<img src="/cb/assets/minimalist-blocks/images/-JMXQP1.jpg" alt="" style="border-radius: 500px;">' .
            '\n<h3 class="size-24" style="letter-spacing: 1px;">Anthony Fales</h3>' .
            '\n<p>Lorem Ipsum is simply text of the printing and typesetting industry.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="size-14 is-social edit social-buttons-container">' .
            _tabs(1) . '<a class="social-button-link" href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '<a class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],
];

$features = [

    [
        'thumbnail' => 'preview/features-01.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">WHY CHOOSE US</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-monitor-outline-gray.png" style="width: 30px;margin: 10px 0 0 0;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-edit-gray.png" style="width: 30px;margin: 10px 0 0 0;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-camera-gray.png" style="width: 30px;margin: 10px 0 0 0;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-02.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-lite"><i>01</i></h1>' .
            '\n<h3>Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-lite"><i>02</i></h1>' .
            '\n<h3>Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-lite"><i>03</i></h1>' .
            '\n<h3>Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-03.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite is-upper" style="text-align: center; font-weight: 300;">WHY CHOOSE US?</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24 default-font2" style="margin: 0 0 0 50px; font-weight: 300">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px;  font-weight: 300">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24 default-font2" style="margin: 0 0 0 50px;  font-weight: 300">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px;  font-weight: 300">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24 default-font2" style="margin: 0 0 0 50px;  font-weight: 300">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px; font-weight: 300">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-04.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-android-bulb-red.png" style="width: 35px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">CREATIVE IDEAS</h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-gear-outline-red.png" style="width: 35px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">WEB DEVELOPMENT </h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-camera-red.png" style="width: 35px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">PHOTOGRAPHY</h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-iphone-red.png" style="width: 35px;margin-left: 20px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">RESPONSIVE DESIGN</h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-paper-outline-red.png" style="width: 35px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">DIGITAL MARKETING</h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="padding-20">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-clock-outline-red.png" style="width: 35px;">' .
            _tabs(1) . '<h3 class="size-18" style="line-height:1">ONLINE SUPPORT</h3>' .
            _tabs(1) . '<p style="border-bottom: 2px solid #e74c3c; width: 50px; display: inline-block;"></p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-05.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h4 class="size-28 display-font2">Discover</h4>' .
            '\n<h1 class="size-48 is-title1-48 is-title-lite is-upper">Why Choose Our Products</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/imac-Bz83W1.png" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24" style="margin: 0 0 0 50px">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px">Lorem Ipsum is simply dummy text</p>' .
            '\n</div>' .
            '\n<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24" style="margin: 0 0 0 50px">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px">Lorem Ipsum is simply dummy text</p>' .
            '\n</div>' .
            '\n<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-checkmark.png">' .
            _tabs(1) . '<h3 class="size-24" style="margin: 0 0 0 50px">Feature Item</h3>' .
            _tabs(1) . '<p style="margin: 5px 0 0 50px">Lorem Ipsum is simply dummy text</p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-06.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-images-gray.png" style="width: 32px;margin: 10px;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-monitor-outline-gray.png" style="width: 32px;margin: 10px;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-android-clipboard-gray.png" style="width: 32px;margin: 10px;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 50px; height: 50px; line-height: 50px; border-radius: 50%; border: 2px solid #888888; display: inline-block;margin-bottom: 25px">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-android-options-gray.png" style="width: 32px;margin: 10px;">' .
            '\n</div>' .
            '\n<h3 class="size-21 is-title-lite">FEATURE ITEM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-07.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p class="size-64 is-title1-64 is-title-bold">1</p>' .
            '\n<h3 class="size-24 is-title-lite" style="line-height: 1.5">CREATIVE IDEAS</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p class="size-64 is-title1-64 is-title-bold">2</p>' .
            '\n<h3 class="size-24 is-title-lite" style="line-height: 1.5">WEB DEVELOPMENT</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p class="size-64 is-title1-64 is-title-bold">3</p>' .
            '\n<h3 class="size-24 is-title-lite" style="line-height: 1.5">RESPONSIVE DESIGN</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<p class="size-64 is-title1-64 is-title-bold">4</p>' .
            '\n<h3 class="size-24 is-title-lite" style="line-height: 1.5">ONLINE SUPPORT</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px;"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-08.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 default-font2" style="letter-spacing: 5px; font-weight: 300">FEATURES</h1>' .
            '\n<p style="border-bottom: 2px solid #333; width: 50px; display: inline-block; margin: 0"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<h1 class="size-48">01</h1>' .
            '\n<h3 class="size-24 default-font2" style="letter-spacing: 2px; font-weight: 300">FEATURE ONE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<h1 class="size-48">02</h1>' .
            '\n<h3 class="size-24 default-font2" style="letter-spacing: 2px; font-weight: 300">FEATURE TWO</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<h1 class="size-48">03</h1>' .
            '\n<h3 class="size-24 default-font2" style="letter-spacing: 2px; font-weight: 300">FEATURE THREE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-09.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">SPECIAL FEATURES</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/icons/ion-android-bulb.png" style="width: 30px;">' .
            '\n<h3 class="size-24">Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/icons/ion-android-globe.png" style="width: 30px;">' .
            '\n<h3 class="size-24">Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/icons/ion-android-download.png" style="width: 30px;">' .
            '\n<h3 class="size-24">Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 30px;">' .
            '\n<h3 class="size-24">Feature Item</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/features-10.png',
        'category' => '105',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 2px;">OUR FEATURES</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-android-bulb.png" style="width: 30px;">' .
            _tabs(1) . '<h3 style="margin: 0 0 0 70px">FEATURE ITEM</h3>' .
            _tabs(1) . '<p style="margin: 10px 0 0 70px">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-android-checkmark-circle.png" style="width: 30px;">' .
            _tabs(1) . '<h3 style="margin: 0 0 0 70px">FEATURE ITEM</h3>' .
            _tabs(1) . '<p style="margin: 10px 0 0 70px">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 30px;">' .
            _tabs(1) . '<h3 style="margin: 0 0 0 70px">FEATURE ITEM</h3>' .
            _tabs(1) . '<p style="margin: 10px 0 0 70px">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-wrench.png" style="width: 30px;">' .
            _tabs(1) . '<h3 style="margin: 0 0 0 70px">FEATURE ITEM</h3>' .
            _tabs(1) . '<p style="margin: 10px 0 0 70px">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

];

$steps = [

    [
        'thumbnail' => 'preview/steps-01.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-21" style="color: #d4d4d4; font-family: \\\'Georgia\\\', serif;"><i>Discover</i></p>' .
            '\n<h1 class="size-32 is-title1-32 is-title-bold">HOW IT WORKS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-bold" style="color: #d4d4d4;">1.</h1>' .
            '\n<h3 class="size-24 is-title-lite">STEP 01</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px; display: inline-block; margin-top: 0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-bold" style="color: #d4d4d4;">2.</h1>' .
            '\n<h3 class="size-24 is-title-lite">STEP 02</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px; display: inline-block; margin-top: 0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80 is-title-bold" style="color: #d4d4d4;">3.</h1>' .
            '\n<h3 class="size-24 is-title-lite">STEP 03</h3>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px; display: inline-block; margin-top: 0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-02.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 style="letter-spacing: 4px;">HOW WE WORK</h1>' .
            '\n<p style="border-bottom: 2px solid #000; width: 60px; display: inline-block; margin-top: 0"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h2 style="color: rgb(204, 204, 204);">01.</h2>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 style="color: rgb(204, 204, 204);">02.</h2>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 style="color: rgb(204, 204, 204);">03.</h2>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-03.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-38" style="letter-spacing: 1px;">THE PROCESS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p class="size-42" style="line-height: 1; font-weight: bold;">01.</p>' .
            '\n<h2 class="size-28">STEP ONE</h2>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/workplace-1245776_1280-oxBIU1.jpg" alt="">' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/desk-office-hero-workspace-(1)-V8F292.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<p class="size-42" style="line-height: 1; font-weight: bold;">02.</p>' .
            '\n<h2 class="size-28">STEP TWO</h2>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Vivamus leo ante, consectetur sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-04.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48" style="text-align:center; letter-spacing: 4px; text-transform: uppercase;">HOW WE WORK</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-arrow-right.png">' .
            _tabs(1) . '<h3 class="size-28">STEP 1</h3>' .
            _tabs(1) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-arrow-right.png">' .
            _tabs(1) . '<h3 class="size-28">STEP 2</h3>' .
            _tabs(1) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="list">' .
            _tabs(1) . '<img src="/cb/assets/icons/ion-ios-arrow-right.png">' .
            _tabs(1) . '<h3 class="size-28">STEP 2</h3>' .
            _tabs(1) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-05.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-46" style="letter-spacing: 2px;">HOW IT WORKS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:90px;height:90px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">1</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step One</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:90px;height:90px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">2</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step Two</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:90px;height:90px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">3</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step Three</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-06.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-46" style="letter-spacing: 2px;">HOW IT WORKS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:90px;height:90px;padding:10px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">1</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step One</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:90px;height:90px;padding:10px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">2</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step Two</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:90px;height:90px;padding:10px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-42" style="margin: 0px; font-weight: bold;">3</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step Three</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-07.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-21" style="color: #d4d4d4; font-family: \\\'Georgia\\\', serif;"><i>Discover</i></p>' .
            '\n<h1 class="size-48 is-title1-48 is-title-bold">OUR WORK STEPS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h3 class="size-24"><img src="/cb/assets/icons/ion-ios-chatboxes-outline.png" style="width: 24px;margin: 0;"> &nbsp;STEP 01</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-24"><img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 24px;margin: 0;"> &nbsp;STEP 2</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-24"><img src="/cb/assets/icons/ion-paper-airplane.png" style="width: 24px;margin: 0;"> &nbsp;STEP 3</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-08.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-60">HOW</h1>' .
            '\n<h3 class="size-24">Step One</h3>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.8;">Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-60">IT</h1>' .
            '\n<h3 class="size-24">Step Two</h3>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.8;">Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-60">WORKS</h1>' .
            '\n<h3 class="size-24">Step Three</h3>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.8;">Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-09.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-50" style="letter-spacing: 2px; text-transform: uppercase;">Timeline Process</h1>' .
            '\n<p style="letter-spacing: 4px; text-transform: uppercase;">Discover How We Work</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<h3 class="size-18" style="border: 2px solid rgb(0, 0, 0); padding: 10px; display: inline-block; letter-spacing: 3px;">STEP ONE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<h3 class="size-18" style="border: 2px solid rgb(0, 0, 0); padding: 10px; display: inline-block; letter-spacing: 3px;">STEP TWO</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<h3 class="size-18" style="border: 2px solid rgb(0, 0, 0); padding: 10px; display: inline-block; letter-spacing: 3px;">STEP THREE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-10.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">THE PROCESS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-android-bulb.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 1</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-compose-outline.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 2</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-code.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 3</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:70px;height:70px;padding:15px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-monitor-outline.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 4</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-11.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">THE PROCESS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:70px;height:70px;padding:15px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-android-bulb-white.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 1</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:70px;height:70px;padding:15px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-compose-outline-white.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 2</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:70px;height:70px;padding:15px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-code-white.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 3</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text" style="width:70px;height:70px;padding:15px;background:#000;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<img src="/cb/assets/icons/ion-ios-monitor-outline-white.png" style="width: 24px;">' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 style="margin-top:1.5em">Step 4</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-12.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h2 class="size-64" style="letter-spacing: 2px;">Timeline Process</h2>' .
            '\n<p class="size-21" style="letter-spacing: 1px;">Discover How We Work</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<p class="size-50 default-font1" style="line-height:1.3">01</p>' .
            '\n<h3 class="size-18">STEP ONE</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<p class="size-50 default-font1" style="line-height:1.3">02</p>' .
            '\n<h3 class="size-18">STEP TWO</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<p class="size-50 default-font1" style="line-height:1.3">03</p>' .
            '\n<h3 class="size-18">STEP THREE</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-13.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 style="letter-spacing: 3px;">WORK STEPS</h1>' .
            '\n<p style="border-bottom: 2px solid #333; width: 40px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-80" style="text-align: center;">01</h1>' .
            '\n<p style="text-align: left;">Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80" style="text-align: center;">02</h1>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-80" style="text-align: center;">03</h1>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Vivamus leo ante, dolor sit amet vel.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-14.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-18" style="font-style: italic;">Discover</p>' .
            '\n<h1 class="size-46" style="letter-spacing: 4px;">HOW WE WORK</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-android-bulb-red.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP ONE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-ios-compose-outline-red.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP TWO</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-gear-b-red.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP THREE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-15.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p style="letter-spacing: 2px;">STEP ONE</p>' .
            '\n<h1 class="size-42" style="font-weight: bold;">Discovery</h1>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-BwYjC1.jpg" alt=""></div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-FrSUb2.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<p style="letter-spacing: 2px;">STEP TWO</p>' .
            '\n<h1 class="size-42" style="font-weight: bold;">Design and Development</h1>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/steps-16.png',
        'category' => '106',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full" style="text-align: center;">' .
            '<h1 style="letter-spacing: 2px;">THIS IS HOW WE WORK</h1>' .
            '\n<p style="border-bottom: 2px solid #333; width: 70px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-android-clipboard.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP ONE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-gear-b.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP TWO</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/icons/ion-paper-airplane.png" style="width: 48px;">' .
            '\n<h3 class="size-21">STEP THREE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

];

$pricings = [

    [
        'thumbnail' => 'preview/pricing-01.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="letter-spacing: 1px;">CHOOSE YOUR PLAN</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-76" style="color: rgb(222, 222, 222); line-height: 1; font-weight: bold;">01</h1>' .
            '\n<h3 class="size-24" style="font-weight: bold">LITE / $33</h3>' .
            '\n<p style="border-bottom: 2.5px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n<div style="margin:1.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-76" style="color: rgb(222, 222, 222); line-height: 1; font-weight: bold;">02</h1>' .
            '\n<h3 class="size-24" style="font-weight: bold">ADVANCED / $59</h3>' .
            '\n<p style="border-bottom: 2.5px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n<div style="margin:1.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-76" style="color: rgb(222, 222, 222); line-height: 1; font-weight: bold;">03</h1>' .
            '\n<h3 class="size-24" style="font-weight: bold">ULTIMATE / $77</h3>' .
            '\n<p style="border-bottom: 2.5px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            '\n<div style="margin:1.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-02.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-38" style="letter-spacing: 2px;">SIMPLE PRICING</h1>' .
            '\n<p style="border-bottom: 2px solid #000; width: 60px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24 is-title-lite">STANDARD</h3>' .
            _tabs(2) . '<p style="color: #e74c3c; font-size: 24px; line-height: 1.4">$<span class="size-64" style="color: #e74c3c">29</span>/mo</p>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24 is-title-lite">DELUXE</h3>' .
            _tabs(2) . '<p style="color: #e74c3c; font-size: 24px; line-height: 1.4">$<span class="size-64" style="color: #e74c3c">59</span>/mo</p>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24 is-title-lite">ULTIMATE</h3>' .
            _tabs(2) . '<p style="color: #e74c3c; font-size: 24px; line-height: 1.4">$<span class="size-64" style="color: #e74c3c">79</span>/mo</p>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Buy Now</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-04.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 1px;">SUBSCRIPTION PLANS</h1>' .
            '\n<p>Choose the right plan that works for you.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-76" style="color: rgb(204, 204, 204); line-height: 1;">01</h1>' .
            _tabs(2) . '<h3 class="size-24">BASIC / FREE</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-light-text shadow-1" style="width:calc(100%);background-color: #27ae60">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-76" style="line-height:1">02</h1>' .
            _tabs(2) . '<h3 class="size-24">DELUXE / $77</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-light-text shadow-1" style="width:calc(100%);background-color: #f39c12">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-76" style="line-height:1">03</h1>' .
            _tabs(2) . '<h3 class="size-24">PREMIUM / $89</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-05.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 default-font2" style="letter-spacing: 2px;">PRICING PLANS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h2 class="size-64">$31</h2>' .
            '\n<p class="size-16 default-font1">MONTHLY</p>' .
            '\n<h3 class="size-24 default-font2" style="line-height: 2; letter-spacing: 2px;">STANDARD</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:1.5em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-64">$57</h2>' .
            '\n<p class="size-16 default-font1">MONTHLY</p>' .
            '\n<h3 class="size-24 default-font2" style="line-height: 2; letter-spacing: 2px;">PREMIUM</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:1.5em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-64">$62</h2>' .
            '\n<p class="size-16 default-font1">MONTHLY</p>' .
            '\n<h3 class="size-24 default-font2" style="line-height: 2; letter-spacing: 2px;">ULTIMATE</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:1.5em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-06.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 1px;">PRICING PLANS</h1>' .
            '<p>Fair Prices. Excellent Services.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<div class="is-card is-card-circle is-light-text card" style="width:90px;height:90px;padding:15px;margin-top:20px;background-color: #2980b9;">' .
            _tabs(3) . '<div class="is-card-content-centered">' .
            _tabs(4) . '<p class="size-42" style="margin:0; color: #fff">$<b style="color: #fff">55</b></p>' .
            _tabs(3) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '<h3 class="size-24 is-title-lite" style="margin-top:25px">STANDARD</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Purchase</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<div class="is-card is-card-circle is-light-text card" style="width:90px;height:90px;padding:15px;margin-top:20px;background-color: #c0392b">' .
            _tabs(3) . '<div class="is-card-content-centered">' .
            _tabs(4) . '<p class="size-42" style="margin:0; color: #fff">$<b style="color: #fff">67</b></p>' .
            _tabs(3) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '<h3 class="size-24 is-title-lite" style="margin-top:25px">DELUXE</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Purchase</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<div class="is-card is-card-circle is-light-text card" style="width:90px;height:90px;padding:15px;margin-top:20px;background-color: #8e44ad">' .
            _tabs(3) . '<div class="is-card-content-centered">' .
            _tabs(4) . '<p class="size-42" style="margin:0; color: #fff">$<b style="color: #fff">72</b></p>' .
            _tabs(3) . '</div>' .
            _tabs(2) . '</div>' .
            _tabs(2) . '<h3 class="size-24 is-title-lite" style="margin-top:25px">PREMIUM</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Purchase</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-07.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-38">PLANS THAT MEET YOUR NEEDS</h1>' .
            '\n<p>Fair Prices. Excellent Services.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-28 is-title-lite">BASIC</h1>' .
            _tabs(2) . '<p style="border-bottom: 2px solid #333; width: 30px; display: inline-block; margin-top: 0"></p>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever.</p>' .
            _tabs(2) . '<h4>$ <span class="size-64 is-title-bold" style="font-weight: 600">39</span></h4>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-28 is-title-lite">ADVANCED</h1>' .
            _tabs(2) . '<p style="border-bottom: 2px solid #333; width: 30px; display: inline-block; margin-top: 0"></p>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever.</p>' .
            _tabs(2) . '<h4>$ <span class="size-64 is-title-bold" style="font-weight: 600">59</span></h4>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-28 is-title-lite">ULTIMATE</h1>' .
            _tabs(2) . '<p style="border-bottom: 2px solid #333; width: 30px; display: inline-block; margin-top: 0"></p>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever.</p>' .
            _tabs(2) . '<h4>$ <span class="size-64 is-title-bold" style="font-weight: 600">79</span></h4>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-08.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="letter-spacing: 1px;">Plans That Meet Your Needs</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h1 class="size-76" style="color: rgb(222, 222, 222); font-weight: bold;">01</h1>' .
            '\n<h3 class="size-24" style="font-weight: bold">BASIC / <span style="color: rgb(27, 131, 223);">$55</span></h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div style="margin:1.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<h1 class="size-76" style="color: rgb(222, 222, 222); font-weight: bold;">02</h1>' .
            '\n<h3 class="size-24" style="font-weight: bold">PREMIUM / <span style="color: rgb(27, 131, 223);">$77</span></h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '\n<div style="margin:1.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-09.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48">Pricing Plans</h1>' .
            '\n<p>Choose the right plan that works for you. No hidden fees.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-60" style="font-weight: bold;">$17</h1>' .
            _tabs(2) . '<h2 class="size-28">BASIC</h2>' .
            _tabs(2) . '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-small is-btn-ghost1 is-upper edit">Buy Now</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:30px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-60" style="font-weight: bold;">$29</h1>' .
            _tabs(2) . '<h2 class="size-28">PREMIUM</h2>' .
            _tabs(2) . '<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-small is-btn-ghost1 is-upper edit">Buy Now</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-12.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">CHOOSE YOUR PLAN</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-64 is-title1-64 is-title-bold"><span style="font-size:30px">$</span>19</h1>' .
            '\n<h3 class="size-18 is-title1-18 is-title-bold">Per Month</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-64 is-title1-64 is-title-bold"><span style="font-size:30px">$</span>27</h1>' .
            '\n<h3 class="size-18 is-title1-18 is-title-bold">Per Month</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-64 is-title1-64 is-title-bold"><span style="font-size:30px">$</span>39</h1>' .
            '\n<h3 class="size-18 is-title1-18 is-title-bold">Per Month</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],


    [
        'thumbnail' => 'preview/pricing-10.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 default-font2" style="letter-spacing: 2px;">SIMPLE PRICING</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h2 class="size-64">$31</h2>' .
            '\n<p class="size-16 default-font1">MONTHLY</p>' .
            '\n<h3 class="size-28 default-font2" style="line-height: 2;letter-spacing: 2px;">STANDARD</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:2.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<h2 class="size-64">$57</h2>' .
            '\n<p class="size-16 default-font1">MONTHLY</p>' .
            '\n<h3 class="size-28 default-font2" style="line-height: 2;letter-spacing: 2px;">ULTIMATE </h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:2.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-15.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">PRICING PLANS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3>BASIC</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="font-size: 24px; line-height: 1.4">$<span class="size-64 is-title-lite">34</span>/ month</p>' .
            '\n<div style="margin:2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Choose Plan</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<h3>PREMIUM</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="font-size: 24px; line-height: 1.4">$<span class="size-64 is-title-lite">57</span>/ month</p>' .
            '\n<div style="margin:2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Choose Plan</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-16.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">PRICING PLANS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h1 class="size-48 is-title1-48 is-title-bold" style="color: rgb(204, 204, 204);">FREE</h1>' .
            '\n<h3 class="size-21">Try New Features</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-small is-btn-ghost1 is-upper edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-48 is-title1-48 is-title-bold">$19</h1>' .
            '\n<h3 class="size-21">Monthly</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-small is-btn-ghost1 is-upper edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<h1 class="size-48 is-title1-48 is-title-bold">$227</h1>' .
            '\n<h3 class="size-21">Yearly</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '\n<div style="margin:2em 0 1em">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-small is-btn-ghost1 is-upper edit">Buy Now</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-17.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42">PRICING PLANS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-32 is-title-lite">BASIC</h1>' .
            _tabs(2) . '<h4>$ <span class="size-76">39</span></h4>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-light-text shadow-1" style="width:calc(100%);background-color: #f39c12">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-32 is-title-lite">PREMIUM</h1>' .
            _tabs(2) . '<h4>$ <span class="size-76">59</span></h4>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-32 is-title-lite">ULTIMATE</h1>' .
            _tabs(2) . '<h4>$ <span class="size-76">99</span></h4>' .
            _tabs(2) . '<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            _tabs(2) . '<div style="margin:1.2em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Select Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-18.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="letter-spacing: 2px;">SUBSCRIPTION  PLANS</h1>' .
            '\n<p style="letter-spacing: 1px;">We make everything way easier for you.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-35" style="letter-spacing: 2px;">STARTER</h1>' .
            _tabs(2) . '<h3 class="size-18" style="color: rgb(119, 119, 119); letter-spacing: 2px;">$19 / MONTH</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.5em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Choose Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-35" style="letter-spacing: 2px;">PRO</h1>' .
            _tabs(2) . '<h3 class="size-18" style="color: rgb(119, 119, 119); letter-spacing: 2px;">$59 / MONTH</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.5em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Choose Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h1 class="size-35" style="letter-spacing: 2px;">BUSINESS</h1>' .
            _tabs(2) . '<h3 class="size-18" style="color: rgb(119, 119, 119); letter-spacing: 2px;">$79 / MONTH</h3>' .
            _tabs(2) . '<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div style="margin:1.5em 0">' .
            _tabs(3) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Choose Plan</a>' .
            _tabs(2) . '</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-19.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48" style="letter-spacing: 4px; text-align: center;">OUR PLANS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24" style="line-height: 2;letter-spacing: 2px;">STANDARD</h3>' .
            _tabs(2) . '<h3 class="size-60" style="font-weight: bold;">$27</h3>' .
            _tabs(2) . '<h3 class="size-18" style="font-weight: bold;">Per Month</h3>' .
            _tabs(2) . '<p style="margin-top:0">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div><a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Get Started</a></div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24" style="line-height: 2;letter-spacing: 2px;">DELUXE</h3>' .
            _tabs(2) . '<h3 class="size-60" style="font-weight: bold;">$39</h3>' .
            _tabs(2) . '<h3 class="size-18" style="font-weight: bold;">Per Month</h3>' .
            _tabs(2) . '<p style="margin-top:0">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div><a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Get Started</a></div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<div class="is-card is-dark-text shadow-1" style="width:calc(100%);">' .
            _tabs(1) . '<div style="padding:25px;width:100%;box-sizing:border-box;text-align:center;">' .
            _tabs(2) . '<h3 class="size-24" style="line-height: 2;letter-spacing: 2px;">ULTIMATE</h3>' .
            _tabs(2) . '<h3 class="size-60" style="font-weight: bold;">$55</h3>' .
            _tabs(2) . '<h3 class="size-18" style="font-weight: bold;">Per Month</h3>' .
            _tabs(2) . '<p style="margin-top:0">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            _tabs(2) . '<div><a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Get Started</a></div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/pricing-20.png',
        'category' => '107',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-28" style="line-height: 2;letter-spacing: 2px;">BASIC <span style="color: rgb(149, 149, 149);">PLAN</span></h3>' .
            '\n<h3>$ <span class="size-64">39</span></h3>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Lorem ipsum dolor sit amet, vivamus ante.</p>' .
            '\n<div style="margin:2.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-28" style="line-height: 2;letter-spacing: 2px;">PRO <span style="color: rgb(149, 149, 149);">PLAN</span></h3>' .
            '\n<h3>$ <span style="font-size: 64px;">79</span></h3>' .
            '\n<p>Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s. Lorem ipsum dolor sit amet, vivamus ante.</p>' .
            '\n<div style="margin:2.2em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Get Started</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

];

$skills = [

    [
        'thumbnail' => 'preview/skills-01.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16">DISCOVER HOW GOOD WE ARE</p>' .
            '\n<h1 class="size-64" style="letter-spacing: 5px;">TEAM SKILLS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h2 class="size-64">85%</h2>' .
            '\n<h3 class="size-18 default-font2">WEB DESIGN</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-64">98%</h2>' .
            '\n<h3 class="size-18 default-font2">WEB DEVELOPMENT</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-64">77%</h2>' .
            '\n<h3 class="size-18 default-font2">PHOTOSHOP</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-02.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="default-font2 size-64" style="letter-spacing: 6px;">PROFESSIONAL SKILLS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:70px;height:70px;padding:15px;background: #6ab04c;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-28 default-font1" style="margin: 0">87%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-21" style="margin-top: 25px;">WEB DESIGN </h3>' .
            '\n<p style="line-height: 1.8">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:70px;height:70px;padding:15px;background: #e84393;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-28 default-font1" style="margin: 0">92%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-21" style="margin-top: 25px;">WEB DEVELOPMENT </h3>' .
            '\n<p style="line-height: 1.8">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:70px;height:70px;padding:15px;background: #0984e3;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-28 default-font1" style="margin: 0">99%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-21" style="margin-top: 25px;">CUSTOMER SUPPORT</h3>' .
            '\n<p style="line-height: 1.8">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-03.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48"><b>WORK <span style="color: #888888">SKILLS</span></b></h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 90px; height: 90px; line-height: 90px; border-radius: 50%; border: 3px solid #888888; display: inline-block;">' .
            _tabs(1) . '<p class="size-28" style="padding: 10px; color: rgb(136, 136, 136); line-height: 1.3; font-weight: bold;">93%</p>' .
            '\n</div>' .
            '\n<h3 class="size-24">Design / Graphics</h3>' .
            '\n<p>Lorem Ipsum is dummy text of printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 90px; height: 90px; line-height: 90px; border-radius: 50%; border: 3px solid #888888; display: inline-block;">' .
            _tabs(1) . '<p class="size-28" style="padding: 10px; color: rgb(136, 136, 136); line-height: 1.3; font-weight: bold;">85%</p>' .
            '\n</div>' .
            '\n<h3 class="size-24">HTML & CSS</h3>' .
            '\n<p>Lorem Ipsum is dummy text of printing industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-20"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 90px; height: 90px; line-height: 90px; border-radius: 50%; border: 3px solid #888888; display: inline-block;">' .
            _tabs(1) . '<p class="size-28" style="padding: 10px; color: rgb(136, 136, 136); line-height: 1.3; font-weight: bold;">77%</p>' .
            '\n</div>' .
            '\n<h3 class="size-24">WordPress</h3>' .
            '\n<p>Lorem Ipsum is dummy text of printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<div style="text-align: center; width: 90px; height: 90px; line-height: 90px; border-radius: 50%; border: 3px solid #888888; display: inline-block;">' .
            _tabs(1) . '<p class="size-28" style="padding: 10px; color: rgb(136, 136, 136); line-height: 1.3; font-weight: bold;">89%</p>' .
            '\n</div>' .
            '\n<h3 class="size-24">Customer Support</h3>' .
            '\n<p>Lorem Ipsum is dummy text of printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-04.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="display">' .
            '<h1 style="letter-spacing: 25px;">PROFESSIONAL SKILLS</h1>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:60px;height:60px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<div class="size-24">91%</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h4 style="line-height: 2.2;">HTML & CSS</h4>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.7">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:60px;height:60px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<div class="size-24">83%</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h4 style="line-height: 2.2;">PHP</h4>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.7">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:60px;height:60px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<div class="size-24">72%</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h4 style="line-height: 2.2;">JavaScript</h4>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.7">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-dark-text shadow-1" style="width:60px;height:60px;padding:10px">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<div class="size-24">85%</div>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h4 style="line-height: 2.2;">Photoshop</h4>' .
            '\n<p style="color: rgb(136, 136, 136); line-height: 1.7">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-05.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">OUR <span style="color: #888888">SKILLS</span></h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline-orange.png" style="width: 28px;">' .
            '\n<h3 class="size-21" style="color: #888888">WEB DESIGN</h3>' .
            '\n<p style="border-bottom: 2px solid #f39c12; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-code-orange.png" style="width: 28px;">' .
            '\n<h3 class="size-21" style="color: #888888">HTML & CSS</h3>' .
            '\n<p style="border-bottom: 2px solid #f39c12; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-android-globe-orange.png" style="width: 28px;">' .
            '\n<h3 class="size-21" style="color: #888888">BRANDING</h3>' .
            '\n<p style="border-bottom: 2px solid #f39c12; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-06.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 default-font2" style="letter-spacing: 2px;">PROFESSIONAL SKILLS</h1>' .
            '\n<p style="border-bottom: 2px solid #333; width: 50px; display: inline-block; margin: 0"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:100px;height:100px;padding:15px;background: #f0932b;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-32 default-font1">92%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-18 default-font2" style="margin-top: 25px;">CREATIVE DESIGN</h3>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:100px;height:100px;padding:15px;background:  #6ab04c;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-32 default-font1">80%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-18 default-font2" style="margin-top: 25px;">PROGRAMMING</h3>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:100px;height:100px;padding:15px;background: #eb4d4b;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-32 default-font1">77%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-18 default-font2" style="margin-top: 25px;">PHOTOGRAPHY</h3>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<div class="is-card is-card-circle is-light-text shadow-1" style="width:100px;height:100px;padding:15px;background: #0984e3;">' .
            _tabs(1) . '<div class="is-card-content-centered">' .
            _tabs(2) . '<p class="size-32 default-font1">83%</p>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '\n<h3 class="size-18 default-font2" style="margin-top: 25px;">PHOTOSHOP</h3>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-07.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<p class="size-16" style="letter-spacing: 1px;">DISCOVER HOW GOOD WE ARE</p>' .
            '\n<h1 class="size-54" style="letter-spacing: 5px;">TEAM SKILLS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h2 class="size-60">85%</h2>' .
            '\n<h3 class="size-18 default-font2">WEB DESIGN</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h2 class="size-60">98%</h2>' .
            '\n<h3 class="size-18 default-font2">WEB DEVELOPMENT</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h2 class="size-60">77%</h2>' .
            '\n<h3 class="size-18 default-font2">PHOTOSHOP</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h2 class="size-60">83%</h2>' .
            '\n<h3 class="size-18 default-font2">ANIMATION</h3>' .
            '\n<p class="size-16" style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-08.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">OUR FINEST SKILLS</h1>' .
            '\n<p class="size-21">We create things beautifully.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<div style="text-align: center; width: 120px; height: 120px; line-height: 120px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:12px;">80%</p>' .
            '\n</div>' .
            '\n<p>DESIGN</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div style="text-align: center; width: 120px; height: 120px; line-height: 120px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:12px;">75%</p>' .
            '\n</div>' .
            '\n<p>MARKETING</p>' .
            '</div>' .
            '<div class="column third center">' .
            '<div style="text-align: center; width: 120px; height: 120px; line-height: 120px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:12px;">92%</p>' .
            '\n</div>' .
            '\n<p>DEVELOPMENT</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-09.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-64" style="letter-spacing: 11px; font-weight: 400;">TEAM SKILLS</h1>' .
            '\n<p class="size-16">DISCOVER HOW GOOD WE ARE</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 40px;margin: 8px 0 0 0;">' .
            '\n<p class="size-16">WEB DESIGN</p>' .
            '\n<p class="size-64 default-font1" style="font-weight: 400; line-height: 1.2">87%</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 40px;margin: 8px 0 0 0;">' .
            '\n<p class="size-16">WEB DEVELOPMENT</p>' .
            '\n<p class="size-64 default-font1" style="font-weight: 400; line-height: 1.2">92%</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/icons/ion-camera.png" style="width: 40px;margin: 8px 0 0 0;">' .
            '\n<p class="size-16">PHOTOGRAPHY</p>' .
            '\n<p class="size-64 default-font1" style="font-weight: 400; line-height: 1.2">77%</p>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/icons/ion-ios-world-outline.png" style="width: 40px;margin: 8px 0 0 0;">' .
            '\n<p class="size-16">BRANDING</p>' .
            '\n<p class="size-64 default-font1" style="font-weight: 400; line-height: 1.2">80%</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-10.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-42" style="text-align: center; letter-spacing: 3px;">OUR CAPABILITIES</h1>' .
            '\n<p style="letter-spacing: 1px;">SEE WHAT WE ARE GOOD AT</p>' .
            '\n<p style="border-bottom: 2px solid #000; width: 50px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column center fourth">' .
            '<div style="text-align: center; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:4px;">90%</p>' .
            '\n</div>' .
            '\n<p>WEB DESIGN</p>' .
            '</div>' .
            '<div class="column center fourth">' .
            '<div style="text-align: center; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:4px;">78%</p>' .
            '\n</div>' .
            '\n<p>GRAPHIC DESIGN</p>' .
            '</div>' .
            '<div class="column center fourth">' .
            '<div style="text-align: center; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:4px;">70%</p>' .
            '\n</div>' .
            '\n<p>PHOTOGRAPHY</p>' .
            '</div>' .
            '<div class="column center fourth">' .
            '<div style="text-align: center; width: 100px; height: 100px; line-height: 100px; border-radius: 50%; border: 3px solid #333; display: inline-block;">' .
            _tabs(1) . '<p class="size-32 is-title1-32 is-title-bold" style="padding:4px;">82%</p>' .
            '\n</div>' .
            '\n<p>MARKETING</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-11.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">TEAM <span style="color: #888888">SKILLS</span></h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<div style="padding-right:30px">' .
            _tabs(1) . '<img src="/cb/assets/minimalist-blocks/images/-sGkY41.jpg" alt="">' .
            '\n</div>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline-orange.png" style="width: 28px;margin: 16px 0;">' .
            '\n<h3 class="size-21" style="color: #888888;letter-spacing: 1px;">GRAPHIC DESIGN</h3>' .
            '\n<p style="border-bottom: 2px solid #f37312; width: 50px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-code-orange.png" style="width: 28px;margin: 16px 0;">' .
            '\n<h3 class="size-21" style="color: #888888; letter-spacing: 1px;">WEB DEVELOPMENT</h3>' .
            '\n<p style="border-bottom: 2px solid #f37312; width: 50px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-12.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="text-align: center; letter-spacing: 3px;">OUR CAPABILITIES</h1>' .
            '\n<p style="letter-spacing: 1px; text-align: center;">SEE WHAT WE ARE GOOD AT</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column center third">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 24px;margin: 4px 0 0 0;">' .
            '\n<p class="size-64" style="line-height: 1.2">95%</p>' .
            '\n<p style="letter-spacing: 2px;">WEB DESIGN</p>' .
            '</div>' .
            '<div class="column center third">' .
            '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 24px;margin: 4px 0 0 0;">' .
            '\n<p class="size-64" style="line-height: 1.2">90%</p>' .
            '\n<p style="letter-spacing: 2px;">WEB DEVELOPMENT</p>' .
            '</div>' .
            '<div class="column center third">' .
            '<img src="/cb/assets/icons/ion-camera.png" style="width: 24px;margin: 4px 0 0 0;">' .
            '\n<p class="size-64" style="line-height: 1.2">87%</p>' .
            '\n<p style="letter-spacing: 2px;">PHOTOGRAPHY</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-13.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">TEAM <span style="color: #888888">SKILLS</span></h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-android-bulb.png" style="width: 40px;">' .
            '\n<h3 class="size-24"><b><span style="color: #bdbdbd;">90%</span></b>&nbsp; CONCEPT & IDEAS</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 40px;">' .
            '<h3 class="size-24"><b><span style="color: #bdbdbd;">88%</span></b>&nbsp; WEB DESIGN</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 40px;">' .
            '\n<h3 class="size-24"><b><span style="color: #bdbdbd;">85%</span></b>&nbsp; WEB DEVELOPMENT</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-monitor-outline.png" style="width: 40px;">' .
            '\n<h3 class="size-24"><b><span style="color: #bdbdbd;">77%</span></b>&nbsp; BRANDING</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-14.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">TEAM SKILLS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-android-bulb.png" style="width: 32px;margin: 10px 0;">' .
            '\n<h3 class="size-21"><b><span style="color: #bdbdbd;">90%</span></b>&nbsp; CONCEPT IDEAS</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-ios-heart-outline.png" style="width: 32px;margin: 10px 0;">' .
            '\n<h3 class="size-21"><b><span style="color: #bdbdbd;">88%</span></b>&nbsp; WEB DESIGN</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<img src="/cb/assets/icons/ion-ios-compose-outline.png" style="width: 32px;margin: 10px 0;">' .
            '\n<h3 class="size-21"><b><span style="color: #bdbdbd;">88%</span></b>&nbsp; PHOTOGRAPHY</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-15.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p class="size-48 is-title1-48 is-title-bold" style="line-height: 1.4">89%</p>' .
            '\n<h3 class="size-24" style="letter-spacing: 2px;">WEB DEVELOPMENT</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-UPno91.jpg" alt="">' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-lkx4s2.jpg" alt="">' .
            '</div>' .
            '<div class="column half">' .
            '<p class="size-48 is-title1-48 is-title-bold" style="line-height: 1.4">95%</p>' .
            '\n<h3 class="size-24" style="letter-spacing: 2px;">WEB DESIGN</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<p class="size-48 is-title1-48 is-title-bold" style="line-height: 1.4">79%</p>' .
            '\n<h3 class="size-24" style="letter-spacing: 2px;">GRAPHIC DESIGN</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/minimalist-blocks/images/-sJf643.jpg" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/skills-16.png',
        'category' => '108',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="letter-spacing: 3px; font-weight: bold;">OUR <span style="color: rgb(149, 149, 149);">SKILLS</span></h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h2 class="size-48">91%</h2>' .
            '\n<h3 class="size-21">CREATIVE DESIGN</h3>' .
            '\n<p style="color: rgb(149, 149, 149);">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-48">85%</h2>' .
            '\n<h3 class="size-21">PROGRAMMING</h3>' .
            '\n<p style="color: rgb(149, 149, 149);">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h2 class="size-48">80%</h2>' .
            '\n<h3 class="size-21">BRANDING</h3>' .
            '\n<p style="color: rgb(149, 149, 149);">Lorem Ipsum is dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

];

$partners = [

    [
        'thumbnail' => 'preview/partners-03.png',
        'category' => '111',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48" style="letter-spacing: 5px; font-weight: 400;">OUR CLIENTS</h1>' .
            '\n<p class="size-16" style="letter-spacing: 1px; color: rgb(136, 136, 136);">We are globally trusted by the world\\\'s best names.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/creative.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/light-studio.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/infinitech.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/design-firm.png" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/partners-05.png',
        'category' => '111',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 style="letter-spacing: 7px;">OUR PARTNERS</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="center" style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/sitepro.png" alt="">' .
            '</div>' .
            '<div class="center" style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/steady.png" alt="">' .
            '</div>' .
            '<div class="center" style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/creative.png" alt="">' .
            '</div>' .
            '<div class="center" style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/light-studio.png" alt="">' .
            '</div>' .
            '<div class="center" style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/infinitech.png" alt="">' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/partners-06.png',
        'category' => '111',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48" style="font-weight: bold; text-transform: uppercase;">Our Clients</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/creative.png" alt="">' .
            '</div>' .
            '<div style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/steady.png" alt="">' .
            '</div>' .
            '<div style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/light-studio.png" alt="">' .
            '</div>' .
            '<div style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/sitepro.png" alt="">' .
            '</div>' .
            '<div style="display:inline-block;width:18%">' .
            '<img src="/cb/assets/minimalist-blocks/images/design-firm.png" alt="">' .
            '</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/partners-01.png',
        'category' => '111',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">OUR PARTNERS</h1>' .
            '\n<p class="size-21">We are globally recognized and trusted by the world\\\'s best names.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/creative.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/light-studio.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/sitepro.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/infinitech.png" alt="">' .
            '</div>' .
            '</div>'
    ],

];

$asfeatured = [

    [
        'thumbnail' => 'preview/asfeaturedon-01.png',
        'category' => '112',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column fourth">' .
            '<h2 class="size-24">AS FEATURED ON</h2>' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/onesight.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/mmedia.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/digitalmag.png" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/asfeaturedon-02.png',
        'category' => '112',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">As featured on</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/digitalmag.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/upclick.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/onesight.png" alt="">' .
            '</div>' .
            '<div class="column fourth">' .
            '<img src="/cb/assets/minimalist-blocks/images/mmedia.png" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/asfeaturedon-03.png',
        'category' => '112',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">AS FEATURED ON</h1>' .
            '\n<p class="size-21">Lorem Ipsum is simply dummy text of the printing industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/upclick.png" alt="">' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/mmedia.png" alt="">' .
            '</div>' .
            '<div class="column third center">' .
            '<img src="/cb/assets/minimalist-blocks/images/worldwide.png" alt="">' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/asfeaturedon-05.png',
        'category' => '112',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48" style="letter-spacing: 7px;">AS FEATURED ON</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-60"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/upclick.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/digitalmag.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/mmedia.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/bbuzz.png" alt="">' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/prosource.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/light-studio.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/nett.png" alt="">' .
            '</div>' .
            '<div class="column fourth center">' .
            '<img src="/cb/assets/minimalist-blocks/images/worldwide.png" alt="">' .
            '</div>' .
            '</div>'
    ],

];

$error_pages = [

    [
        'thumbnail' => 'preview/404-01.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<p class="size-132" style="font-weight: bold; line-height: 1.4">404</p>' .
            '\n<h1>PAGE NOT FOUND</h1>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Back to Home</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-02.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-96 is-title1-96 is-title-bold">404</h1>' .
            '\n<p class="size-24">Oops! The page you\\\'re looking for doesn\\\'t exist.<br>Click the link below to return home.</p>' .
            '\n<p><a href="#">HOMEPAGE</a></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-03.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<i class="icon ion-alert-circled size-64"></i>' .
            '\n<h1 class="size-48 is-title2-48 is-title-lite">Oops, page not found.</h1>' .
            '\n<p>The page you are looking for might have been removed, had its name changed, or temporarily unavailable.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Homepage</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-04.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<i class="icon ion-android-sad size-64"></i>' .
            '\n<h1 class="size-48 is-title1-48 is-title-lite">Something\\\'s wrong here... </h1>' .
            '\n<p class="size-21">The page you requested couldn\\\'t be found. This could be a spelling error in the URL or a removed page.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Back to Home</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-06.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<img src="/cb/assets/minimalist-blocks/images/lost-2747289-ThbrT1.png" alt="">' .
            '\n<p class="size-21" style="letter-spacing: 2px;">Sorry. The page you are looking for could not be found.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div style="margin: 25px 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Back to Home</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-07.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-60" style="letter-spacing: 3px; font-weight: bold">404 ERROR - PAGE NOT FOUND</h1>' .
            '\n<p class="size-21">Sorry, the page could not be found. You might be able to find what you are looking for from the homepage.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div style="margin: 10px 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Back to Home</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-08.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-196" style="letter-spacing: 12px; margin-bottom: 10px">404</h1>' .
            '\n<h3 class="size-32" style="letter-spacing: 8px;">PAGE NOT FOUND</h3>' .
            '\n<p class="size-21">The page you are looking for might have been removed, had its name changed, or is temporarily unavailable.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/404-09.png',
        'category' => '113',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-220" style="letter-spacing: 12px; margin-bottom: 0">404</h1>' .
            '\n<p>We are sorry, the page you are looking for could not be found. This could be a spelling error in the URL or a removed page.</p>' .
            '\n<div style="margin: 35px 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost2 is-upper is-btn-small edit">Contact Us</a> &nbsp;' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Homepage</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

];

$comingsoon = [

    [
        'thumbnail' => 'preview/comingsoon-01.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h3 class="size-32 is-title1-32 is-title-lite">STAY TUNED!</h3>' .
            '\n<h1 class="size-64 is-title1-64 is-title-bold">OUR WEBSITE IS COMING VERY SOON</h1>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<div class="is-social edit social-media-buttons-holder">' .
            _tabs(1) . '<div class="size-21 social-buttons-container">' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link"  href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(1) . '</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-02.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<img src="/cb/assets/icons/ion-android-bicycle.png" style="width: 46px;">' .
            '\n<h1 class="size-80 is-title2-80 is-title-lite">WE ARE COMING SOON</h1>' .
            '\n<p class="size-18">Our website is under construction. We will be here with new awesome site.</p>' .
            '\n<div style="margin: 3em 0">' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost2 is-upper is-btn-small edit">Contact Us</a> &nbsp;' .
            _tabs(1) . '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small edit">Notify Me</a>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-03.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<img src="/cb/assets/icons/ion-laptop.png" style="width: 46px;">' .
            '\n<h1 class="size-48 is-title2-48 is-title-lite">SITE IS UNDER MAINTENANCE </h1>' .
            '\n<p class="size-24">Please check back in sometime.</p>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="is-social edit size-21 social-buttons-container">' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link"  href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-04.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">MAINTENANCE MODE</h1>' .
            '\n<p class="size-24">Our website is under maintenance. Please comeback later.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<p class="size-64 is-title1-64 is-title-bold">90%</p>' .
            '\n<p>COMPLETED</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-05.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-96" style="letter-spacing: 19px; margin-bottom: 10px;">COMING SOON</h1>' .
            '\n<p style="text-transform: uppercase; letter-spacing: 2px;">CHECK BACK SOON FOR THE NEW AND IMPROVED SITE</p>' .
            '\n<div class="spacer height-40"></div>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="is-social edit size-21 social-buttons-container">' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link"  href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '\n</div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-06.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-64" style="letter-spacing: 5px;">OUR SITE IS COMING VERY SOON</h1>' .
            '\n<p>We are currently working on something awesome. We will be here soon.</p>' .
            '\n<div class="social-media-buttons-holder">' .
            '\n<div class="is-social edit size-21 social-buttons-container" style="margin: 30px 0">' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link"  href="https://twitter.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-twitter.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="https://www.facebook.com/"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-social-facebook.png" style="width: 14px; display: inline-block;"></a>' .
            _tabs(2) . '<a style="margin: 10px;" class="social-button-link" href="mailto:you@example.com"><img class="social-button-icon no-image-edit" src="/cb/assets/icons/ion-android-drafts.png" style="width: 14px; display: inline-block;"></a>' .
            '\n</div>' .
            '</div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small edit">Notify Me</a>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-07.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="display">' .
            _tabs(1) . '<h1 class="size-80" style="letter-spacing: 4px; text-align: center; color: rgb(209, 209, 209);">COMING SOON.</h1>' .
            '\n</div>' .
            '\n<p style="text-align: center;">Our website is under construction. We will be here with new awesome site.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="center"><a href="#" class="is-btn is-btn-ghost1 is-upper">Notify Me</a></div>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/comingsoon-08.png',
        'category' => '114',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<p style="border-bottom: 2px solid #b9b6b6;width: 210px;display: inline-block;"></p>' .
            '\n<h1 class="size-42" style="letter-spacing: 3px;">Sorry, our website is currently getting a face lift. Check back soon for the new awesome and improved site.</h1>' .
            '\n<p style="border-bottom: 2px solid #b9b6b6; width: 210px; display: inline-block; margin-top: 20px"></p>' .
            '</div>' .
            '</div>'
    ],

];

$faqs = [

    [
        'thumbnail' => 'preview/faq-01.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-60">FAQs</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-compose-outline.png" style="width: 35px;">' .
            '\n<h3 class="size-24">How do I sign up? </h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-close-outline.png" style="width: 35px;">' .
            '\n<h3 class="size-24">How do I cancel my order?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-color-filter-outline.png" style="width: 35px;">' .
            '\n<h3 class="size-24">What is account limits?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<img src="/cb/assets/icons/ion-ios-gear-outline.png" style="width: 35px;">' .
            '\n<h3 class="size-24">How do I update my settings?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/faq-02.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-48 is-title1-48 is-title-lite">FAQs</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-24">How do I create an account?</h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-24">How do I cancel my order?</h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-24">How do I close my account?</h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-24">How do I update my settings?</h3>' .
            '\n<p style="border-bottom: 2px solid #000; width: 40px; display: inline-block; margin-top:0"></p>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem ipsum dolor sit amet.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/faq-03.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-60" style="font-weight: bold;">FAQs</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h3>How do I sign up?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h3>How do I cancel or change my order?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h3>How do I contact customer support?</h3>' .
            '\n<p>Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry\\\'s standard dummy text ever since the 1500s.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/faq-04.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<h1 class="size-42" style="text-align: center;">Frequently Asked Questions</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h3 class="size-21">HOW DO I CREATE AN ACCOUNT?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21">WHAT\\\'S ACCOUNT LIMITS?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21">HOW DO I CANCEL MY ORDER?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h3 class="size-21">HOW DO I RESET MY PASSWORD?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21">HOW DO I REPORT A BUG?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21">HOW DO I CLOSE MY ACCOUNT?</h3>' .
            '\n<p>Lorem Ipsum is dummy text of the printing and typesetting industry.</p>' .
            '\n<p style="border-bottom: 2px solid #e67e22; width: 45px; display: inline-block;"></p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/faq-05.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48" style="letter-spacing: 2px;">FAQ</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">HOW DO I SIGN UP?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">WHAT\\\'S ACCOUNT LIMIT?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">HOW DO I CONTACT SUPPORT?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">HOW DO I UPDATE MY SETTINGS?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">HOW DO I REPORT AN ISSUE?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '<div class="column half">' .
            '<h3 class="size-21 default-font2">HOW DO I CHANGE MY ORDER?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum dolor sit amet, consectetur elit.</p>' .
            '</div>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/faq-06.png',
        'category' => '115',
        'html' =>
            '<div class="row clearfix">' .
            '<div class="column full center">' .
            '<h1 class="size-48" style="letter-spacing: 2px;">FAQ</h1>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column full">' .
            '<div class="spacer height-40"></div>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I CREATE AN ACCOUNT? </h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I UPDATE MY SETTINGS?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I CHANGE MY PASSWORD?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>' .
            '<div class="row clearfix">' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I CANCEL MY ORDER?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I CLOSE MY ACCOUNT?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '<div class="column third">' .
            '<h3 class="size-21 default-font2">HOW DO I CONTACT CUSTOMER SERVICE?</h3>' .
            '\n<p style="color: rgb(136, 136, 136);">Lorem Ipsum is simply dummy text of the printing industry.</p>' .
            '</div>' .
            '</div>'
    ],


];

$buttons = [

    [
        'thumbnail' => 'preview/button-01.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper">Read More</a> &nbsp;' .
            '\n<a href="#" class="is-btn is-btn-ghost1 is-upper">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-02.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper">Read More</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-03.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-04.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-btn-small">Read More</a> &nbsp;' .
            '\n<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-05.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-btn-small">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-06.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-btn-small">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-07.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-rounded-30">Read More</a> &nbsp;' .
            '\n<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-08.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-rounded-30">Read More</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-09.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-10.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-rounded-30 is-btn-small">Read More</a> &nbsp;' .
            '\n<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Buy Now</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-11.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost2 is-upper is-rounded-30 is-btn-small">Read More</a>' .
            '</div>'
    ],

    [
        'thumbnail' => 'preview/button-12.png',
        'category' => '119',
        'html' =>
            '<div>' .
            '<a href="#" class="is-btn is-btn-ghost1 is-upper is-rounded-30 is-btn-small">Buy Now</a>' .
            '</div>'
    ],


];

$logos = [
    [
        'thumbnail' => 'preview/thumbnail logo-1.jpg',
		'category' => '1',
		'html' => '<div style="width: 100%; max-width: 100%; text-align: left;"><img src="' . $sig_logo_url . '" style="max-width: 100%; width: 150px;" alt="Logo"></div>'
    ],
    [
        'thumbnail' => 'preview/thumbnail logo-2.jpg',
		'category' => '1',
		'html' => '<div style="width: 100%; max-width: 100%; text-align: center;"><img src="' . $sig_logo_url . '" style="max-width: 100%; width: 150px;" alt="Logo"></div>'
    ],
    [
        'thumbnail' => 'preview/thumbnail logo-3.jpg',
		'category' => '1',
		'html' => '<div style="width: 100%; max-width: 100%; text-align: right;"><img src="' . $sig_logo_url . '" style="max-width: 100%; width: 150px;" alt="Logo"></div>'
    ]
];

$reviews = [
    [
        'thumbnail' => 'preview/review2.png',
		'category' => '1013',
		'html' => '\n<div class="submit-review-product row clearfix" product-source="0" style="text-align: center;">' .
        '\n	<div class="column full">' .
        '\n		<img class="no-image-edit" src="/cb/assets/minimalist-blocks/review.jpg" alt="" loading="lazy">' .
        '\n		<h1 style="font-size: 28px; font-weight: 700;">How did you like this item?</h1>' .
        '\n		<h2 style="font-size: 22px; color: rgb(102, 102, 102); font-weight: 500;" contenteditable="false">Product Title</h2>' .
        '\n		<a href="#" title="">' .
        '\n			<img class="no-image-edit" src="/cb/assets/minimalist-blocks/images/0_stars.png" alt="" loading="lazy" style="width: 50%;height: auto;max-width: 50%;object-fit: cover;">' .
        '\n		</a>' .
        '\n	</div>' .
        '\n</div> <!--- end-submit-review-product -->'
    ]
];


$snippets = array_merge($products, $logos, $orders, $cart, $chats, $signatures,
    $basic, $articles,
    $headers, $photos, $profiles, $features,
    $steps, $pricings, $skills, $partners, $asfeatured, $error_pages,
    $comingsoon, $faqs, $buttons, $reviews
);
?>

function _path() {
var scripts = document.querySelectorAll('script[src]');
var currentScript = scripts[scripts.length - 1].src;
var currentScriptChunks = currentScript.split('/');
var currentScriptFile = currentScriptChunks[currentScriptChunks.length - 1];
return currentScript.replace(currentScriptFile, '');
}
var _snippets_path = _path();

var data_basic = {
'snippets': [
@foreach($snippets as $snippet)
    {
    'thumbnail': '{!! $snippet['thumbnail'] !!}',
    'category': '{!! $snippet['category'] !!}',
    'html': '{!! $snippet['html'] !!}'
    },
@endforeach
]};

if(!(window.Glide||parent.Glide)){
for (let i = 0; i < data_basic.snippets.length; i++) {
if (data_basic.snippets[i].glide) {
data_basic.snippets.splice(i, 1);
break;
}
}
}