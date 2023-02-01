<?php

namespace Acelle\Model;

use DOMDocument;
use DOMXPath;
use Acelle\Library\Automation\DynamicWidgetConfig\ProductConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\Products3Config;
use Acelle\ShopifyModel\Product;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Acelle\Helpers\ShopifyHelper;

/**
 * @property integer id
 * @property string created_at
 * @property string updated_at
 * @property string uid
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property integer shopify_id
 * @property string data
 * @property string shopify_title
 * @property boolean submitted_for_review
 * @property boolean review_fetch_complete
 * @property integer units_sold
 */
class ShopifyProduct extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_data = 'data';
    const COLUMN_shopify_title = 'shopify_title';
    const COLUMN_submitted_for_review = 'submitted_for_review';
    const COLUMN_review_fetch_complete = 'review_fetch_complete';
    const COLUMN_units_sold = 'units_sold';

    protected $casts = [
        self::COLUMN_submitted_for_review => 'boolean',
        self::COLUMN_review_fetch_complete => 'boolean'
    ];

    protected $hidden = ['data'];

    protected $appends = [
        'reviews_count'
    ];

    function getReviewsCountAttribute(): int
    {
        return $this->reviews()->count();
    }

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    function reviews(): HasMany
    {
        return $this->hasMany(ShopifyReview::class, ShopifyReview::COLUMN_shopify_product_id, self::COLUMN_shopify_id);
    }

    static function findByShopAndId(ShopifyShop $shop, string $shopify_id): ?self
    {
        return self::where(self::COLUMN_shop_id, $shop->id)
            ->where(self::COLUMN_shopify_id, $shopify_id)
            ->first();
    }

    static function findByShopAndIdOrCreate(ShopifyShop $shop, string $shopify_id): ?self
    {
        $model = new self();
        $model->shopify_id = $shopify_id;
        $model->shop_id = $shop->id;
        $model->customer_id = $shop->customer_id;
        $model->data = "[]";
        $model->shopify_title = "";

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }

    static function storeProduct(ShopifyShop $shop, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->shopify_title = $model->getShopifyModel()->title;

        $model->save();
        return $model;
    }

    /**
     * @return Product
     */
    function getShopifyModel()
    {
        return json_decode($this->data, false);
    }

    /**
     * Find item by uid.
     *
     * @param string $uid
     * @return self
     */
    static function findByUid($uid)
    {
        return self::where(self::COLUMN_uid, $uid)->first();
    }

    /**
     * Find item by shopify id.
     *
     * @param string $id
     * @return self
     */
    static function findByShopifyId($id)
    {
        return self::where(self::COLUMN_shopify_id, $id)->first();
    }

    /**
     * @param $ids
     * @return self[]
     */
    static function findByShopifyIds($ids)
    {
        return self::whereIn(self::COLUMN_shopify_id, $ids)->get();
    }

    static function trigger_review_fetch()
    {
        /** @var self[] $models */
        $models = self::where(self::COLUMN_submitted_for_review, false)->limit(100)->get();
        if (!count($models)) return;

        $product_data = [];
        foreach ($models as $model) {
            $product_data[] = [
                'shop_name' => $model->shop->myshopify_domain,
                'product_id' => $model->shopify_id,
                'product_handle' => $model->getShopifyModel()->handle,
                'product_url' => "https://" . $model->shop->myshopify_domain . "/products/" . $model->getShopifyModel()->handle,
            ];
        }
        $client = new Client();
        try {
            $res = $client->request('POST', "http://localhost:4003/register-product", [
                'json' => [
                    'products' => $product_data,
                ]
            ]);
            if ($res->getStatusCode() == 200) {
                foreach ($models as $model) {
                    $model->submitted_for_review = true;
                    $model->save();
                }
            }
        } catch (GuzzleException $e) {

        }
    }

    static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (self::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    function getProductLink(): string
    {
        return "https://" . $this->shop->primary_domain . "/products/" . $this->getShopifyModel()->handle;
    }

    function getPrice(): string
    {
        $model = $this->getShopifyModel();
        $amount = ($model && $model->variants && count($model->variants)) ? $model->variants[0]->price : null;
        return $amount ? ($this->shop->primary_currency . " " . $amount) : "";
    }

    static function getBetweenData($html, $startIndex, $startChar, $endChar)
    {
        $ValueStart = -1;
        $ValueEnd = -1;
        $data = '';
        for($i = $startIndex; $i < strlen($html); $i++) {
            if($ValueStart == -1 && $html[$i] == $startChar) {
                $ValueStart = $i;
            }
            else if($ValueStart != -1 && $html[$i] == $endChar) {
                $ValueEnd = $i;
                break;
            }
        }
        if($ValueStart != -1 && $ValueEnd != -1) {
            $data = substr($html, $ValueStart + 1, $ValueEnd - $ValueStart - 1);
        }
        return array($ValueStart, $ValueEnd, $data);
    }

    static function prepareDynamicProducts($html)
    {
        $find = ' class="dynamic-products ';
        $start = 0;
        while(true) {
            $index = strpos($html, $find, $start);
            if(!$index) {
                break;
            }
            
            $dcIndex = strpos($html, 'data-count', $index + strlen($find));
            if(!$dcIndex) {
                $start += $index + strlen($find);
                continue;
            }
            list($a1, $b1, $count) = ShopifyProduct::getBetweenData($html, $dcIndex + strlen('data-count'), '"', '"');
            if($a1 == -1 || $b1 == -1) {
                $start += $index + strlen($find);
                continue;
            }
            $count = (int)$count;

            $psIndex = strpos($html, 'product-source', $index + strlen($find));
            if(!$psIndex) {
                $start += $index + strlen($find);
                continue;
            }
            list($a2, $b2, $productSource) = ShopifyProduct::getBetweenData($html, $psIndex + strlen('product-source'), '"', '"');
            if($a2 == -1 || $b2 == -1) {
                $start += $index + strlen($find);
                continue;
            }
            $productSource = explode(',', $productSource);

            $pidIndex = strpos($html, 'product-id', $index + strlen($find));
            $productId = 0;
            if(!$pidIndex) {
                $start += $index + strlen($find);
                continue;
            }
            list($a3, $b3, $productId) = ShopifyProduct::getBetweenData($html, $pidIndex + strlen('product-id'), '"', '"');
            if($a3 == -1 || $b3 == -1) {
                $start += $index + strlen($find);
                continue;
            }
            $dbsIndex = strpos($html, 'data-best-selling', $index + strlen($find));
            if (!$dbsIndex) {
                $start += $index + strlen($find);
                continue;
            }
            list($a4, $b4, $bestSelling) = ShopifyProduct::getBetweenData($html, $dbsIndex + strlen('data-best-selling'), '"', '"');
            if ($a4 == -1 || $b4 == -1) {
                $start += $index + strlen($find);
                continue;
            }
            $productId = explode(',', $productId);

            $isError = false;
            for ($i=0; $i < $count; $i++) { 
                $product = ShopifyProduct::find($productId[$i]);
                if(!$product) {
                    continue;
                }
                $model = $product->getShopifyModel();

                $imgSrc = ($model->images && count($model->images)) ? $model->images[0]->src : "";
                $title = $model->title;
                $price = $product->getPrice();
                $description = ShopifyHelper::html2Text($model->body_html);
                $link = $product->getProductLink();

                // Image Src Update
                $imgIndex = strpos($html, 'dynamic-products-image-' . $i, $index + strlen($find));
                if(!$imgIndex) {
                    $isError = true;
                    break;
                }
                $imgIndex = strpos($html, 'src=', $imgIndex);
                if(!$imgIndex) {
                    $isError = true;
                    break;
                }
                list($img1, $img2, $t1) = ShopifyProduct::getBetweenData($html, $imgIndex, '"', '"');
                if($img1 == -1 || $img2 == -1) {
                    $isError = true;
                    break;
                }
                $html = substr($html, 0, $img1 + 1) . $imgSrc . substr($html, $img2);
                
                $titleIndex = strpos($html, 'dynamic-products-title-' . $i, $index + strlen($find));
                if(!$titleIndex) {
                    $isError = true;
                    break;
                }
                list($tt1, $tt2, $t2) = ShopifyProduct::getBetweenData($html, $titleIndex, '>', '<');
                if($tt1 == -1 || $tt2 == -1) {
                    $isError = true;
                    break;
                }
                $html = substr($html, 0, $tt1 + 1) . $title . substr($html, $tt2);

                $priceIndex = strpos($html, 'dynamic-products-price-' . $i, $index + strlen($find));
                if(!$priceIndex) {
                    $isError = true;
                    break;
                }
                list($p1, $p2, $t3) = ShopifyProduct::getBetweenData($html, $priceIndex, '>', '<');
                if($p1 == -1 || $p2 == -1) {
                    $isError = true;
                    break;
                }
                $html = substr($html, 0, $p1 + 1) . $price . substr($html, $p2);

                $descriptionIndex = strpos($html, 'dynamic-products-description-' . $i, $index + strlen($find));
                if(!$descriptionIndex) {
                    $isError = true;
                    break;
                }
                list($d1, $d2, $t4) = ShopifyProduct::getBetweenData($html, $descriptionIndex, '>', '<');
                if($d1 == -1 || $d2 == -1) {
                    $isError = true;
                    break;
                }
                $html = substr($html, 0, $d1 + 1) . $description . substr($html, $d2);
                
                $buttonIndex = strpos($html, 'dynamic-products-button-' . $i, $index + strlen($find));
                if(!$buttonIndex) {
                    $isError = true;
                    break;
                }
                $buttonIndex = strpos($html, 'href=', $buttonIndex);
                if(!$buttonIndex) {
                    $isError = true;
                    break;
                }
                list($b1, $b2, $t5) = ShopifyProduct::getBetweenData($html, $buttonIndex, '"', '"');
                if($b1 == -1 || $b2 == -1) {
                    $isError = true;
                    break;
                }
                $html = substr($html, 0, $b1 + 1) . $link . substr($html, $b2);
            }
            if($isError) {
                $start += $index + strlen($find);
                continue;
            }
            $start += $index + strlen($find);
        }
        return $html;
    }
    static function getBestSellingHtml($body, $customer){
        
        $html = new DOMDocument();
        //Throwing invalid Tag warnings
        @$html->loadHTML($body, LIBXML_HTML_NODEFDTD | LIBXML_HTML_NOIMPLIED);
        $xp = new DOMXPath($html);
        $productRows = $xp->evaluate('//*[@data-best-selling="1"]');
        
     
        if(count($productRows) > 0){
                $products = ShopifyProduct::query()
            ->where(ShopifyProduct::COLUMN_customer_id, $customer->id)
            ->orderBy(ShopifyProduct::COLUMN_units_sold, "desc")->limit(3)->get();
                if($products){
                    $product1 = json_decode($products[0]->data);
                    $product2 = json_decode($products[1]->data);
                    $product3 = json_decode($products[2]->data);
                    foreach($xp->evaluate('//*[@class="dynamic-products-title-0"]/text()') as $title){
                        $title->data = $product1->title;//$product1->title;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-price-0"]/text()') as $price){
                        $price->data = $customer->shopify_shop->primary_currency . " " . $product1->variants[0]->price;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-image-0 no-image-edit"]') as $src){
                        if(isset($product1->image->src)){
                            $src->setAttribute('src',$product1->image->src);
                        }
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-description-0"]/text()') as $desc){
                        $desc->data = "{desc1}";
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-button-0"]') as $button){
                        $button->setAttribute('href', $customer->shopify_shop->primary_domain . "/products/" . $product1->handle);
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-title-1"]/text()') as $title){
                        $title->data = $product2->title;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-price-1"]/text()') as $price){
                        $price->data = $customer->shopify_shop->primary_currency . " " . $product2->variants[0]->price;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-image-1 no-image-edit"]') as $src){
                        if(isset($product2->image->src)){
                            $src->setAttribute('src',$product2->image->src);
                        }
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-description-1"]/text()') as $desc){
                        $desc->data =  "{desc2}";
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-button-1"]') as $button){
                        $button->setAttribute('href', $customer->shopify_shop->primary_domain . "/products/" . $product2->handle);
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-title-2"]/text()') as $title){
                        $title->data = $product3->title;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-price-2"]/text()') as $price){
                        $price->data = $customer->shopify_shop->primary_currency . " " . $product3->variants[0]->price;
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-image-2 no-image-edit"]') as $src){
                        if(isset($product3->image->src)){
                            $src->setAttribute('src',$product3->image->src);
                        }
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-description-2"]/text()') as $desc){
                        $desc->data =  "{desc3}";
                    }
                    foreach($xp->evaluate('//*[@class="dynamic-products-button-2"]') as $button){
                        $button->setAttribute('href', $customer->shopify_shop->primary_domain . "/products/" . $product3->handle);
                    }
                    $body = $html->saveHTML();
                    $html = str_replace("{desc1}",strip_tags($product1->body_html), $body);
                    $html = str_replace("{desc2}",strip_tags($product2->body_html), $html);
                    $html = str_replace("{desc3}",strip_tags($product3->body_html), $html);
                return $html;
                }
                
        }
        return $body;
    }
    static function getHtmlRepresentation(ProductConfig $config): string
    {
        $image_url = "/cb/assets/minimalist-blocks/images/photo-1459411552884-841db9b3cc2a-YO89Y1.jpg";
        $title = "Product One";
        $price = "$109";
        $description = "Lorem Ipsum is simply dummy text of the printing industry.";
        $buy_now_link = "";

        if ($config->product) {
            $model = $config->product->getShopifyModel();

            $image_url = ($model->images && count($model->images)) ? $model->images[0]->src : "";
            $title = $model->title;
            $price = $config->product->getPrice();
            $description = ShopifyHelper::html2Text($model->body_html);
            $buy_now_link = $config->product->getProductLink();
        }
        return '
    <div style="background-color:'.$config->text_background_color.';color:'.$config->text_color.'">
    <img src="' . $image_url . '" alt="">
    <h3>' . $title . ', <b>' . $price . '</b></h3>
    '.($config->description ? "<p>$description</p>": '').'
    <div style="margin:1.2em 0">
        <span>
        <a href="' . $buy_now_link . '" class="is-btn is-btn-small is-btn-ghost1 is-upper edit"
        style="color:' . $config->button_text_color . ';background-color:' . $config->button_color . ';border-color:'.$config->button_border_color.'">' . $config->button_text . '</a></span>
    </div>
    </div>';
    }

    static function getProduct3HtmlRepresentation(Products3Config $config): string
    {
        $image_url_1 = "/cb/assets/minimalist-blocks/images/-7eFyL1.jpg";
        $title_1 = "Product One";
        $price_1 = "$109";
        $description_1 = "Lorem Ipsum is simply dummy text of the printing industry.";
        $buy_now_link_1 = "";
        if ($config->product_1) {
            $model = $config->product_1->getShopifyModel();

            $image_url_1 = ($model->images && count($model->images)) ? $model->images[0]->src : "";
            $title_1 = $model->title;
            $price_1 = $config->product_1->getPrice();
            $description_1 = ShopifyHelper::html2Text($model->body_html);
            $buy_now_link_1 = $config->product_1->getProductLink();
        }

        $image_url_2 = "/cb/assets/minimalist-blocks/images/-09yZA2.jpg";
        $title_2 = "Product Two";
        $price_2 = "$89";
        $description_2 = "Lorem Ipsum is simply dummy text of the printing industry.";
        $buy_now_link_2 = "";
        if ($config->product_2) {
            $model = $config->product_2->getShopifyModel();

            $image_url_2 = ($model->images && count($model->images)) ? $model->images[0]->src : "";
            $title_2 = $model->title;
            $price_2 = $config->product_2->getPrice();
            $description_2 = ShopifyHelper::html2Text($model->body_html);
            $buy_now_link_2 = $config->product_2->getProductLink();
        }
        
        $image_url_3 = "/cb/assets/minimalist-blocks/images/-Tmg8p2.jpg";
        $title_3 = "Product Three";
        $price_3 = "$79";
        $description_3 = "Lorem Ipsum is simply dummy text of the printing industry.";
        $buy_now_link_3 = "";
        if ($config->product_3) {
            $model = $config->product_3->getShopifyModel();

            $image_url_3 = ($model->images && count($model->images)) ? $model->images[0]->src : "";
            $title_3 = $model->title;
            $price_3 = $config->product_3->getPrice();
            $description_3 = ShopifyHelper::html2Text($model->body_html);
            $buy_now_link_3 = $config->product_3->getProductLink();
        }

        return '
    <div style="background-color:'.$config->text_background_color.';color:'.$config->text_color.'">
    <div class="row clearfix">
        <div class="column third">
            <img src="' . $image_url_1 . '" alt="" />
            <h3 class="size-21">' . $title_1 . ', <b>' . $price_1 . '</b></h3>
            '.($config->description ? "<p>$description_1</p>": '').'
            <p><a href="' . $buy_now_link_1 . '">' . $config->button_text . '</a></p>
        </div>
        <div class="column third">
            <img src="' . $image_url_2 . '" alt="" />
            <h3 class="size-21">' . $title_2 . ', <b>' . $price_2 . '</b></h3>
            '.($config->description ? "<p>$description_2</p>": '').'
            <p><a href="' . $buy_now_link_2 . '">' . $config->button_text . '</a></p>
        </div>
        <div class="column third">
            <img src="' . $image_url_3 . '" alt="" />
            <h3 class="size-21">' . $title_3 . ', <b>' . $price_3 . '</b></h3>
            '.($config->description ? "<p>$description_3</p>": '').'
            <p><a href="' . $buy_now_link_3 . '">' . $config->button_text . '</a></p>
        </div>
    </div>
    </div>';
    }
}
