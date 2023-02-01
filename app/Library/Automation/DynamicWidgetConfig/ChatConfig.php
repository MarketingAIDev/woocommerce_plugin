<?php

namespace Acelle\Library\Automation\DynamicWidgetConfig;

class ChatConfig
{
    public $text_background_color;
    public $text_color;

    function __construct($query_string)
    {
        parse_str($query_string, $query);
        $this->text_background_color = $query['text_background_color'] ?? "#ffffff";
        $this->text_color = $query['text_color'] ?? "#ffffff";
    }
}