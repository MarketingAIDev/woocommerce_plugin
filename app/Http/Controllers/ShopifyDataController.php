<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\ShopifyProduct;
use Illuminate\Http\Request;

class ShopifyDataController extends Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->middleware('selected_customer');
    }

    public function products(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;

        return response()->json([
            'items' => $shop->shopify_products()->paginate($request->per_page ?? 20),
        ]);
    }

    public function products_select(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;
        /** @var ShopifyProduct[] $products */
        $products = $shop->shopify_products()
            ->where(ShopifyProduct::COLUMN_shopify_title, 'LIKE', "%{$request->keyword}%")
            ->limit(20)->get();

        $search_results = [];
        foreach ($products as $product)
        {
            $search_results[] = [
                "value" => $product->shopify_id,
                "label" => $product->shopify_title
            ];
        }

        return response()->json([
            'products' => $search_results
        ]);
    }
}
