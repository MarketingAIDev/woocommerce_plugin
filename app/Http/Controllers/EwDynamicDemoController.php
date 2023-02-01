<?php


namespace Acelle\Http\Controllers;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Model\GalleryImage;
use Acelle\Model\ShopifyShop;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EwDynamicDemoController extends Controller
{
    function discount_codes(): JsonResponse
    {
        $items = [];
        $items[] = ['id' => "DISCOUNT_CODE_1", 'text' => "DISCOUNT_CODE_1"];
        $items[] = ['id' => "DISCOUNT_CODE_2", 'text' => "DISCOUNT_CODE_2"];
        $items[] = ['id' => "DISCOUNT_CODE_3", 'text' => "DISCOUNT_CODE_3"];
        $items[] = ['id' => "DISCOUNT_CODE_4", 'text' => "DISCOUNT_CODE_4"];
        $data = ['items' => $items];
        return response()->json($data);
    }

    function select2_product(): JsonResponse
    {
        return response()->json([
            "items" => [
                [
                    "id" => 4,
                    "text" => "sample product",
                    "data" => "{\"id\": 6861983219891, \"tags\": \"\", \"image\": {\"id\": 29593615106227, \"alt\": null, \"src\": \"https:\/\/cdn.shopify.com\/s\/files\/1\/0580\/4359\/6979\/products\/blogger-boz-579bedef5f9b589aa9915064.jpg?v=1629612431\", \"width\": 3722, \"height\": 2965, \"position\": 1, \"created_at\": \"2021-08-22T11:37:11+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2021-08-22T11:37:11+05:30\", \"variant_ids\": [], \"admin_graphql_api_id\": \"gid:\/\/shopify\/ProductImage\/29593615106227\"}, \"title\": \"sample product\", \"handle\": \"sample-product\", \"images\": [{\"id\": 29593615106227, \"alt\": null, \"src\": \"https:\/\/cdn.shopify.com\/s\/files\/1\/0580\/4359\/6979\/products\/blogger-boz-579bedef5f9b589aa9915064.jpg?v=1629612431\", \"width\": 3722, \"height\": 2965, \"position\": 1, \"created_at\": \"2021-08-22T11:37:11+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2021-08-22T11:37:11+05:30\", \"variant_ids\": [], \"admin_graphql_api_id\": \"gid:\/\/shopify\/ProductImage\/29593615106227\"}], \"status\": \"active\", \"vendor\": \"Emailwish tester store 1\", \"options\": [{\"id\": 8795617132723, \"name\": \"Title\", \"values\": [\"Default Title\"], \"position\": 1, \"product_id\": 6861983219891}], \"variants\": [{\"id\": 40414839505075, \"sku\": \"10\", \"grams\": 0, \"price\": \"10.00\", \"title\": \"Default Title\", \"weight\": 0, \"barcode\": \"\", \"option1\": \"Default Title\", \"option2\": null, \"option3\": null, \"taxable\": false, \"image_id\": null, \"position\": 1, \"created_at\": \"2021-08-22T11:37:09+05:30\", \"product_id\": 6861983219891, \"updated_at\": \"2022-02-04T11:23:51+05:30\", \"weight_unit\": \"kg\", \"compare_at_price\": null, \"inventory_policy\": \"continue\", \"inventory_item_id\": 42509750698163, \"requires_shipping\": true, \"inventory_quantity\": 16, \"fulfillment_service\": \"manual\", \"admin_graphql_api_id\": \"gid:\/\/shopify\/ProductVariant\/40414839505075\", \"inventory_management\": \"shopify\", \"old_inventory_quantity\": 16}], \"body_html\": \"lorem ipsum\", \"created_at\": \"2021-08-22T11:37:09+05:30\", \"updated_at\": \"2022-02-04T11:23:51+05:30\", \"product_type\": \"\", \"published_at\": \"2021-08-22T11:37:10+05:30\", \"published_scope\": \"web\", \"template_suffix\": \"\", \"admin_graphql_api_id\": \"gid:\/\/shopify\/Product\/6861983219891\"}"
                ]
            ],
            "more" => true
        ]);
    }

    function images(): JsonResponse
    {
        $items = GalleryImage::query()->paginate();
        return response()->json($items);
    }

    function query(Request $request): JsonResponse
    {
        $shop = ShopifyShop::query()->first();

        $request->validate([
            'target' => 'required|string|in:products,collections',
            'query' => 'required|string',
        ]);

        $target = $request->input('target');
        $query = $request->input('query');

        $graphql_query = "{}";
        switch ($target) {
            case "products":
                $graphql_query = "{ products(query: \"$query\", first: 10) { edges { node { handle onlineStoreUrl onlineStorePreviewUrl title }}}}";
                break;
            case "collections":
                $graphql_query = "{ collections(query: \"$query\", first: 10) { edges { node { handle title }}}}";
                break;
        }

        $helper = new ShopifyHelper($shop);
        return response()->json($helper->graphQLQuery($graphql_query));
    }

    function blogs(Request $request): JsonResponse
    {
        /** @var ShopifyShop $shop */
        $shop = ShopifyShop::query()->first();

        $request->validate([
            'query' => 'required|string',
        ]);

        $blogs = $shop->shopify_blogs()->search($shop)->paginate();
        return response()->json(['items' => $blogs]);
    }

    function pages(Request $request): JsonResponse
    {
        /** @var ShopifyShop $shop */
        $shop = ShopifyShop::query()->first();

        $request->validate([
            'query' => 'required|string',
        ]);

        $pages = $shop->shopify_pages()->search($shop)->paginate();
        return response()->json(['items' => $pages]);
    }

    function send_email(Request $request)
    {
        $request->validate([
            'content' => 'required|string',
            'recipient' => 'required|string|email|max:100',
        ]);

        return response()->json(['status' => 'ok']);
    }
}