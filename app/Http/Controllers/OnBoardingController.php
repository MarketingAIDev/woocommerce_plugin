<?php

namespace Acelle\Http\Controllers;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Model\Customer;
use Acelle\Model\MailList;
use Acelle\Model\Plan;
use Acelle\Model\SendingDomain;
use Acelle\Model\ShopifyRecurringApplicationCharge;
use Acelle\Model\ShopifyShop;
use Acelle\Model\Signature;
use Acelle\Model\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class OnBoardingController extends Controller
{
    public function register_form(Request $request)
    {
        $data = $request->validate([
            'myshopify_domain' => 'required|string'
        ]);
        $myshopify_domain = $data['myshopify_domain'] ?? '';

        $shop = ShopifyShop::findByMyShopifyDomain($myshopify_domain);
        if ($shop != null) {
            /** @var User $user */
            $user = $request->user();
            if ($user == null) {
                if (empty($shop->user->password)) {
                    return response()->json([
                        "message" => "Please reset the password and login to continue onboarding",
                        "reactURL" => "/forgot-password?email=" . $shop->user->email
                    ]);
                }
                return response()->json([
                    "message" => "Please login to continue",
                    "reactURL" => "/login"
                ]);
            }
            if ($user->id != $shop->user->id) {
                return response()->json([
                    "message" => "This shop is already registered with another Emailwish account. Please relogin with that account.",
                    "reactURL" => "/"
                ]);
            }
            return response()->json([
                'shop' => $shop
            ]);
        }

        $nonce = uniqid();
        session([
            'onboarding_nonce' => $nonce,
        ]);

        $redirectURL = react_route('/register/complete');

        $params = [
            'client_id' => (new ShopifyHelper(null))->client_id,
            'redirect_uri' => $redirectURL,
            'scope' => ShopifyShop::getScopes(),
            'state' => $nonce
        ];
        return response()->json([
            "redirectURL" => "https://$myshopify_domain/admin/oauth/authorize?" . http_build_query($params)
        ]);
    }

    public function re_authorize(Request $request)
    {
        $data = $request->validate([
            'myshopify_domain' => 'required|string'
        ]);
        $myshopify_domain = $data['myshopify_domain'] ?? '';

        /** @var User $user */
        $user = $request->user();
        $shop = ShopifyShop::findByMyShopifyDomain($myshopify_domain);
        if (!$shop || $shop->user_id != $user->id)
            return response()->json([
                'message' => "Invalid shop!"
            ]);

        $nonce = uniqid();
        session([
            'onboarding_nonce' => $nonce,
        ]);

        $redirectURL = react_route('/register/complete');

        $params = [
            'client_id' => (new ShopifyHelper(null))->client_id,
            'redirect_uri' => $redirectURL,
            'scope' => ShopifyShop::getScopes(),
            'state' => $nonce
        ];
        return response()->json([
            "redirectURL" => "https://$myshopify_domain/admin/oauth/authorize?" . http_build_query($params)
        ]);
    }

    public function step_one(Request $request)
    {
        $nonce = $request->get('state');
        $temporary_code = $request->get('code');
        $hmac = $request->get('hmac');
        $myshopify_domain = $request->get('shop');
        $timestamp = $request->get('timestamp');

        $existing_shop = ShopifyShop::findByMyShopifyDomain($myshopify_domain);
        if ($existing_shop != null) {
            /** @var User $user */
            $user = $request->user();
            $password_required = false;
            if (empty($user->password))
                $password_required = true;
            if ($user == null || $existing_shop->customer->user_id != $user->id)
                return response()->json([
                        'message' => 'The shop is already registered with emailwish']
                    , 401);

            $h = new ShopifyHelper(null);
            $access_token = $h->getPermanentAccessToken($myshopify_domain, $temporary_code);
            if ($access_token === false) {
                $existing_shop->delete();
                throw new AuthorizationException('Failed to fetch permanent access token, please install the app again!');
            }
            $shop_object = $h->getShopObject($myshopify_domain, $access_token);
            $existing_shop->access_token = $access_token;
            $existing_shop->initialized = 0;
            $existing_shop->initializing = 0;
            $existing_shop->token_refresh_required = 0;
            $existing_shop->save();

            // Shop already exists and belongs to the logged-in user
            if ($existing_shop->onboarding_complete)
                return response()->json([
                    "reactURL" => "/"
                ]);

            return response()->json([
                'store' => [
                    'email' => $user->email,
                    'shop_owner_email' => $shop_object->email,
                    'password_required' => $password_required,
                    'contact_email' => $user->email,
                    'owner_first_name' => $user->first_name,
                    'owner_last_name' => $user->last_name,
                    'myshopify_domain' => $existing_shop->myshopify_domain,
                    'primary_domain' => $existing_shop->primary_domain,
                    'name' => $existing_shop->name,
                    'font_family' => $existing_shop->theme['font_family'] ?? '',
                    'primary_background_color' => $existing_shop->theme['primary_background_color'] ?? '#000000ff',
                    'primary_text_color' => $existing_shop->theme['primary_text_color'] ?? '#ffffffff',
                    'secondary_background_color' => $existing_shop->theme['secondary_background_color'] ?? '#ffffffff',
                    'secondary_text_color' => $existing_shop->theme['secondary_text_color'] ?? '#000000ff',
                ],
                'plans' => Plan::getAllActive()->orderBy('custom_order', 'asc')->get(),
                'currencies' => (new ShopifyHelper(null))->getCurrencies($existing_shop->myshopify_domain, $existing_shop->access_token)
            ]);
        }

        if ($nonce != session('onboarding_nonce')) {
            return response()->json([
                "reactURL" => "/register?shop=" . $myshopify_domain
            ]);
        }
        if (!$hmac || !$timestamp) {
            throw new AccessDeniedHttpException("Failed to verify parameters from Shopify. Please authenticate again.");
        }

        $h = new ShopifyHelper(null);
        $access_token = $h->getPermanentAccessToken($myshopify_domain, $temporary_code);
        if ($access_token === false) {
            throw new AuthorizationException('Failed to fetch permanent access token, please install the app again!');
        }
        $shop_object = $h->getShopObject($myshopify_domain, $access_token);

        session([
            'onboarding_nonce' => null,
        ]);

        $email = $shop_object->email;
        $shop_owner = $shop_object->shop_owner;
        $timezone = $shop_object->iana_timezone;


        /** @var User $user */
        $user = $request->user();
        $password_required = false;

        if ($user == null) {
            if (User::where('email', $email)->first()) {
                throw new AuthorizationException('Email address associated with this store is already registered with us. Please login first.');
            }

            $password_required = true;
            /** @var User $user */
            $user = new User();
            $user->first_name = $shop_owner;
            $user->last_name = "";
            $user->timezone = $timezone;
            $user->email = $email;
            $user->password = "";
            $user->activated = true;

            $af_id = $request->session()->get('af_id');
            if (empty($user->affiliation_id) && $af_id)
                $user->affiliation_id = $af_id;
            $user->save();
            $user->createAdminSubscribers();
            $user->sendWelcomeMail();
        } else {
            $user->triggerAdditionalShopAddedAutomation();
        }

        /** @var Customer $customer */
        $customer = $user->customers()->create([
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'timezone' => $user->timezone,
            'chat_settings' => "[]",
            'review_settings' => Customer::DEFAULT_REVIEW_SETTINGS,
        ]);

        $shopifyShop = new ShopifyShop();
        $shopifyShop->name = $shop_object->name;
        $shopifyShop->myshopify_domain = $myshopify_domain;
        $shopifyShop->primary_domain = $shop_object->domain;
        $shopifyShop->access_token = $access_token;
        $shopifyShop->scope = "";
        $shopifyShop->active = 1;
        $shopifyShop->nonce = "";
        $shopifyShop->initializing = 0;
        $shopifyShop->initialized = 0;
        $shopifyShop->new = 1;

        /** @var ShopifyShop $shopifyShop */
        $shopifyShop = $customer->shopify_shop()->save($shopifyShop);
        $shopifyShop->user()->associate($user);
        $shopifyShop->customer()->associate($customer);
        $shopifyShop->save();
        $customer->createDefaultContact();
        $customer->setChatSettings();

        $list = new MailList();
        $list->all_sending_servers = true;
        $list->name = 'Shopify Customers: ' . $shopifyShop->name;
        $list->from_email = $email;
        $list->from_name = $shop_object->shop_owner;
        $list->email_subscribe = $email;
        $list->email_unsubscribe = $email;
        $list->email_daily = $email;
        $list->customer_id = $customer->id;
        $list->contact_id = $customer->contact_id;
        $shopifyShop->mail_list()->save($list);

        $helper = new ShopifyHelper($shopifyShop);
        $helper->setThemeData();

        Auth::login($user);

        return response()->json([
            'store' => [
                'email' => $email,
                'shop_owner_email' => $shop_object->email,
                'password_required' => $password_required,
                'contact_email' => $email,
                'owner_first_name' => $shop_owner,
                'owner_last_name' => "",
                'myshopify_domain' => $myshopify_domain,
                'primary_domain' => $shop_object->domain,
                'name' => $shop_object->name,
                'font_family' => $shopifyShop->theme['font_family'] ?? '',
                'primary_background_color' => $shopifyShop->theme['primary_background_color'] ?? '',
                'primary_text_color' => $shopifyShop->theme['primary_text_color'] ?? '',
                'secondary_background_color' => $shopifyShop->theme['secondary_background_color'] ?? '',
                'secondary_text_color' => $shopifyShop->theme['secondary_text_color'] ?? '',
            ],
            'plans' => Plan::getAllActive()->orderBy('custom_order', 'asc')->get(),
            'currencies' => (new ShopifyHelper(null))->getCurrencies($myshopify_domain, $access_token)
        ]);
    }

    public function select_plan(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user)
            throw new AuthorizationException();

        $shopify_shop = ShopifyShop::findByMyShopifyDomain($request->post('myshopify_domain'));
        if (!$shopify_shop || !$shopify_shop->customer || $shopify_shop->customer->user_id != $user->id)
            throw new AuthorizationException();


        $data = $request->validate([
            'name' => 'required|string|max:255',
            'font_family' => 'nullable|string|max:25',
            'primary_background_color' => 'required|regex:/^#[0-9a-fA-F]{3,8}$/',
            'primary_text_color' => 'required|regex:/^#[0-9a-fA-F]{3,8}$/',
            'secondary_background_color' => 'required|regex:/^#[0-9a-fA-F]{3,8}$/',
            'secondary_text_color' => 'required|regex:/^#[0-9a-fA-F]{3,8}$/',
            'password' => 'nullable|string|min:8',
            'currency' => 'required|string|max:3',
            'owner_first_name' => 'required|string|max:50',
            'owner_last_name' => 'required|string|max:50',
            'phone_number' => 'nullable|string|max:15',
            'contact_email' => 'nullable|email|max:30',
            'facebook' => 'nullable|string|max:60',
            'instagram' => 'nullable|string|max:60',
            'linkedin' => 'nullable|string|max:60',
            'skype' => 'nullable|string|max:60',
            'twitter' => 'nullable|string|max:60',
            'selected_plan_id' => 'required|integer|exists:plans,id'
        ]);

        /** @var Plan $plan */
        $plan = Plan::find($data['selected_plan_id']);
        if ($plan->status != Plan::STATUS_ACTIVE) {
            throw new AuthorizationException('Invalid Plan');
        }

        $user->first_name = $data['owner_first_name'];
        $user->last_name = $data['owner_last_name'];
        if (!empty($data['password']))
            $user->password = Hash::make($data['password']);
        $user->save();

        $shopify_shop->name = $data['name'];
        $shopify_shop->primary_currency = $data['currency'];
        $shopify_shop->theme = [
            'font_family' => $data['font_family'],
            'primary_background_color' => $data['primary_background_color'] ?? "",
            'primary_text_color' => $data['primary_text_color'] ?? "",
            'secondary_background_color' => $data['secondary_background_color'] ?? "",
            'secondary_text_color' => $data['secondary_text_color'] ?? "",
        ];
        $shopify_shop->save();

        /*
        $shopify_shop->customer->updateChatColors($data);
        $shopify_shop->customer->updateReviewColors($data);
        */

        // Create new Sending Domain
        $sendingDomain = new SendingDomain();
        $sendingDomain->name = $shopify_shop->customer->shopify_shop->primary_domain;
        $sendingDomain->customer_id = $shopify_shop->customer->id;
        $sendingDomain->status = 'active';
        $sendingDomain->save();

        Signature::validateAndSave($shopify_shop->customer, $request->all());

        $helper = new ShopifyHelper($shopify_shop);
        $returnURL = react_route('/register/complete');
        $charge = $helper->createRecurringApplicationCharge($plan, $returnURL);

        return response()->json([
            "redirectURL" => $charge->getShopifyModel()->confirmation_url
        ]);
    }

    public function change_plan(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user)
            throw new AuthorizationException();


        $data = $request->validate([
            'myshopify_domain' => 'required|string',
            'selected_plan_id' => 'required|integer|exists:plans,id'
        ]);

        $shopify_shop = ShopifyShop::findByMyShopifyDomain($request->post('myshopify_domain'));
        if ($shopify_shop->customer->user_id != $user->id)
            throw new AuthorizationException();


        /** @var Plan $plan */
        $plan = Plan::find($data['selected_plan_id']);
        if ($plan->status != Plan::STATUS_ACTIVE) {
            throw new AuthorizationException('Invalid Plan');
        }

        $helper = new ShopifyHelper($shopify_shop);
        $returnURL = react_route('/register/complete');
        $charge = $helper->createRecurringApplicationCharge($plan, $returnURL);

        return response()->json([
            "redirectURL" => $charge->getShopifyModel()->confirmation_url
        ]);
    }

    public function complete_plan(Request $request)
    {
        /** @var User $user */
        $user = $request->user();
        if (!$user || !count($user->customers)) {
            throw new AuthorizationException();
        }

        $data = $request->validate([
            'shop' => 'required|string',
            'charge_id' => 'required|string'
        ]);

        $shop = ShopifyShop::findByMyShopifyDomainOrFail($data['shop']);
        Gate::authorize('update', $shop);

        $charge = ShopifyRecurringApplicationCharge::findByShopifyIdOrFail($data['charge_id']);
        $helper = new ShopifyHelper($shop);
        $charge = $helper->activateRecurringApplicationCharge($charge);

        if (!$charge) {
            throw new AuthorizationException("Failed to activate plan");
        }
        $shop->onboarding_complete = true;
        $shop->save();

        $helper->createDefaultAutomationsAndPopups();

        return response("success");
    }

    function abort_onboarding(Request $request)
    {
        $data = $request->validate([
            'shop' => 'required|string',
        ]);

        $shop = ShopifyShop::findByMyShopifyDomainOrFail($data['shop']);
        Gate::authorize('update', $shop);

        $shop->delete();

        return response("success");
    }
}
