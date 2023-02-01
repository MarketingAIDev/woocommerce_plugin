<?php

namespace Acelle\Model;

use Acelle\Library\Automation\DynamicWidgetConfig\CartConfig;
use Acelle\ShopifyModel\Checkout;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Acelle\Helpers\ShopifyHelper;

/**
 * @property integer id
 * @property string uid
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property integer customer_id
 * @property Customer customer
 * @property integer shop_id
 * @property ShopifyShop shop
 * @property integer shopify_customer_id
 * @property ShopifyCustomer shopify_customer
 * @property string data
 * @property string shopify_id
 * @property boolean automation_triggered
 * @property string|Carbon shopify_created_at
 * @property string|Carbon shopify_updated_at
 * @property string|Carbon shopify_completed_at
 */
class ShopifyCheckout extends Model
{
    const COLUMN_id = 'id';
    const COLUMN_uid = 'uid';
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_shop_id = 'shop_id';
    const COLUMN_shopify_customer_id = 'shopify_customer_id';
    const COLUMN_shopify_id = 'shopify_id';
    const COLUMN_data = 'data';
    const COLUMN_shopify_created_at = 'shopify_created_at';
    const COLUMN_shopify_updated_at = 'shopify_updated_at';
    const COLUMN_shopify_completed_at = 'shopify_completed_at';

    protected $hidden = ['data'];

    protected $dates = [
        self::COLUMN_shopify_created_at,
        self::COLUMN_shopify_updated_at,
        self::COLUMN_shopify_completed_at,
    ];

    function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    function shop(): BelongsTo
    {
        return $this->belongsTo(ShopifyShop::class);
    }

    function shopify_customer(): BelongsTo
    {
        return $this->belongsTo(ShopifyCustomer::class);
    }

    // Maintain a list of automations triggered for this checkout
    // To avoid triggering same automation for same checkout again
    function triggered_automations(): BelongsToMany
    {
        return $this->belongsToMany(Automation2::class, 'automations_shopify_checkouts');
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
        $model->shopify_created_at = null;
        $model->shopify_updated_at = null;
        $model->shopify_completed_at = null;

        $db_model = self::findByShopAndId($shop, $shopify_id);
        if ($db_model) return $db_model;

        $model->save();
        return $model;
    }

    static function storeCheckout(ShopifyShop $shop, array $data)
    {
        if (empty($shop)) return null;

        $shopify_id = $data['id'] ?? null;
        if (empty($shopify_id)) return false;

        $model = self::findByShopAndIdOrCreate($shop, $shopify_id);
        $model->data = json_encode(array_merge(json_decode($model->data, true), $data));
        $model->shopify_created_at = Carbon::parse($model->getShopifyModel()->created_at)->setTimezone('UTC');
        $model->shopify_updated_at = Carbon::parse($model->getShopifyModel()->updated_at)->setTimezone('UTC');
        $model->shopify_completed_at = $model->getShopifyModel()->completed_at ? Carbon::parse($model->getShopifyModel()->completed_at)->setTimezone('UTC') : null;

        $model->save();
        return $model;
    }

    /**
     * Gets the abandoned checkouts of last 24hours from the 1 hour less than the current time
     * Ex: Current Time is 2022-06-02 10:30:24
     * The checks are gathered when 
     * shopify_created_at <= 2022-06-02 09:30:24 AND shopify_created_at >= 2022-06-01 10:30:24
     * @param Customer $customer
     * @return self[]
     */
    static function getAbandonedCheckouts(Customer $customer)
    {
        return self::whereNull(self::COLUMN_shopify_completed_at)
            ->where(self::COLUMN_customer_id, $customer->id)
            ->where(self::COLUMN_shopify_created_at, "<=", Carbon::now()->subHour())
            ->where(self::COLUMN_shopify_created_at, ">=", Carbon::now()->subDay())
            ->limit(10)
            ->get();
    }

    /**
     * @return Checkout
     */
    function getShopifyModel()
    {
        return json_decode($this->data, false);
    }

    /**
     * Find item by uid.
     *
     * @return self
     */
    public static function findByUid($uid)
    {
        return self::where('uid', '=', $uid)->first();
    }

    public static function boot()
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

    
    static function prepareAbandonedCart(?self $instance, $html)
    {
        $find = '<tbody class="abandoned-body-starts';
        $start = 0;
        while(true) {
            $index = strpos($html, $find, $start);
            if(!$index)
                break;
            $index1 = strpos($html, '>', $index + strlen($find));
            $a = substr($html, 0, $index1+1);

            $isDescriptionIndex = strpos($html, 'data-isdescription', $index + strlen($find));
            if(!$isDescriptionIndex) {
                $start = $index + strlen($find);
                continue;
            }
            list($d1, $d2, $isDescription) = ShopifyProduct::getBetweenData($html, $isDescriptionIndex + strlen('data-isdescription'), '"', '"');
            if($d1 == -1 || $d2 == -1) {
                $start = $index + strlen($find);
                continue;
            }
            $isDescription = (int)$isDescription;

            list($a1, $a2, $data1) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1', $index + strlen($find)) + strlen('abandoned-body-starts-r1'), '"', '"');
            list($a1, $a2, $data2) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1'), '"', '"');
            list($a1, $a2, $data3) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item'), '"', '"');
            list($a1, $a2, $data4) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-table', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-table'), '"', '"');
            list($a1, $a2, $data5) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1'), '"', '"');
            list($a1, $a2, $data6) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c1', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c1'), '"', '"');
            list($a1, $a2, $data7) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c1-img', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c1-img'), '"', '"');
            list($a1, $a2, $data8) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2'), '"', '"');
            list($a1, $a2, $data9) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-title', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-title'), '"', '"');
            list($a1, $a2, $data10) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-info', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-info'), '"', '"');
            list($a1, $a2, $data11) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c1-item-r1-c2-price', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c1-item-r1-c2-price'), '"', '"');
            list($a1, $a2, $data12) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c2', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c2'), '"', '"');
            list($a1, $a2, $data13) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c2-qty', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c2-qty'), '"', '"');
            list($a1, $a2, $data14) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c3', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c3'), '"', '"');
            list($a1, $a2, $data15) = ShopifyProduct::getBetweenData($html, strpos($html, 'abandoned-body-starts-r1-c3-price', $index + strlen($find)) + strlen('abandoned-body-starts-r1-c3-price'), '"', '"');

            $x = '';
            $orderModel = $instance->getShopifyModel();
            if ($instance) {
                foreach ($instance->getShopifyModel()->line_items as $item) {
                    $product = ShopifyProduct::findByShopifyId($item->product_id);
                    $model = $product->getShopifyModel();
                    $image_url_1 = '';
                    foreach($model->variants as $v) {
                        if($v->id == $item->variant_id) {
                            foreach ($model->images as $img) {
                                if($img->id == $v->image_id) {
                                    $image_url_1 = $img->src;
                                    break;     
                                }
                            }
                            break;
                        }
                    }
                    $description_1 = ShopifyHelper::html2Text($model->body_html);
                    
                    $descHtml = '';
                    if($isDescription) {
                        $descHtml = '<div class="item-info" style="' . $data10 . '">' . $description_1 . '</div>';
                    }

                    $x .= '<tr style="' . $data1 . '">' .
                    '<td style="' . $data2 . '">' .
                    '<div class="item" style="' . $data3 . '">' .
                    '<table style="' . $data4 . '">' .
                    '<tr style="' . $data5 . '">' .
                    '<td style="' . $data6 . '"><img class="no-image-edit" src="' . $image_url_1 . '" width="100px" style="' . $data7 . '">' .
                    '</td>' .
                    '<td class="mobile-info" style="' . $data8 . '">' .
                    '<div class="item-title" style="' . $data9 . '">' . $item->title . ' - ' . $item->variant_title . '</div>' .
                    $descHtml .
                    '<div class="mobile-qty-price" style="' . $data11 . '">' . $item->quantity . 'x' . $orderModel->presentment_currency . ' ' . $item->price . '</div>' .
                    '</td>' .
                    '</tr>' .
                    '</table>' .
                    '</div>' .
                    '</td>' .
                    '<td style="' . $data12 . '">' .
                    '<div class="qty" contenteditable="false" style="' . $data13 . '">' . $item->quantity .'</div>' .
                    '</td>' .
                    '<td style="' . $data14 . '">' .
                    '<div class="price" contenteditable="false" style="' . $data15 . '">' . $orderModel->presentment_currency . ' ' . $item->price .'</div>' .
                    '</td>' .
                    '</tr>';
                }
            }
            $a .= $x;
            $a .= substr($html, $index1+1);
            $html = $a;

            $btnFind = '<a class="gotocart-button';
            $btnFindIndex = strpos($html, $btnFind, $start);

            if($btnFindIndex) {
                $hrefIndex = strpos($html, 'href=', $btnFindIndex + strlen($btnFind));
                if($hrefIndex) {
                    list($a1, $a2, $href) = ShopifyProduct::getBetweenData($html, $hrefIndex, '"', '"');
                    $html = substr($html, 0, $a1 + 1) . $orderModel->abandoned_checkout_url . substr($html, $a2);
                }
            }
            $start = $index1;
            
        }
        return $html;
    }
    
    static function getHtmlRepresentation(?self $instance, CartConfig $config)
    {
        $html = '
    <div style="background-color:'.$config->text_background_color.';color:'.$config->text_color.'">
    <div class="row clearfix">
        <div class="column full" style="position: relative;">
            <p>Cart Summary</p>
            <p style="font-style: normal; top: 10px; right: 10px; float: right; position: absolute;">
            </p>
        </div>
    </div>
    <div class="row clearfix">
        <div class="column full">
            <table class="default table-condensed"
                   style="table-layout:fixed;border-collapse:collapse;width:100%;border-spacing: 0;">
                <tbody>';
        if ($instance) {
            foreach ($instance->getShopifyModel()->line_items as $item) {
                $html .= '                <tr>
                    <td style="width:50px"></td>
                    <td colspan="3">
                        <p class="margin-0"><b>'.$item->title.' x '.$item->quantity .' </b><br>'.$item->variant_title.'</p>
                    </td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$41.55</b></p></td>
                </tr>';
            }
        } else {
            $html .= '                <tr>
                    <td style="width:50px"></td>
                    <td colspan="3">
                        <p class="margin-0"><b>Test Title x 1 </b><br>Variant</p>
                    </td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$41.55</b></p></td>
                </tr>';
        }
        $html .= '                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Subtotal</td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$41.55</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Shipping</td>

                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$0.00</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td colspan="2">Taxes</td>
                    <td style="width:100px"><p class="margin-0" style="text-align:right;"><b>$0.00</b></p></td>
                </tr>
                <tr>
                    <td><br></td>
                    <td></td>
                    <td>Total</td>
                    <td colspan="2"><p class="margin-0" style="text-align:right;font-size:1.5em;"><b>$41.55</b></p></td>
                </tr>
                </tbody>
            </table>
        </div>
    </div>
    <div class="row clearfix" draggable="false">
        <div class="column full" style="text-align: center;">
            <a href="#"
               style="display: inline-block; text-decoration: none; transition: all 0.16s ease 0s; border-style: solid; cursor: pointer; background-color: rgb(58, 140, 233); color: rgb(247, 247, 247); border-color: rgb(21, 101, 192); border-width: 2px; border-radius: 4px; padding: 13px 28px; line-height: 1.5; text-transform: initial; font-weight: normal; font-size: 14px; letter-spacing: 3px;"
               draggable="false">Complete your purchase</a>
        </div>
    </div>
    </div>';

        return $html;
    }

    function getFirstProductLink(): string
    {
        $product_id = $this->getShopifyModel()->line_items[0]->product_id ?? null;
        if (!$product_id) return "";

        $product = ShopifyProduct::findByShopifyId($product_id);
        if (!$product) return "";

        return "https://" . $this->shop->primary_domain . "/products/" . $product->getShopifyModel()->handle;
    }

    function getFirstProductTitle(): string
    {
        $product_id = $this->getShopifyModel()->line_items[0]->product_id ?? null;
        if (!$product_id) return "";

        $product = ShopifyProduct::findByShopifyId($product_id);
        if (!$product) return "";

        return $product->shopify_title;
    }
}
