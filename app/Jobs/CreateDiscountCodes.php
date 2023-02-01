<?php

namespace Acelle\Jobs;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Model\ShopifyShop;

class CreateDiscountCodes extends SystemJob
{
    /** @var ShopifyShop */
    protected $shop;
    /** @var array */
    protected $discounts;

    public function __construct(ShopifyShop $shop, $discounts)
    {
        parent::__construct();
        $this->shop = $shop;
        $this->discounts = $discounts;
    }


    public function handle()
    {
        $helper = new ShopifyHelper($this->shop);
        foreach ($this->discounts as $discount) {
            $helper->createDiscountCode($discount['code'], $discount['discount']);
        }
    }
}
