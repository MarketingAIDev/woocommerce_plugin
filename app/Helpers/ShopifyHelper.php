<?php


namespace Acelle\Helpers;


use Acelle\Helpers\ShopifyResponses\Shop;
use Acelle\Http\Controllers\ShopifyWebhookController;
use Acelle\Model\Automation2;
use Acelle\Model\MonthlyCharge;
use Acelle\Model\Plan;
use Acelle\Model\Popup;
use Acelle\Model\ShopifyBlog;
use Acelle\Model\ShopifyCheckout;
use Acelle\Model\ShopifyCustomer;
use Acelle\Model\ShopifyDiscountCode;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyPage;
use Acelle\Model\ShopifyPriceRule;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyRecurringApplicationCharge;
use Acelle\Model\ShopifyShop;
use Acelle\Model\TrialRecord;
use Carbon\Carbon;
use DateTime;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use TiagoHillebrandt\ParseLinkHeader;

class ShopifyHelper
{
    /** @var ShopifyShop $shop */
    private $shop;
    public $api_version;
    public $client_id;
    private $client_secret;

    public function __construct($shop)
    {
        $this->shop = $shop;
        $this->api_version = config('shopify.api_version');
        $this->client_id = config('shopify.client_id');
        $this->client_secret = config('shopify.client_secret');
    }

    public static function html2Text($html): string
    {
        $text = $text = preg_replace(
            array(
                // Remove invisible content
                '@<head[^>]*?>.*?</head>@siu',
                '@<style[^>]*?>.*?</style>@siu',
                '@<script[^>]*?.*?</script>@siu',
                '@<object[^>]*?.*?</object>@siu',
                '@<embed[^>]*?.*?</embed>@siu',
                '@<applet[^>]*?.*?</applet>@siu',
                '@<noframes[^>]*?.*?</noframes>@siu',
                '@<noscript[^>]*?.*?</noscript>@siu',
                '@<noembed[^>]*?.*?</noembed>@siu',
                // Add line breaks before and after blocks
                '@</?((address)|(blockquote)|(center)|(del))@iu',
                '@</?((div)|(h[1-9])|(ins)|(isindex)|(p)|(pre))@iu',
                '@</?((dir)|(dl)|(dt)|(dd)|(li)|(menu)|(ol)|(ul))@iu',
                '@</?((table)|(th)|(td)|(caption))@iu',
                '@</?((form)|(button)|(fieldset)|(legend)|(input))@iu',
                '@</?((label)|(select)|(optgroup)|(option)|(textarea))@iu',
                '@</?((frameset)|(frame)|(iframe))@iu',
            ),
            array(
                ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ', ' ',
                "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0", "\n\$0",
                "\n\$0", "\n\$0",
            ),
            $html);
        return preg_replace("/[\r\n]+/", "\n", strip_tags($text));
    }

    public function verify_webhook($data, $header): bool
    {
        if (empty($header)) return false;
        $calculated_hmac = base64_encode(hash_hmac('sha256', $data, $this->client_secret, true));
        return hash_equals($header, $calculated_hmac);
    }

    public static function getPrimaryDomain(string $myshopify_domain)
    {
        ini_set('user_agent', 'Mozilla/5.0 (X11; Linux x86_64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/85.0.4183.83 Safari/537.36');
        $myshopify_url = "https://$myshopify_domain/";
        $headers = @get_headers($myshopify_url, 1);
        $effective_url = $headers["Location"] ?? $myshopify_url;

        return parse_url($effective_url, PHP_URL_HOST);
    }

    public function getPermanentAccessToken(string $myshopify_domain, string $temporary_code)
    {
        $client = new Client();
        try {
            $res = $client->request('POST', "https://$myshopify_domain/admin/oauth/access_token", [
                'form_params' => [
                    'client_id' => $this->client_id,
                    'client_secret' => $this->client_secret,
                    'code' => $temporary_code,
                ]
            ]);
        } catch (GuzzleException $e) {
            return false;
        }

        $response = json_decode($res->getBody()->getContents());
        return $response->access_token;
    }

    /**
     * @param string $myshopify_domain
     * @param string $access_token
     * @return Shop
     * @throws GuzzleException
     */
    public function getShopObject(string $myshopify_domain, string $access_token)
    {
        $client = new Client();
        $res = $client->request('GET', "https://$myshopify_domain/admin/api/" . $this->api_version . "/shop.json", [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json',
            ]
        ]);
        $response = json_decode($res->getBody()->getContents());
        return $response->shop;
    }

    function graphQLQuery($graphql_query)
    {
        
        $shop = $this->shop->myshopify_domain;
        $access_token = $this->shop->access_token;
        $url = "https://$shop/admin/api/2023-01/graphql.json";

        $client = new Client();
        $res = $client->request('POST', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'query' => $graphql_query
            ]),
        ]);
        return json_decode($res->getBody()->getContents());
    }
    function queryStoreFront($query)
    {
        
        $shop = $this->shop->myshopify_domain;
        $url = "https://$shop/storefront/api/2023-01/queries/blogs";

        $client = new Client();
        $res = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/json',
            ],
            'body' => json_encode([
                'query' => $query
            ]),
        ]);
        return json_decode($res->getBody()->getContents());
    }
    public function getCurrencies(string $myshopify_domain, string $access_token)
    {
        $default_currency_codes = ["AED",
            "GTQ",
            "PEN",
            "AFN",
            "GYD",
            "PGK",
            "ALL",
            "HKD",
            "PHP",
            "AMD",
            "HNL",
            "PKR",
            "ANG",
            "HRK",
            "PLN",
            "AOA",
            "HTG",
            "PYG",
            "ARS",
            "HUF",
            "QAR",
            "AUD",
            "IDR",
            "RON",
            "AWG",
            "ILS",
            "RSD",
            "AZN",
            "INR",
            "RUB",
            "BAM",
            "ISK",
            "RWF",
            "BBD",
            "JMD",
            "SAR",
            "BDT",
            "JPY",
            "SBD",
            "BGN",
            "KES",
            "SCR",
            "BIF",
            "KGS",
            "SEK",
            "BMD",
            "KHR",
            "SGD",
            "BND",
            "KMF",
            "SHP",
            "BOB",
            "KRW",
            "SLL",
            "BRL",
            "KYD",
            "SRD",
            "BSD",
            "KZT",
            "STD",
            "BWP",
            "LAK",
            "SZL",
            "BZD",
            "LBP",
            "THB",
            "CAD",
            "LKR",
            "TJS",
            "CDF",
            "LRD",
            "TOP",
            "CHF",
            "LSL",
            "TRY",
            "CLP",
            "MAD",
            "TTD",
            "CNY",
            "MDL",
            "TWD",
            "COP",
            "MGA",
            "TZS",
            "CRC",
            "MKD",
            "UAH",
            "CVE",
            "MMK",
            "UGX",
            "CZK",
            "MNT",
            "USD",
            "DJF",
            "MOP",
            "UYU",
            "DKK",
            "MUR",
            "UZS",
            "DOP",
            "MVR",
            "VND",
            "DZD",
            "MWK",
            "VUV",
            "EGP",
            "MXN",
            "WST",
            "ETB",
            "MYR",
            "XAF",
            "EUR",
            "MZN",
            "XCD",
            "FJD",
            "NAD",
            "XOF",
            "FKP",
            "NGN",
            "XPF",
            "GBP",
            "NIO",
            "YER",
            "GEL",
            "NOK",
            "ZAR",
            "GIP",
            "NPR",
            "ZMW",
            "GMD",
            "NZD",
            "GNF",
            "PAB"];
        return $default_currency_codes;
        /*
        $client = new Client();
        $res = $client->request('GET', "https://$myshopify_domain/admin/api/" . $this->api_version . "/currencies.json", [
            'headers' => [
                'X-Shopify-Access-Token' => $access_token,
                'Content-Type' => 'application/json',
            ]
        ]);
        $response = json_decode($res->getBody()->getContents(), true);
        $currencies = $response['currencies'] ?? [];
        $currency_codes = [];
        foreach ($currencies as $currency) {
            $currency_codes[] = $currency['currency'];
        }
        if (!count($currency_codes)) return $default_currency_codes;
        return $currency_codes;*/
    }

    private function getOAuthAuthorizeURL()
    {
        $myshopify_domain = $this->shop->myshopify_domain;
        return "https://$myshopify_domain/admin/oauth/authorize";
    }

    public function fetchFromShopifyApi($end_point, $query = array(), $request_method = "GET")
    {
        $shop = $this->shop->myshopify_domain;

        $url = "https://$shop/admin/api/" . $this->api_version . "/$end_point";

        //echo $shop;
        //echo $this->access_token;

        $header = array(
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
        );

        if (is_array($query) && !empty($query)) {
            if ($request_method == "GET") {
                $header['query'] = $query;
            } elseif ($request_method == "PUT") {
                $header['json'] = $query;
            } else {
                $header['form_params'] = $query;
            }
        }

        $client = new Client();
        $response = $client->request($request_method, $url, $header);

        if ($response->getStatusCode() != 200) {
            return false;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents);

        return $json;
    }

    function setThemeData()
    {
        if (!config('shopify.live_payments'))
            return;
        $themes_response = $this->fetchFromShopifyApi("themes.json", array('role' => 'main'));
        if (isset($themes_response->errors) || empty($themes_response->themes)) {
            $this->shop->theme = [
                'font_family' => '',
                'buy_now_btn_color' => '',
                'buy_now_btn_text_color' => '',
                'add_to_cart_btn_color' => '',
                'add_to_cart_btn_text_color' => '',
            ];
            $this->shop->save();
            return;
        }

        $theme_id = $themes_response->themes[0]->id;
        $theme_setting_api_response = $this->fetchFromShopifyApi("themes/$theme_id/assets.json",
            array('asset[key]' => 'config/settings_data.json'));

        $style_settings_arr = array();
        if (is_object($theme_setting_api_response) && !empty((array)$theme_setting_api_response)) {
            $theme_style_settings = json_decode($theme_setting_api_response->asset->value);
            $style_current_settings = $theme_style_settings->current;

            /* For add to cart button */
            $style_settings_arr['add_to_cart_btn_color'] = array(
                (!empty($style_current_settings->color_content_bg) && $style_current_settings->color_content_bg != "rgba(0,0,0,0)") ?
                    $style_current_settings->color_content_bg /* Venture(also used for content bg color) */ :
                    (!empty($style_current_settings->color_body_bg) ?
                        $style_current_settings->color_body_bg : ''), /* Ven-ture(also used for bg color) */
                !empty($style_current_settings->color_body_bg) ? $style_current_settings->color_body_bg : '', /* Brooklyn, Debut, simple, Boundless, Supply, Minimal */
                !empty($style_current_settings->color_main_bg) ? $style_current_settings->color_main_bg : '', /* Narrative */
            );

            $style_settings_arr['add_to_cart_btn_text_color'] = array(
                !empty($style_current_settings->color_primary) ? $style_current_settings->color_primary : '', /* Brooklyn, Supply, Narrative, Minimal*/
                !empty($style_current_settings->color_button) ? $style_current_settings->color_button : '', /* Debut */
                !empty($style_current_settings->color_primary_button_text) ? $style_current_settings->color_primary_button_text : '', /* Simple */
                !empty($style_current_settings->color_button_bg) ? $style_current_settings->color_button_bg : '', /* Boundless, Venture */
            );

            /* For buy now button */
            $style_settings_arr['buy_now_btn_color'] = array(
                !empty($style_current_settings->color_primary) ? $style_current_settings->color_primary : '', /* Brooklyn, Supply, Narrative, Minimal */
                !empty($style_current_settings->color_button) ? $style_current_settings->color_button : '', /* Debut */
                !empty($style_current_settings->color_button_bg) ? $style_current_settings->color_button_bg : '', /* Boundless, Venture */
                !empty($style_current_settings->color_primary_color) ? $style_current_settings->color_primary_color : '', /* simple */
            );

            $style_settings_arr['buy_now_btn_text_color'] = array(
                !empty($style_current_settings->color_button_text) ? $style_current_settings->color_button_text : '', /* Brooklyn, Debut, Boundless, Venture, Narrative */
                !empty($style_current_settings->color_body_bg) ? $style_current_settings->color_body_bg : '', /* Supply */
                !empty($style_current_settings->color_primary_button_text) ? $style_current_settings->color_primary_button_text : '', /* Simple */
                !empty($style_current_settings->color_button_primary_text) ? $style_current_settings->color_button_primary_text : '', /* Minimal */
            );

            /* For description font-family */
            $style_settings_arr['font_family'] = array(
                !empty($style_current_settings->type_base_family) ? $style_current_settings->type_base_family : '', /* Brooklyn, Boundless, Venture, Minimal */
                !empty($style_current_settings->type_base_font) ? $style_current_settings->type_base_font : '', /* Debut, Simple, Supply, Narrative */
            );
        }

        if (!empty($style_settings_arr)) {
            $style_settings_arr = array_map([$this, 'make_theme_settings_arr'], $style_settings_arr);
            $style_settings_arr = array_filter($style_settings_arr);
        }

        $this->shop->theme = [
            'theme_id' => $theme_id,
            'font_family' => $style_settings_arr['font_family'] ?? '',
            'buy_now_btn_color' => $style_settings_arr['buy_now_btn_color'] ?? '',
            'buy_now_btn_text_color' => $style_settings_arr['buy_now_btn_text_color'] ?? '',
            'add_to_cart_btn_color' => $style_settings_arr['add_to_cart_btn_color'] ?? '',
            'add_to_cart_btn_text_color' => $style_settings_arr['add_to_cart_btn_text_color'] ?? '',
        ];

        $this->shop->save();
    }

    function insertReviewSnippet()
    {
        // Skip this for local installations
        if (Str::contains(url(''), 'localhost'))
            return;

        $theme_id = $this->shop->theme['theme_id'] ?? null;
        if ($theme_id) {
            $first_attempt = $this->put_review_snippet($theme_id, "sections/product-template.liquid");
            if (!$first_attempt) {
                $second_attempt = $this->put_review_snippet($theme_id, "templates/products.liquid");
            }
        }
    }

    public function put_review_snippet($theme_id, $template_name = "sections/product-template.liquid")
    {
        $qeury_string = array('asset[key]' => $template_name, 'theme_id' => $theme_id);
        $product_file_get = $this->fetchFromShopifyApi("themes/$theme_id/assets.json", $qeury_string);

        if (isset($product_file_get->asset)) {
            $review_snippet = '<div id="ew_reviews" data-shop-name="{{shop.permanent_domain}}" data-product-id="{{product.id}}"></div>';
            $variables_item = '{{ product.description }}';
            $original_asset_value = $product_file_get->asset->value;

            /* we checked is template has product description object or not if not we don't need to check place our snippet here */
            if (strpos($original_asset_value, $variables_item) !== FALSE) {
                /* we checked is our snippet is already exist or not if exist then no need to placed again */
                if (strpos($original_asset_value, $review_snippet) === FALSE) {
                    /* going to pur the snippet */
                    $variable_position = strpos($original_asset_value, $variables_item, 0);
                    if ($variable_position !== FALSE) {
                        $close_div_pos = strpos($original_asset_value, "</div>", $variable_position);
                        if ($close_div_pos !== FALSE) {
                            $close_div_pos += strlen("</div>");
                            $review_snippet = "\n $review_snippet \n";
                            $new_asset_value = substr_replace($original_asset_value, $review_snippet, $close_div_pos, 0);
                        }
                    }
                }
            }

            if (isset($new_asset_value)) {
                echo "Trying to update the theme...\n\n\n";
                /* here we going for update liquid template but
                 * we take backup first of its own template */
                $bkp_template_name = str_replace(".liquid", "your_app_bkp.liquid", $template_name);
                $url_param_arr = array('asset' => array('key' => $bkp_template_name, 'value' => $original_asset_value));
                $update_response = $this->fetchFromShopifyApi("themes/$theme_id/assets.json", $url_param_arr, 'PUT');
                print "backup response ready";
                print_r($update_response);

                if (isset($update_response->theme) || isset($update_response->asset)) {
                    $url_param_arr = array('asset' => array('key' => $template_name, 'value' => $new_asset_value));
                    $update_response = $this->fetchFromShopifyApi("themes/$theme_id/assets.json", $url_param_arr, 'PUT');
                    print "update response ready";
                    print_r($update_response);

                    if (isset($update_response->theme) || isset($update_response->asset)) {
                        return TRUE;
                    }
                }
            }
        }

        return FALSE;
    }

    function make_theme_settings_arr($array)
    {
        return current(array_filter($array));
    }

    // Needs checking

    private function getOAuthAccessTokenURL(): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/oauth/access_token";
    }

    private function getRecurringApplicationChargeURL(): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/recurring_application_charges.json";
    }

    private function getRecurringApplicationChargeViewURL($charge_id): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/recurring_application_charges/$charge_id.json";
    }

    private function getRecurringApplicationChargeActivationURL($charge_id): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/recurring_application_charges/$charge_id/activate.json";
    }

    private function getUsageChargeURL($charge_id): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/recurring_application_charges/$charge_id/usage_charges.json";
    }

    private function getCreateWebhookSubscriptionURL(): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/webhooks.json";
    }

    private function getStorefrontAccessTokenURL(): string
    {
        $shop_name = $this->shop->myshopify_domain;
        return "https://$shop_name/admin/api/" . $this->api_version . "/storefront_access_tokens.json";
    }

    public function getOAuthURL(string $redirect_uri): string
    {
        $this->shop->update([
            'nonce' => uniqid()
        ]);

        $params = [
            'client_id' => $this->client_id,
            'redirect_uri' => $redirect_uri,
            'scope' => $this->shop->scope,
            'state' => $this->shop->nonce
        ];

        return $this->getOAuthAuthorizeURL() . "?" . http_build_query($params);
    }

    /**
     * @param string $temporary_code
     * @throws GuzzleException
     */
    public function fetchPermanentAccessToken(string $temporary_code)
    {
        $client = new Client();
        $res = $client->request('POST', $this->getOAuthAccessTokenURL(), [
            'form_params' => [
                'client_id' => $this->client_id,
                'client_secret' => $this->client_secret,
                'code' => $temporary_code,
            ]
        ]);

        $response = json_decode($res->getBody()->getContents());

        $this->shop->update([
            'access_token' => $response->access_token,
            'scope' => $response->scope,
            'active' => 1,
            'nonce' => null,
            'new' => 0,
            'initializing' => false,
            'initialized' => false
        ]);
    }

    function createStorefrontAccessToken()
    {
        $client = new Client();
        $res = $client->request('POST', $this->getStorefrontAccessTokenURL(), [
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'storefront_access_token' => [
                    'title' => "Storefront Token"
                ]
            ]
        ]);

        $response = json_decode($res->getBody()->getContents(), true);
        $token = $response['storefront_access_token'] ?? [];
        return $token['access_token'] ?? '';
    }

    /**
     * @param Plan $plan
     * @param string $return_url
     * @return ShopifyRecurringApplicationCharge
     * @throws \Exception|GuzzleException
     */
    public function createRecurringApplicationCharge(Plan $plan, string $return_url)
    {
        $tester_emails = [
            'ankitsrivasta0193@gmail.com',
            'emailwishserver@gmail.com',
            'emailwish.tester.1@gmail.com',
            'gurjap@volobot.com',
            'support@bluekyoto.com',
            'shsumair8@gmail.com',
        ];
        $email = $this->shop->customer->user->email;
        $test = in_array($email, $tester_emails);
        if (!config('shopify.live_payments'))
            $test = true;

        try {
            $client = new Client();
            $res = $client->request('POST', $this->getRecurringApplicationChargeURL(), [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shop->access_token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'recurring_application_charge' => [
                        'name' => $plan->name,
                        'price' => $plan->price,
                        'return_url' => $return_url . '?shop=' . $this->shop->myshopify_domain,
                        'capped_amount' => $plan->getMaximumUsageChargeAttribute(),
                        'terms' => "$" . $plan->usage_rate . " per 1000 emails",
                        'test' => $test,
                        'trial_days' => TrialRecord::recordExists($this->shop->myshopify_domain) ? 0 : 14
                    ]
                ]
            ]);
        } catch (RequestException $exception) {
            throw new \Exception($exception->getMessage());
        }

        $response = json_decode($res->getBody()->getContents(), true);
        if (!$response || empty($response['recurring_application_charge']))
            throw new \Exception("Failed to create recurring charge");

        return ShopifyRecurringApplicationCharge::storeRecurringApplicationCharge($this->shop, $plan, $response['recurring_application_charge']);
    }

    public function getRecurringApplicationChargeStatus(ShopifyRecurringApplicationCharge $charge): ShopifyRecurringApplicationCharge
    {
        try {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/recurring_application_charges/" . $charge->shopify_id . ".json";
            $client = new Client();
            $res = $client->request('GET', $url, [
                'exceptions' => FALSE,
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shop->access_token,
                    'Content-Type' => 'application/json',
                ],
            ]);

            $response = json_decode($res->getBody()->getContents(), true);
            if (!$response || empty($response['recurring_application_charge'])) {
                $charge->status = ShopifyRecurringApplicationCharge::STATUS_unknown;
                $charge->save();
                return $charge;
            }

            $charge->status = $response['recurring_application_charge']['status'] ?? ShopifyRecurringApplicationCharge::STATUS_unknown;
            $charge->save();
            return $charge;
        } catch (RequestException $exception) {
            $charge->status = ShopifyRecurringApplicationCharge::STATUS_unknown;
            $charge->save();
            return $charge;
        }
    }

    public function createUsageCharge(ShopifyRecurringApplicationCharge $charge, MonthlyCharge $monthlyCharge)
    {
        if ($monthlyCharge->usage_charges <= 0) {
            $monthlyCharge->usage_charges_billed = true;
            $monthlyCharge->save();
            return true;
        }

        try {
            $client = new Client();
            $res = $client->request('POST', $this->getUsageChargeURL($charge->shopify_id), [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shop->access_token,
                    'Content-Type' => 'application/json',
                ],
                'json' => [
                    'usage_charge' => [
                        'description' => $monthlyCharge->getUsageChargeDescription(),
                        'price' => $monthlyCharge->usage_charges,
                    ]
                ]
            ]);
        } catch (RequestException $exception) {
            return false;
        }

        $response = json_decode($res->getBody()->getContents(), true);
        if (!$response || empty($response['usage_charge']))
            return false;

        $monthlyCharge->usage_charges_billed = true;
        $monthlyCharge->save();
        return true;
    }

    function getRegisteredWebhookTopics(): array
    {
        $webhooks = $this->getWebhookSubscriptions();
        $topics = [];
        foreach ($webhooks as $webhook) {
            $topic = $webhook['topic'] ?? "";
            if ($topic)
                $topics[] = $topic;
        }

        return $topics;
    }

    function isShopActive(): bool
    {
        try {
            $shop_name = $this->shop->myshopify_domain;
            $url = "https://$shop_name/admin/api/" . $this->api_version . "/webhooks.json";

            $client = new Client();
            $client->request('GET', $url, [
                'headers' => [
                    'X-Shopify-Access-Token' => $this->shop->access_token,
                    'Content-Type' => 'application/json',
                ]
            ]);
            return true;
        } catch (GuzzleException $e) {
            return false;
        }
    }

    function getWebhookSubscriptions()
    {
        $shop_name = $this->shop->myshopify_domain;
        $url = "https://$shop_name/admin/api/" . $this->api_version . "/webhooks.json";

        $client = new Client();
        $res = $client->request('GET', $url, [
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);
        $response = json_decode($res->getBody()->getContents(), true);
        return $response['webhooks'] ?? [];
    }

    function createWebhookSubscription($topic)
    {
        // Skip this for local installations
        if (Str::contains(url(''), 'localhost'))
            return true;

        $client = new Client();
        $res = $client->request('POST', $this->getCreateWebhookSubscriptionURL(), [
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'webhook' => [
                    'topic' => $topic,
                    'address' => route('shopifyWebhookUrl'),
                    'format' => "json"
                ]
            ]
        ]);
        json_decode($res->getBody()->getContents(), true);
        return true;
    }

    public function activateRecurringApplicationCharge(ShopifyRecurringApplicationCharge $charge): ?ShopifyRecurringApplicationCharge
    {
        $client = new Client();
        $res = $client->request('GET', $this->getRecurringApplicationChargeViewURL($charge->shopify_id), [
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        $response = json_decode($res->getBody()->getContents(), true);
        if ($response['recurring_application_charge']['status'] != 'active') {
            Log::debug($response['recurring_application_charge']['status']);
            return null;
        }

        // Mark all other charges in the current shop as cancelled
        foreach ($charge->customer->shopifyRecurringApplicationCharges as $c) {
            $c->status = ShopifyRecurringApplicationCharge::STATUS_cancelled;
            $c->save();
        }

        $charge->data = json_encode($response['recurring_application_charge']) ?? "";
        $charge->status = $response['recurring_application_charge']['status'] ?? "";
        $charge->save();

        TrialRecord::storeRecord($this->shop);
        return $charge;
    }

    public static function syncDiscountCodesAndWebhooks()
    {
        /** @var ShopifyShop[] $shops */
        $shops = ShopifyShop::where('active', '=', true)
            ->where('initialized', true)
            ->where('token_refresh_required', false)
            ->get();
        print("Syncing discount codes for  " . count($shops) . " shops" . PHP_EOL);

        foreach ($shops as $shop) {
            try {
                $helper = new ShopifyHelper($shop);

                if (!$helper->isShopActive()) {
                    $shop->active = 0;
                    $shop->save();
                    continue;
                }

                $registered_topics = $helper->getRegisteredWebhookTopics();
                foreach (ShopifyWebhookController::TOPICS_REQUIRED as $topic) {
                    if (!in_array($topic, $registered_topics))
                        $helper->createWebhookSubscription($topic);
                }
                $helper->getAllPriceRules();
                foreach (ShopifyPriceRule::findByShop($shop) as $shopify_price_rule) {
                    $helper->getAllDiscountCodes($shopify_price_rule->shopify_id);
                }
            } catch (\Exception $e) {
                print($e);
            }
        }
    }

    public static function initShops()
    {
        /** @var ShopifyShop[] $shops */
        $shops = ShopifyShop::where('active', '=', true)
            ->where('initialized', false)
            ->where('initializing', false)
            ->where('token_refresh_required', false)
            ->limit(100)
            ->get();
        print("Initializing " . count($shops) . " shops" . PHP_EOL);

        foreach ($shops as $shop) {
            try {
                $helper = new ShopifyHelper($shop);
                $shop->initializing = true;
                $shop->save();

                if (!$helper->isShopActive()) {
                    $shop->active = 0;
                    $shop->save();
                    continue;
                }

                $registered_topics = $helper->getRegisteredWebhookTopics();
                foreach (ShopifyWebhookController::TOPICS_REQUIRED as $topic) {
                    if (!in_array($topic, $registered_topics))
                        $helper->createWebhookSubscription($topic);
                }
                $helper->installWidgetScriptTag();
                $helper->insertReviewSnippet();
                $helper->getAllPriceRules();
                foreach (ShopifyPriceRule::findByShop($shop) as $shopify_price_rule) {
                    $helper->getAllDiscountCodes($shopify_price_rule->shopify_id);
                }
                $helper->getAllProducts();
                $helper->getAllCustomers();
                $helper->getAllOrders();
                $helper->getAllAbandonedCheckouts();
                $helper->getAllBlogs();
                $helper->getAllPages();

                $shop->initialized = true;
                $shop->save();
            } catch (\Exception $e) {
                $shop->initializing = false;
                $shop->save();
                print($e);
            }
        }
    }

    public function getAllDiscountCodes($price_rule_id, $created_at_max = null)
    {
        print($this->shop->myshopify_domain . " Fetching discount codes" . PHP_EOL);
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/price_rules/$price_rule_id/discount_codes.json";
        $client = new Client();
        $query = ['limit' => 250];
        if ($created_at_max) {
            $query['created_at_max'] = $created_at_max;
        }
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'query' => $query
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);
        $last_time_stamp = null;
        if ($json && !empty($json['discount_codes'])) {
            foreach ($json['discount_codes'] as $item) {
                $model = ShopifyDiscountCode::storeDiscountCode($this->shop, $item);
                if ($model)
                    $last_time_stamp = $model->getShopifyModel()->created_at;
                print($this->shop->myshopify_domain . " Got discount code" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }
        if ($last_time_stamp && $last_time_stamp != $created_at_max) {
            $this->getAllDiscountCodes($price_rule_id, $last_time_stamp);
        }
    }

    public function getAllPriceRules($created_at_max = null)
    {
        print($this->shop->myshopify_domain . " Fetching price rules" . PHP_EOL);
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/price_rules.json";
        $client = new Client();
        $query = ['limit' => 250];
        if ($created_at_max) {
            $query['created_at_max'] = $created_at_max;
        }
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'query' => $query
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);
        $last_time_stamp = null;
        if ($json && !empty($json['price_rules'])) {
            foreach ($json['price_rules'] as $item) {
                $model = ShopifyPriceRule::storePriceRule($this->shop, $item);
                if ($model)
                    $last_time_stamp = $model->getShopifyModel()->created_at;
                print($this->shop->myshopify_domain . " Got price rule" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }
        if ($last_time_stamp && $last_time_stamp != $created_at_max) {
            $this->getAllPriceRules($last_time_stamp);
        }
    }

    public function getAllProducts($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching products" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/products.json?limit=250";
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);
        if ($json && !empty($json['products'])) {
            foreach ($json['products'] as $item) {
                $model = ShopifyProduct::storeProduct($this->shop, $item);
                if ($model)
                    print($this->shop->myshopify_domain . " Got product" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllProducts($links['next']['link']);
            }
        }
    }

    public function getAllCustomers($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching customers" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/customers.json?limit=250";
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);

        if ($json && !empty($json['customers'])) {
            foreach ($json['customers'] as $item) {
                $model = ShopifyCustomer::storeCustomer($this->shop, $item);
                if ($model)
                    print($this->shop->myshopify_domain . " Got customer" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllCustomers($links['next']['link']);
            }
        }
    }

    public function getAllOrders($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching Orders" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/orders.json?status=any&limit=250";
        }
        print($url);

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);

        $ids = [];

        if ($json && !empty($json['orders'])) {
            foreach ($json['orders'] as $item) {
                $model = ShopifyOrder::storeOrder($this->shop, $item);
                if ($model)
                    $ids[] = $model->shopify_id;
            }
            print($this->shop->myshopify_domain . ": Got " . count($json['orders']) . " orders" . PHP_EOL);
            print_r($ids);
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllOrders($links['next']['link']);
            }
        }
    }

    public function getAllAbandonedCheckouts($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching AbandonedCheckouts" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/checkouts.json?limit=250";
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents . true);
        if ($json && !empty($json['checkouts'])) {
            foreach ($json['checkouts'] as $item) {
                $model = ShopifyCheckout::storeCheckout($this->shop, $item);
                if ($model)
                    print($this->shop->myshopify_domain . " Got AbandonedCheckout" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllAbandonedCheckouts($links['next']['link']);
            }
        }
    }

    public function getAllBlogs($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching blogs" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/blogs.json?limit=250";
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);
        if ($json && !empty($json['blogs'])) {
            foreach ($json['blogs'] as $item) {
                $model = ShopifyBlog::storeBlog($this->shop, $item);
                if ($model)
                    print($this->shop->myshopify_domain . " Got blog" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllBlogs($links['next']['link']);
            }
        }
    }

    public function getAllPages($url_with_page_info = null)
    {
        print($this->shop->myshopify_domain . " Fetching pages" . PHP_EOL);

        if ($url_with_page_info) {
            $url = $url_with_page_info;
        } else {
            $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/pages.json?limit=250";
        }

        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ]
        ]);

        if ($response->getStatusCode() != 200) {
            return;
        }

        $body_contents = $response->getBody()->getContents();
        $json = json_decode($body_contents, true);
        if ($json && !empty($json['pages'])) {
            foreach ($json['pages'] as $item) {
                $model = ShopifyPage::storePage($this->shop, $item);
                if ($model)
                    print($this->shop->myshopify_domain . " Got page" . ($model ? $model->getShopifyModel()->id : "false") . PHP_EOL);
            }
        }

        $link_headers = $response->getHeader('Link');
        if (count($link_headers)) {
            $links = (new ParseLinkHeader($link_headers[0]))->toArray();
            if (!empty($links['next']) && !empty($links['next']['link'])) {
                $this->getAllBlogs($links['next']['link']);
            }
        }
    }

    public function createScriptTag($scriptUrl)
    {
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/script_tags.json";
        $client = new Client();
        $response = $client->request('POST', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'script_tag' => [
                    'event' => 'onload',
                    'src' => $scriptUrl
                ]
            ]
        ]);

        if ($response->getStatusCode() >= 300)
            return false;

        $responseArray = json_decode($response->getBody(), true);

        if (empty($responseArray['script_tag']))
            return false;

        if (empty($responseArray['script_tag']['id']))
            return false;

        return $responseArray['script_tag']['id'];
    }

    public function updateScriptTag($scriptUrl, $id)
    {
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/script_tags/$id.json";
        $client = new Client();
        $response = $client->request('PUT', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'script_tag' => [
                    'id' => $id,
                    'src' => $scriptUrl
                ]
            ]
        ]);

        if ($response->getStatusCode() >= 300)
            return false;

        $responseArray = json_decode($response->getBody(), true);

        if (empty($responseArray['script_tag']))
            return false;

        if (empty($responseArray['script_tag']['id']))
            return false;

        return true;
    }

    public function unInstallScriptTag($scriptId): bool
    {
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/script_tags/" . $scriptId . ".json";
        $client = new Client();
        $response = $client->request('DELETE', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() >= 300)
            return false;

        return true;
    }

    public function getScriptTag($scriptId): bool
    {
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/script_tags/" . $scriptId . ".json";
        $client = new Client();
        $response = $client->request('GET', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
        ]);

        if ($response->getStatusCode() == 404)
            return false;

        return true;
    }

    function isWidgetScriptInstalled(): bool
    {
        // Skip this for local installations
        if (Str::contains(url(''), 'localhost'))
            return true;

        return $this->getScriptTag($this->shop->widget_script_tag_id);
    }

    public function createDefaultAutomationsAndPopups()
    {
        if (!$this->shop->new) return;
        $customer = $this->shop->customer;

        $automations = Automation2::getPublicAutomations();
        foreach ($automations as $automation) {
            $new = $automation->makeCopy($automation->name, $customer);
            if($new) {
                $new->status = Automation2::STATUS_ACTIVE;
                $new->save();
            }
        }

        $popups = Popup::getPublicPopups();
        foreach ($popups as $popup) {
            $popup->makeCopy($popup->title, $customer);
        }
        $this->shop->new = false;
        $this->shop->save();

        $customer->automations_imported_at = Carbon::now();
        $customer->popups_imported_at = Carbon::now();
        $customer->save();
    }

    public function installWidgetScriptTag(): bool
    {
        // Skip this for local installations
        if (Str::contains(url(''), 'localhost'))
            return true;

        $widget_host = config('widgets.host');
        $widget_version = config('widgets.version');
        $client_id = $this->shop->customer->uid;
        $scriptUrl = "$widget_host/embed.emailwish.js?v=$widget_version&client-id=$client_id";

        if ($this->shop->widget_script_tag_id) {
            return $this->updateScriptTag($scriptUrl, $this->shop->widget_script_tag_id);
        }

        $tag_id = $this->createScriptTag($scriptUrl);
        if ($tag_id) {
            $this->shop->widget_script_tag_id = $tag_id;
            $this->shop->save();
            return true;
        }
        return false;
    }

    function createPriceRule($discount_code, $percentage)
    {
        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/price_rules.json";
        $client = new Client();
        $response = $client->request('POST', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'price_rule' => [
                    "title" => $discount_code,
                    "target_type" => "line_item",
                    "target_selection" => "all",
                    "allocation_method" => "across",
                    "value_type" => "percentage",
                    "value" => "-$percentage",
                    "customer_selection" => "all",
                    "starts_at" => Carbon::now()->toISOString()
                ]
            ]
        ]);

        if ($response->getStatusCode() >= 300)
            return false;

        $responseArray = json_decode($response->getBody(), true);

        if (empty($responseArray['price_rule']))
            return false;

        if (empty($responseArray['price_rule']['id']))
            return false;

        return $responseArray['price_rule']['id'];
    }

    function createDiscountCode($discount_code, $percentage)
    {
        $price_rule_id = $this->createPriceRule($discount_code, $percentage);
        if ($price_rule_id === false) return false;

        $url = "https://" . $this->shop->myshopify_domain . "/admin/api/" . $this->api_version . "/price_rules/" . $price_rule_id . "/discount_codes.json";
        $client = new Client();
        $response = $client->request('POST', $url, [
            'exceptions' => FALSE,
            'headers' => [
                'X-Shopify-Access-Token' => $this->shop->access_token,
                'Content-Type' => 'application/json',
            ],
            'json' => [
                'discount_code' => [
                    "code" => $discount_code
                ]
            ]
        ]);

        if ($response->getStatusCode() >= 300)
            return false;

        $responseArray = json_decode($response->getBody(), true);

        if (empty($responseArray['discount_code']))
            return false;

        if (empty($responseArray['discount_code']['id']))
            return false;

        return $responseArray['discount_code']['id'];
    }

    // region Untested code
    function create_discount_code__deprecated()
    {
        $prerequisite_customer_ids = array('123456');
        $current_time_full_date = strtotime(date('Y-m-d H:i:s'));
        $date_obj = new DateTime("$current_time_full_date");  // convert UNIX timestamp to PHP DateTime
        $starts_at = $date_obj->format('c');

        $price_rule_req_arr = array("price_rule" =>
            array(
                "title" => "10OFF10",
                "target_type" => "line_item",
                "target_selection" => "all",
                "allocation_method" => "across", /* The calculated discount amount will be applied across the entitled items. For example, for a price rule that takes $15 off, the discount will be applied across all the entitled items. */
                "value_type" => "percentage", /* fixed_amount */
                "value" => "10", /* here we give 10% off*/
                "once_per_customer" => false,
                "usage_limit" => 1,
                "customer_selection" => "prerequisite",
                "prerequisite_customer_ids" => $prerequisite_customer_ids,
                "starts_at" => $starts_at,
                //"ends_at" =>
            )
        );

        $end_point = "price_rules.json";
        $price_rule_api_resp = $this->fetchFromShopifyApi($end_point, $price_rule_req_arr, "POST");

        if (isset($price_rule_api_resp->price_rule) && isset($price_rule_api_resp->price_rule->id)) {

            $discount_req_arr = array("discount_code" =>
                array(
                    "code" => "10OFF10"
                )
            );

            $end_point = "price_rules/" . $price_rule_api_resp->price_rule->id . "/discount_codes.json";
            $discount_api_resp = $this->fetchFromShopifyApi($end_point, $discount_req_arr, "POST");

            if (isset($discount_code_details->discount_code) && isset($discount_code_details->discount_code->id)) {
                $autoapplied_link = 'https://shopifystore.myshopify.com/discount/10OFF10';
            }
        }
    }
    // endregion
}