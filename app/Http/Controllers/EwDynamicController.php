<?php


namespace Acelle\Http\Controllers;

use Acelle\Helpers\EmailImageHelper;
use Acelle\Helpers\ShopifyHelper;
use Acelle\Library\Automation\DynamicWidgetConfig\CartConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\ChatConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\OrderConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\ProductConfig;
use Acelle\Library\Automation\DynamicWidgetConfig\Products3Config;
use Acelle\Library\ExtendedSwiftMessage;
use Acelle\Library\StringHelper;
use Acelle\Model\ChatSession;
use Acelle\Model\Customer;
use Acelle\Model\GalleryImage;
use Acelle\Model\ShopifyCheckout;
use Acelle\Model\ShopifyDiscountCode;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyShop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Swift_Mime_ContentEncoder_PlainContentEncoder;
use TijsVerkoyen\CssToInlineStyles\CssToInlineStyles;

class EwDynamicController extends Controller
{
    function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    function preview($type)
    {
        switch ($type) {
            case 'chat':
                return view('ew_dynamic.preview.chat');
            case 'order':
                return view('ew_dynamic.preview.order');
            case 'cart':
                return view('ew_dynamic.preview.cart');
            case 'products3':
                return view('ew_dynamic.preview.products3');
            case 'product':
            default:
                return view('ew_dynamic.preview.product');
        }
    }

    function setup($type)
    {
        switch ($type) {
            case 'chat':
                return view('ew_dynamic.setup.chat');
            case 'order':
                return view('ew_dynamic.setup.order');
            case 'cart':
                return view('ew_dynamic.setup.cart');
            case 'products3':
                return view('ew_dynamic.setup.products3');
            case 'product':
            default:
                return view('ew_dynamic.setup.product');
        }
    }

    function select2_product(Request $request)
    {
        $query = $request->get('q');
        /** @var ShopifyProduct[] $models */
        $models = ShopifyProduct::query()
            ->where(ShopifyProduct::COLUMN_customer_id, $request->selected_customer->id)
            ->where(ShopifyProduct::COLUMN_shopify_title, "like", "%$query%")
            ->limit(10)
            ->get();

        $items = [];
        foreach ($models as $model) {
            $items[] = [
                'id' => $model->id,
                'text' => $model->shopify_title,
                'data' => $model->data
            ];
        }
        $data = ['items' => $items, 'more' => true];
        return json_encode($data);
    }

    function best_selling_products(Request $request): JsonResponse
    {
        $models = ShopifyProduct::query()
            ->where(ShopifyProduct::COLUMN_customer_id, $request->selected_customer->id)
            ->orderBy(ShopifyProduct::COLUMN_units_sold, "desc");

        return response()->json([
            'products' => $models->paginate()
        ]);
    }

    function discount_codes(Request $request): JsonResponse
    {
        /** @var ShopifyDiscountCode[] $codes */
        $codes = ShopifyDiscountCode::query()
            ->where(ShopifyDiscountCode::COLUMN_customer_id, $request->selected_customer->id)
            ->get();

        $items = [];
        foreach ($codes as $code) {
            $items[] = ['id' => $code->discount_code, 'text' => $code->discount_code];
        }
        return response()->json(['items' => $items]);
    }

    function images(Request $request): JsonResponse
    {
        $items = GalleryImage::query()
            ->where(GalleryImage::COLUMN_customer_id, $request->selected_customer->id)
            ->orderBy(GalleryImage::COLUMN_id, "desc")
            ->paginate();
        return response()->json($items);
    }
   
    function query(Request $request): JsonResponse
    {
        /** @var ShopifyShop $shop */
        $shop = $request->selected_customer->shopify_shop;
        

        $request->validate([
            'target' => 'required|string|in:products,collections,tags,pages,blogs,articles',
            'query' => 'string',
            'cursor' => 'string'
        ]);
        $target = $request->input('target');
        $query = $request->input('query');
        $cursor = $request->input('cursor');
        $after = $cursor != "" ? ", after: \"$cursor\"" : "";
        $graphql_query = "{}";
        switch ($target) {
            case "tags":
                $tags = $this->tagLinks($request);
                return response()->json($tags);
            default :
                    $graphql_query = "{ $target(query: \"$query\", first: 30$after) { 
                        edges { 
                            cursor
                            node { 
                                handle 
                                title 
                            }
                        }
                        pageInfo{
                            hasNextPage
                        }
                    }
                }";
                    break;
        }
        $helper = new ShopifyHelper($shop);
        if($target == "pages" || $target == "articles" || $target == "blogs"){
            $array = json_decode(json_encode($helper->queryStoreFront($graphql_query)), true);
        }else{
            $array = json_decode(json_encode($helper->graphQLQuery($graphql_query)), true);
        }
        
        $array['data']['shop'] = "https://" . $shop->myshopify_domain . "/" . $target . "/";
        return response()->json($array);
    }

    function blogs(Request $request): JsonResponse
    {
        /** @var ShopifyShop $shop */
        $shop = $request->selected_customer->shopify_shop;

        // $request->validate([
        //     'query' => 'required|string',
        // ]);

        $blogs = $shop->shopify_blogs()->search($shop)->paginate();
        return response()->json(['items' => $blogs]);
    }

    function pages(Request $request): JsonResponse
    {
        /** @var ShopifyShop $shop */
        $shop = $request->selected_customer->shopify_shop;

        // $request->validate([
        //     'query' => 'required|string',
        // ]);

        $pages = $shop->shopify_pages()->search($shop)->paginate();
        return response()->json(['items' => $pages]);
    }
    function send_email(Request $request): JsonResponse
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $data = $request->validate([
            'content' => 'required|string',
            'recipient' => 'required|string|email|max:100',
        ]);
        $server = $customer->shopify_shop->mail_list->pickSendingServer();

        // STEP 01. Get RAW content
        $body = $data['content'] ?? '';
        $body = StringHelper::removeDataImg($body);
        $body = ChatSession::getHtmlRepresentation($body,$request->selected_customer, "", $data['recipient'] ?? '', "");
        $body = ShopifyProduct::getBestSellingHtml($body,$request->selected_customer);
        $body = StringHelper::replaceBareLineFeed($body);
        $msgId = StringHelper::generateMessageId(StringHelper::getDomainFromEmail($server->default_from_email));
        $body = $this->tagMessage($request, $body);
        $body = $this->replaceDynamicWidgets($body);
        $body = $this->saveImages($body, $customer);

        $body = $this->inlineHtml($body);
        $files = [
            public_path('/cb/assets/minimalist-blocks/contentmedia.css'),
            public_path('/cb/assets/minimalist-blocks/contentmedia1.css'),
            public_path('/cb/assets/minimalist-blocks/contentmedia2.css')
        ];
        $css = "";
        foreach ($files as $file)
            $css .= '<style>' . file_get_contents($file) . '</style>';
        // Additional Step: Add HTML
        $body = '<!DOCTYPE HTML>
<html>
<head>
    <meta charset="utf-8">
    <title>Email Preview</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    ' . $css . '
</head>
<body>
' . $body . '
</body>
</html>';

        $message = new ExtendedSwiftMessage();
        $message->setId($msgId);
        $message->setContentType('text/html; charset=utf-8');
        $message->setSubject("Email Preview");
        $message->setFrom($server->default_from_email);
        $message->setTo($data['recipient'] ?? '');
        $message->setEncoder(new Swift_Mime_ContentEncoder_PlainContentEncoder('8bit'));
        $message->addPart($this->getPlainContent($body), 'text/plain');
        $message->addPart($body, 'text/html');
        $message = $server->sign($message);
        $RES = $server->send($message);

        return response()->json($RES);
    }

    function getPlainContent($html)
    {
        $style_removed = preg_replace('/<style>.*?<\/style>/s', '', $html);
        $with_newlines = str_replace(["</div>", "</p>", "</h1>", "</h2>", "</h3>", "&nbsp;"], ["</div>\n", "</p>\n", "</h1>\n", "</h2>\n", "</h3>\n", "\n"], $style_removed);
        $single_newlines = str_replace("\n\n", "\n", $with_newlines);
        return preg_replace('/[\t\f ]+/', ' ', preg_replace('/[\r\n]+/', "\n", strip_tags($single_newlines)));
    }
    public function tagLinks(Request $request){
        $sig = $request->selected_customer->signature;
        $website = 
        $tagLinks = [
            ['title' => 'Website','link' => $sig ? $sig->website : ""],
            ['title' => 'Email','link' => "mailto:" . $request->selected_customer->user->email],
            ['title' => 'Facebook','link' => $sig ? $sig->facebook : "",],
            ['title' => 'Instagram','link' => $sig ? $sig->instagram : "",],
            ['title' => 'LinkedIn','link' => $sig ? $sig->linkedin : "",],
            ['title' => 'Twitter','link' => $sig ? $sig->twitter : "",],
            ['title' => 'Skype','link' => $sig ? $sig->skype : "",],
            ['title' => 'Youtube','link' => $sig ? $sig->youtube : "",],
            ['title' => 'TikTok','link' => $sig ? $sig->tiktok : "",],
            ['title' => 'Pinterest','link' => $sig ? $sig->pinterest : "",]
        ];
        return $tagLinks;
    }
    public function tagMessage(Request $request, $message)
    {
        $sig = $request->selected_customer->signature;
        $shop = $request->selected_customer->shopify_shop;

        $tags = array(
            'IN_WEBSITE' => $sig ? $sig->website : "",
            'IN_ADDRESS' => $request->selected_customer->getContact()->getAddress(),
            'IN_BRANDNAME' => $request->selected_customer->shopify_shop->name,
            'IN_EMAIL' => $request->selected_customer->user->email,
            'IN_FIRSTNAME' => $request->selected_customer->user->first_name,
            'IN_FULLNAME' => $sig ? $sig->full_name : "",
            'IN_POSITION' => $sig ? $sig->designation : "",
            'IN_PHONE' => $sig ? $sig->phone : "",
            'IN_FACEBOOK' => $sig ? $sig->facebook : "",
            'IN_INSTAGRAM' => $sig ? $sig->instagram : "",
            'IN_LINKEDIN' => $sig ? $sig->linkedin : "",
            'IN_TWITTER' => $sig ? $sig->twitter : "",
            'IN_SKYPE' => $sig ? $sig->skype : "",
            'IN_YOUTUBE' => $sig ? $sig->youtube : "",
            'IN_TIKTOK' => $sig ? $sig->tiktok : "",
            'IN_PINTEREST' => $sig ? $sig->pinterest : "",
            'IN_LOGO_URL' => $sig && $sig->logo_url ? $sig->logo_url : "/cb/assets/minimalist-blocks/images/logoplaceholder.png",
            'IN_THEME_FONT_FAMILY' => $shop->theme['font_family'] ?? 'Roboto',
            'IN_THEME_PRIMARY_BACKGROUND_COLOR' => $shop->theme['primary_background_color'] ?? '#000000ff',
            'IN_THEME_PRIMARY_TEXT_COLOR' => $shop->theme['primary_text_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_BACKGROUND_COLOR' => $shop->theme['secondary_background_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_TEXT_COLOR' => $shop->theme['secondary_text_color'] ?? '#000000ff',
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $request->selected_customer->getContact()->company,
            'CONTACT_COUNTRY' => $request->selected_customer->getContact()->countryName(),
            'CONTACT_STATE' => $request->selected_customer->getContact()->state,
            'CONTACT_CITY' => $request->selected_customer->getContact()->city,
            'CONTACT_ADDRESS_1' => $request->selected_customer->getContact()->address_1,
            'CONTACT_ADDRESS_2' => $request->selected_customer->getContact()->address_2,
            'CONTACT_PHONE' => $request->selected_customer->getContact()->phone,
            'CONTACT_URL' => $request->selected_customer->getContact()->url,
            'CONTACT_EMAIL' => $request->selected_customer->getContact()->email,
            'LIST_NAME' => $request->selected_customer->shopify_shop->mail_list->name,
            'LIST_SUBJECT' => $request->selected_customer->shopify_shop->mail_list->default_subject,
            'LIST_FROM_NAME' => $request->selected_customer->shopify_shop->mail_list->from_name,
            'LIST_FROM_EMAIL' => $request->selected_customer->shopify_shop->mail_list->from_email,
        );

        /** @var ShopifyDiscountCode[] $discount_codes */
        $discount_codes = ShopifyDiscountCode::findByCustomer($request->selected_customer->id);
        foreach ($discount_codes as $code) {
            if ($code->shopify_price_rule && $code->shopify_price_rule->getShopifyModel())
                $tags['DISCOUNT.' . $code->discount_code . '.VALUE'] = $code->shopify_price_rule->getShopifyModel()->value;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_ireplace('{' . $tag . '}', $value, $message);
        }

        return $message;
    }


    function replaceDynamicWidgets($body)
    {
        // find all iframe tags in html content
        preg_match_all('/(<iframe[^>]+src=)([\'"])(?<src>.*?)(\2)[^>]*>/i', $body, $matches);
        foreach ($matches as $match) {
            if (empty($match))
                continue;
            $tag = $match[0];
            $src = parse_url($match['src']);

            $new_tag = "";
            if ($src['path'] == "/ew_dynamic/iframe") {
                parse_str($src['query'], $query);
                switch ($query['type'] ?? '') {
                    case 'product':
                        $new_tag = ShopifyProduct::getHtmlRepresentation(new ProductConfig(null, $src['query']));
                        break;
                    case 'products3':
                        $new_tag = ShopifyProduct::getProduct3HtmlRepresentation(new Products3Config($src['query']));
                        break;
                    case 'order':
                        $new_tag = ShopifyOrder::getHtmlRepresentation(null, new OrderConfig($src['query']));
                        break;
                    case 'abandoned_cart':
                        $new_tag = ShopifyCheckout::getHtmlRepresentation(null, new CartConfig($src['query']));
                        break;
                    case 'chat':
                        $new_tag = ChatSession::getHtmlRepresentation(null, new ChatConfig($src['query']));
                        break;
                }
            }

            $body = str_replace($tag, $new_tag, $body);
        }

        return $body;
    }


    public function saveImages($content, $customer)
    {
        // find all img tags in html content
        preg_match_all('/(<img[^>]+src=)([\'"])(?<src>.*?)(\2)/i', $content, $matches);
        $srcs = array_unique($matches['src']);

        foreach ($srcs as $src) {
            if (empty(trim($src))) continue;
            $asset_url_root = route('test_email_assets', ['uid' => $customer->uid], false);
            $storage_folder = storage_path('app/users/' . $customer->uid . '/test_mail_assets/');
            $new_path = EmailImageHelper::saveImage($asset_url_root, $storage_folder, $src);
            $new_path = URL::asset($new_path);
            try {
                $content = preg_replace('/(<img[^>]+src=)([\'"])(' . preg_quote($src, '/') . ')(\2)/', '$1$2' . $new_path . '$4', $content);
            } catch (\ErrorException $ex) {
                // preg_replace failed
                // use the old and non-safe way!!!!
                $content = str_replace($src, $new_path, $content);
            }

        }

        return $content;
    }

    public function inlineHtml($html)
    {
        // Convert to inline css
        $cssToInlineStyles = new CssToInlineStyles();
        $css = "";
        $files = [
            public_path('/cb/assets/minimalist-blocks/content.css'),
            public_path('/cb/assets/custom.css'),
            // public_path('/cb/contentbuilder/contentbuilder.css')
        ];

        foreach ($files as $file)
            $css .= file_get_contents($file);

        // output
        $html = $cssToInlineStyles->convert(
            $html,
            $css
        );

        return $html;
    }
}