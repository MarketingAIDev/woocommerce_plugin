<?php

namespace Acelle\Library\Automation\DynamicWidgetConfig;

use Acelle\Model\ShopifyProduct;

class Products3Config
{
    /** @var ShopifyProduct|null */
    public $product_1;
    /** @var ShopifyProduct|null */
    public $product_2;
    /** @var ShopifyProduct|null */
    public $product_3;

    public $source_1;
    public $source_2;
    public $source_3;
    public $product_id_1;
    public $product_id_2;
    public $product_id_3;

    public $description;
    public $text_background_color;
    public $text_color;
    public $button_text;
    public $button_color;
    public $button_border_color;
    public $button_text_color;

    function __construct($query_string)
    {
        parse_str($query_string, $query);
        $this->source_1 = $query['source_1'] ?? "";
        $this->source_2 = $query['source_2'] ?? "";
        $this->source_3 = $query['source_3'] ?? "";
        $this->product_id_1 = $query['product_id_1'] ?? "";
        $this->product_id_2 = $query['product_id_2'] ?? "";
        $this->product_id_3 = $query['product_id_3'] ?? "";

        $this->description = strtolower($query['description'] ?? "") == "y";
        $this->text_background_color = $query['text_background_color'] ?? "#ffffff";
        $this->text_color = $query['text_color'] ?? "#ffffff";
        $this->button_text = !empty($query['button_text']) ? $query['button_text'] : "Buy Now";
        $this->button_color = $query['button_color'] ?? "#ffffff";
        $this->button_border_color = $query['button_border_color'] ?? "#00000";
        $this->button_text_color = $query['button_text_color'] ?? "#00000";

        if ($this->source_1 == "product_id" && !empty($this->product_id_1)) {
            $this->product_1 = ShopifyProduct::find($this->product_id_1);
        }
        if ($this->source_2 == "product_id" && !empty($this->product_id_2)) {
            $this->product_2 = ShopifyProduct::find($this->product_id_2);
        }
        if ($this->source_3 == "product_id" && !empty($this->product_id_3)) {
            $this->product_3 = ShopifyProduct::find($this->product_id_3);
        }
    }
}