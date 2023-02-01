<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyReview;
use Acelle\Model\ShopifyShop;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ShopifyReviewGuestController extends Controller
{
    function embed(Request $request)
    {
        if (empty($request->shop_name) || empty($request->product_id))
            return "Invalid request. shop_name and product_id are required.";

        /** @var ShopifyShop $shop */
        $shop = ShopifyShop::findByMyShopifyDomain($request->shop_name);
        if (!$shop)
            return "Shop not found";

        if (!$shop->enable_review_script)
            return response()->json(["error" => "Review not enabled"], 401);

        $stars = (int)$request->filter_stars ?? 0;
        if ($stars > 5) $stars = 5;
        if ($stars < 0) $stars = 0;

        //$request->merge(array("approved" => '1'));
        //$items = ShopifyReview::search($request)->with('images')->paginate($request->per_page ?? 20);
        $items = $reviews = ShopifyReview::where(ShopifyReview::COLUMN_shop_id, $shop->id)
            ->where(ShopifyReview::COLUMN_shopify_product_id, $request->product_id)
            ->with('images');

        if ($stars > 0)
            $items->where(ShopifyReview::COLUMN_stars, $stars);

        $settings = $shop->customer->getReviewSettings();
        return response()->json([
            "review_settings" => $settings,
            'items' => $items->paginate($request->per_page ?? 20),
            'theme' => $shop->theme,
            'shop_name' => $request->shop_name,
            'product_id' => $request->product_id
        ]);
    }

    function embedAll(Request $request)
    {
        if (empty($request->client_uid))
            return "Invalid request. client_uid is required.";

        $customer = Customer::findByUid($request->client_uid);
        if (!$customer)
            return "Invalid client UID";

        $shop = $customer->shopify_shop;
        if (!$shop)
            return "Shop not found";

        if (!$shop->enable_review_script)
            return response()->json(["error" => "Review not enabled"], 401);

        $stars = (int)$request->filter_stars ?? 0;
        if ($stars > 5) $stars = 5;
        if ($stars < 0) $stars = 0;

        //$request->merge(array("approved" => '1'));
        //$items = ShopifyReview::search($request)->with('images')->paginate($request->per_page ?? 20);
        $items = $reviews = ShopifyReview::where(ShopifyReview::COLUMN_shop_id, $shop->id)
            ->with('images');

        if ($stars > 0)
            $items->where(ShopifyReview::COLUMN_stars, $stars);

        $settings = $shop->customer->getReviewSettings();
        return response()->json([
            "review_settings" => $settings,
            'items' => $items->paginate($request->per_page ?? 20),
            'theme' => $shop->theme,
            'shop_name' => $request->shop_name
        ]);
    }

    function store(Request $request)
    {
        $shop = ShopifyShop::findByMyShopifyDomain($request['shop_name'] ?? "");
        if (!$shop) {
            throw ValidationException::withMessages([
                'shop_name' => "Shop doesn't exist"
            ]);
        }

        if (!$shop->enable_review_script)
            return response()->json(["error" => "Review not enabled"], 401);

        ShopifyReview::validateAndCreateReviewForGuest($shop, $request->all());

        return "Your review has been added. It will be published after review.";
    }

    function storeSilently(Request $request)
    {
        $shop = ShopifyShop::findByMyShopifyDomain($request['shop_name'] ?? "");
        if (!$shop) {
            throw ValidationException::withMessages([
                'shop_name' => "Shop doesn't exist"
            ]);
        }

        ShopifyReview::validateAndCreateReviewForGuest($shop, $request->all(), true);
        return "Review has been added.";
    }

    function updateReviewFetchStatus(Request $request)
    {
        $shop = ShopifyShop::findByMyShopifyDomain($request['shop_name'] ?? "");
        if (!$shop) {
            throw ValidationException::withMessages([
                'shop_name' => "Shop doesn't exist"
            ]);
        }
        $data = $request->validate([
            'shopify_product_id' => 'required|integer',
            'complete' => 'required|boolean'
        ]);
        $product = ShopifyProduct::findByShopifyId($data['shopify_product_id'] ?? 0);
        if (!$product || $product->shop_id != $shop->id)
            throw ValidationException::withMessages([
                'shopify_product_id' => "Product doesn't exist"
            ]);

        $product->review_fetch_complete = true;
        $product->save();
    }

    function reviewStats(Request $request)
    {
        if (empty($request->shop_name) || empty($request->product_id))
            return response()->json(["error" => "Invalid request. shop_name and product_id are required."], 419);

        /** @var ShopifyShop $shop */
        $shop = ShopifyShop::findByMyShopifyDomain($request->shop_name);
        if (!$shop)
            return response()->json(["error" => "Shop not found"], 419);

        if (!$shop->enable_review_script)
            return response()->json(["error" => "Review not enabled"], 401);

        $reviews = ShopifyReview::where(ShopifyReview::COLUMN_shop_id, $shop->id)
            ->where(ShopifyReview::COLUMN_shopify_product_id, $request->product_id)
            ->get();

        $count_total = 0;
        $stars_total = 0;
        $counts = [0, 0, 0, 0, 0, 0];

        foreach ($reviews as $review) {
            if ($review->stars <= 5 && $review->stars >= 0) {
                $counts[$review->stars]++;
                $count_total++;
                $stars_total += $review->stars;
            }
        }

        $settings = $shop->customer->getReviewSettings();
        return response()->json([
            "review_settings" => $settings,
            "average_score" => $stars_total / max($count_total, 1),
            "total_reviews" => $count_total,
            "stars_1" => $counts[1],
            "stars_2" => $counts[2],
            "stars_3" => $counts[3],
            "stars_4" => $counts[4],
            "stars_5" => $counts[5],
        ]);
    }

    function view(Request $request)
    {
        $review = ShopifyReview::findByUidAndKey($request->uid, $request->secret_key);

        return response()->json(['review' => $review]);
    }

    function updateFromReviewer(Request $request)
    {
        $review = ShopifyReview::findByUidAndKey($request->uid, $request->secret_key);

        $review->validateAndUpdateForGuest($request->all());

        return "Your review has been updated";
    }
}
