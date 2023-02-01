<?php

// check if a function is disable
function f_enabled($name) {
  $disabled = preg_split('/[\s,]+/', ini_get('disable_functions'));
  return function_exists($name) && !in_array($name, $disabled);
}

// fucntions that are required by Acelle Mail
$required_functions = ['proc_close', 'proc_open', 'escapeshellarg', ];

// Check required functions
foreach ($required_functions as $f) {
    if (!f_enabled($f)) {
        echo "ERROR: The <strong>{$f}</strong> function is disabled on your hosting server. Please enable it to proceed. Contact your hosting provider if you are not sure how to do it.<br />";
        exit(0);
    }
}

// Check PHP version
if (!version_compare(PHP_VERSION, '7.1.3', '>=')) {
    echo "ERROR: Your current PHP version is ".PHP_VERSION.". However, Acelle requires PHP version 7.1.3 or higher.<br />";
    exit(0);
}

// Check required extensions
if (!extension_loaded('mbstring')) {
    echo "ERROR: The requested PHP Mbstring extension is missing from your system.<br />";
    exit(0);
}

// Check if open_basedir is in effect
//if (!empty(ini_get('open_basedir'))) {
//    echo "ERROR: Please disable the <strong>open_basedir</strong> setting to continue.<br />";
//    exit(0);
//}

// Check directory permission
if (!(file_exists('../storage/app') && is_dir('../storage/app') && (is_writable('../storage/app')))) {
    echo "ERROR: The directory [/storage/app] must be writable by the web server.<br />";
    exit(0);
}

if (!(file_exists('../storage/framework') && is_dir('../storage/framework') && (is_writable('../storage/framework')))) {
    echo "ERROR: The directory [/storage/framework] must be writable by the web server.<br />";
    exit(0);
}

if (!(file_exists('../storage/logs') && is_dir('../storage/logs') && (is_writable('../storage/logs')))) {
    echo "ERROR: The directory [/storage/logs] must be writable by the web server.<br />";
    exit(0);
}

if (!(file_exists('../bootstrap/cache') && is_dir('../bootstrap/cache') && (is_writable('../bootstrap/cache')))) {
    echo "ERROR: The directory [/bootstrap/cache] must be writable by the web server.<br />";
    exit(0);
}

if (!empty($_SERVER['HTTP_ORIGIN'])) {
    // Allow CORS from trusted origins and to trusted paths
    $origin = $_SERVER['HTTP_ORIGIN'];
    $allowed_origins = [
        'https://dashboard.emailwish.com',
        'https://beta.emailwish.com',
        'https://apis.emailwish.com',
        'http://localhost:3000',
        'http://localhost:3001',
        'http://localhost:3002',
    ];

    $uri_allowed = true; // false;
    /*$uri = $_SERVER['REQUEST_URI'];
    $allowed_uris = [
        '/_shopify'
    ];

    foreach ($allowed_uris as $a) {
        if (strpos($uri, $a) !== false) {
            $uri_allowed = true;
            break;
        }
    }*/

    if ($uri_allowed || in_array($origin, $allowed_origins)) {
        header('Access-Control-Max-Age: 1000');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Origin: ' . $origin);
        header('Access-Control-Allow-Methods: PUT, GET, POST, PATCH, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Authorization, Origin, Accept, Content-Type, X-Requested-With, X-Auth-Token, X-XSRF-Token, X-Emailwish-Customer-UID');
    }
}

// Store the referrer from outside the app to redirect back.
// This is required for the core may be accessed from outside, for example, by the React App.
// The React app may take the user to a core URL, and the user may navigate within core,
// but when the user clicks "Go back" or similar, we need the original React referrer address to take the user back.
if (!empty($_SERVER['HTTP_REFERER'])) {
    $referrer_url = parse_url($_SERVER['HTTP_REFERER']);
    if (!empty($referrer_url) && !empty($referrer_url['host']) && $referrer_url['host'] != $_SERVER['HTTP_HOST']) {
        $_SESSION['OUTSIDE_REFERRER'] = $_SERVER['HTTP_REFERER'];
    }
}

require 'main.php';
