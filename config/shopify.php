<?php

return [
    'api_version' => env("SHOPIFY_API_VERSION", "2021-04"),
    'client_id' => env("SHOPIFY_CLIENT_ID", ""),
    'client_secret' => env("SHOPIFY_CLIENT_SECRET", ""),
    'read_all_orders' => (bool) (env("SHOPIFY_READ_ALL_ORDERS", false)),
    'live_payments' => (bool) (env("SHOPIFY_LIVE_PAYMENTS", false)),
];
