<?php

namespace Acelle\Library\Automation\DynamicWidgetConfig;

class OrderConfig
{
    public $images;
    public $text_background_color;
    public $text_color;
    public $button_text;
    public $button_color;
    public $button_border_color;
    public $button_text_color;

    function __construct($query_string)
    {
        parse_str($query_string, $query);
        $this->images = strtolower($query['images'] ?? "") == "y";
        $this->text_background_color = $query['text_background_color'] ?? "#ffffff";
        $this->text_color = $query['text_color'] ?? "#ffffff";
        $this->button_text = !empty($query['button_text']) ? $query['button_text'] : "Buy Now";
        $this->button_color = $query['button_color'] ?? "#ffffff";
        $this->button_border_color = $query['button_border_color'] ?? "#00000";
        $this->button_text_color = $query['button_text_color'] ?? "#00000";
    }
}