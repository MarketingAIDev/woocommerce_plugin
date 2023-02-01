<?php

use Acelle\Model\ShopifyReview;
use Acelle\Model\ShopifyReviewImage;
use Acelle\Model\ShopifyShop;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        /** @var ShopifyShop $from_shop */
        $from_shop = ShopifyShop::where(ShopifyShop::COLUMN_myshopify_domain, 'ankit-dev.myshopify.com')->first();
        /** @var ShopifyShop $to_shop */
        $to_shop = ShopifyShop::where(ShopifyShop::COLUMN_myshopify_domain, 'midyoko.myshopify.com')->first();
        if(!$from_shop || !$to_shop) return;

        $from_customer = $from_shop->customer;
        $to_customer = $to_shop->customer;
        if(!$from_customer || !$to_customer) return;

        foreach ($from_customer->shopifyReviews as $review) {
            $copy = new ShopifyReview();
            $copy->stars = $review->stars;
            $copy->shopify_product_id = $review->shopify_product_id;
            $copy->reviewer_email = $review->reviewer_email;
            $copy->reviewer_name = $review->reviewer_name;
            $copy->title = $review->title;
            $copy->message = $review->message;
            $copy->verified_purchase = $review->verified_purchase;
            $copy->approved = $review->approved;
            $copy->ip_address = $review->ip_address;

            $copy->shop_id = $to_shop->id;
            $copy->shop_name = $to_shop->name;
            $copy->customer_id = $to_customer->id;
            $copy->save();

            foreach ($review->images as $image) {
                $image_copy = new ShopifyReviewImage();
                $image_copy->review_id = $copy->id;
                $image_copy->image_path = $image->image_path;
                $image_copy->save();
            }
        }
    }
}
