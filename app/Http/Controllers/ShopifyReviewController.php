<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyReview;
use Acelle\Model\ShopifyShop;
use Illuminate\Http\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ShopifyReviewController extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $request->merge(array("customer_id" => $customer->id));
        $items = ShopifyReview::search($request);

        return response()->json([
            'view' => 'shopify_reviews.index',
            'items' => $items,
        ]);
    }

    /**
     * Display a listing of the resource.
     *
     * @param Request $request
     * @return mixed
     */
    public function listing(Request $request)
    {
        $request->merge(array("customer_id" => $request->selected_customer->id));
        $items = ShopifyReview::search($request)->paginate($request->per_page);

        return response()->json([
            'view' => 'shopify_reviews._list',
            'items' => $items,
        ]);
    }

    public function embed(Request $request)
    {
        if (empty($request->shop_name) || empty($request->product_id))
            return "";

        /** @var ShopifyShop $shop */
        $shop = ShopifyShop::findByMyShopifyDomain($request->shop_name);
        if (!$shop)
            return "";

        $request->merge(array("approved" => '1'));
        $items = ShopifyReview::search($request)->paginate($request->per_page);

        return view('shopify_reviews.embed', [
            'items' => $items,
            'theme' => $shop->theme,
            'shop_name' => $request->shop_name,
            'product_id' => $request->product_id
        ]);
    }

    public function store(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;
        $review = ShopifyReview::validateAndCreateReview($shop, $request->all());
        return response()->json([
            'redirectAction' => 'ShopifyReviewController@show',
            'uid' => $review->uid,
        ]);
    }

    public function show(Request $request, $uid)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $review = $customer->shopify_shop->shopify_reviews()->where('uid', $uid)->first();

        return response()->json([
            'review' => $review,
        ]);
    }

    public function update(Request $request, $uid)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        /** @var ShopifyReview $review */
        $review = $customer->shopify_shop->shopify_reviews()->where('uid', $uid)->first();

        if (!$review) {
            throw new NotFoundHttpException();
        }

        $review->validateAndUpdate($request->all());
        if ($request->wantsJson()) {
            return response()->json([
                'redirectAction' => 'ShopifyReviewController@show',
                'uid' => $review->uid
            ]);
        }
        return redirect()->action('ShopifyReviewController@show', $review->uid);
    }

    public function delete(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $items = $customer->shopify_shop->shopify_reviews()
            ->whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            $item->delete();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Deleted"
            ]);
        }
        return response('Deleted');
    }

    function approve(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $items = $customer->shopify_shop->shopify_reviews()
            ->whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            /** @var ShopifyReview $item */
            $item->approved = $request->action == "approve";
            $item->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Updated"
            ]);
        }
        return response('Updated');
    }

    function verify(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $items = $customer->shopify_shop->shopify_reviews()
            ->whereIn('uid', explode(',', $request->uids));

        foreach ($items->get() as $item) {
            /** @var ShopifyReview $item */
            $item->verified_purchase = $request->action == "verify";
            $item->save();
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => "Updated"
            ]);
        }
        return response('Updated');
    }

    function getProducts(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;
        $keyword = $request->keyword ?? "";

        return $shop
            ->shopify_products()
            ->where(ShopifyProduct::COLUMN_shopify_title, 'LIKE', "%$keyword%")
            ->paginate($request->per_page ?? 20);
    }

    function refetchReviews(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $shop = $customer->shopify_shop;

        $products = $shop->shopify_products;
        foreach ($products as $product) {
            $product->submitted_for_review = false;
            $product->review_fetch_complete = false;
            $product->save();
        }

        return response()->json(['message' => 'Started fetching reviews in the background. This may take a few minutes to complete.']);
    }
}
