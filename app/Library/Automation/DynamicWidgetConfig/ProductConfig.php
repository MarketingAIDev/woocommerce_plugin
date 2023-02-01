<?php

namespace Acelle\Library\Automation\DynamicWidgetConfig;

use Acelle\Model\ShopifyProduct;

class ProductConfig
{
    /** @var ShopifyProduct|null */
    public $product;

    public $source;
    public $product_id;

    public $description;
    public $text_background_color;
    public $text_color;
    public $button_text;
    public $button_color;
    public $button_border_color;
    public $button_text_color;

    function __construct($product, $query_string)
    {
        $this->product = $product;

        parse_str($query_string, $query);
        $this->source = $query['source'] ?? "";
        $this->product_id = $query['product_id'] ?? "";
        $this->description = strtolower($query['description'] ?? "") == "y";
        $this->text_background_color = $query['text_background_color'] ?? "#ffffff";
        $this->text_color = $query['text_color'] ?? "#ffffff";
        $this->button_text = !empty($query['button_text']) ? $query['button_text'] : "Buy Now";
        $this->button_color = $query['button_color'] ?? "#ffffff";
        $this->button_border_color = $query['button_border_color'] ?? "#00000";
        $this->button_text_color = $query['button_text_color'] ?? "#00000";

        if ($this->source == "product_id" && !empty($this->product_id)) {
            $this->product = ShopifyProduct::find($this->product_id);
        }
    }
}