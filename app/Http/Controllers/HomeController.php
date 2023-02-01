<?php

namespace Acelle\Http\Controllers;

use Acelle\Events\UserUpdated;
use Acelle\Helpers\ShopifyHelper;
use Acelle\Library\Tool;
use Acelle\Model\Automation2;
use Acelle\Model\Campaign;
use Acelle\Model\Customer;
use Acelle\Model\Popup;
use Acelle\Model\ShopifyReview;
use Acelle\Model\Subscriber;
use Acelle\Model\MailList;
use Acelle\Model\Segment2;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyShop;
use Acelle\Model\Email;
use Acelle\Model\SyncStatus;
use Acelle\Model\TrackingLog;
use Acelle\Model\User;
use Acelle\Model\ChatSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Stevebauman\Location\Facades\Location;

class HomeController extends Controller
{

    /** @var User */
    private $user;

    /** @var Customer */
    private $selected_customer;

    /** @var ShopifyShop */
    private $shop;

    public function __construct()
    {
        parent::__construct();
        $this->middleware('selected_customer');
        $this->middleware(function ($request, $next) {

            $this->user = request()->user();
            $this->selected_customer = request()->selected_customer;
            $this->shop = $this->selected_customer->shopify_shop;

            return $next($request);
        });
    }

    /**
     * Show the application dashboard.
     *
     * @param Request $request
     * @return mixed
     */
    public function index(Request $request)
    {
        event(new UserUpdated($this->selected_customer));
        if ($request->wantsJson()) {
            return response()->json([
                'view' => 'dashboard'
            ]);
        }
        return view('dashboard');
    }

    function global_search(Request $request): JsonResponse
    {
        $result = [];
        $query = $request->search ?? "";

        $result['automations'] = Automation2::where(Automation2::COLUMN_name, 'LIKE', "%$query%")
            ->where(Automation2::COLUMN_customer_id, $this->selected_customer->id)
            ->limit(5)
            ->get();
        $result['campaigns'] = Campaign::where(Campaign::COLUMN_name, 'LIKE', "%$query%")
            ->where(Campaign::COLUMN_customer_id, $this->selected_customer->id)
            ->limit(5)
            ->get();
        $result['mail_lists'] = MailList::where(MailList::COLUMN_name, 'LIKE', "%$query%")
            ->where(MailList::COLUMN_customer_id, $this->selected_customer->id)
            ->limit(5)
            ->get();
        $result['segments'] = Segment2::where(Segment2::COLUMN_name, 'LIKE', "%$query%")
            ->where(Segment2::COLUMN_customer_id, $this->selected_customer->id)
            ->limit(5)
            ->get();

        return response()->json($result);
    }

    public function analytics()
    {
        $total_sales = [];
        $ew_sales = [];
        $twelve_months_ago = Carbon::now()
            ->subMonths(12)
            ->setTime(0, 0)
            ->setTimezone('UTC')
            ->toDateTimeString();
        /** @var ShopifyOrder[] $orders */
        $orders = ShopifyOrder::where('customer_id', $this->selected_customer->id)
            ->where('date', '>=', $twelve_months_ago)
            ->get();

        foreach ($orders as $order) {
            $date_string = Carbon::parse($order->shopify_created_at)->setTimezone('UTC')->format('Y-m');
            $total_sales_amount = $total_sales[$date_string] ?? 0;
            $ew_sales_amount = $ew_sales[$date_string] ?? 0;

            $total_sales_amount += $order->shopify_total_price;
        }
    }

    private function credits_usage_data(): array
    {
        /** @var Customer $customer */
        $customer = request()->selected_customer;
        $data = [];

        $sending_limit_used = $customer->getSendingQuotaUsage();
        $sending_limit_quota = $customer->getSendingQuota();
        if ($sending_limit_quota == -1) {
            $sending_limit_quota = '∞';
            $sending_limit_progress = 0;
            $sending_limit_progress_text = 'Unlimited';
        } else if (!$sending_limit_quota) {
            $sending_limit_progress = 100;
            $sending_limit_progress_text = $sending_limit_progress . " %";
        } else {
            $sending_limit_progress = 100 * $sending_limit_used / $sending_limit_quota;
            $sending_limit_progress_text = $sending_limit_progress . " %";
        }

        $data['sending_limit'] = [
            'used' => $sending_limit_used,
            'quota' => $sending_limit_quota,
            'progress' => $sending_limit_progress,
            'progress_text' => $sending_limit_progress_text
        ];

        $lists_used = $customer->listsCount();
        $lists_quota = $customer->getOption('list_max');
        if ($lists_quota == -1) {
            $lists_quota = '∞';
            $lists_progress = 0;
            $lists_progress_text = 'Unlimited';
        } else {
            $lists_progress = 100 * $lists_used / $lists_quota;
            $lists_progress_text = $lists_progress . " %";
        }

        $data['lists'] = [
            'used' => $lists_used,
            'quota' => $lists_quota,
            'progress' => $lists_progress,
            'progress_text' => $lists_progress_text
        ];


        $campaigns_used = $customer->campaignsCount();
        $campaigns_quota = $customer->getOption('campaign_max');
        if ($campaigns_quota == -1) {
            $campaigns_quota = '∞';
            $campaigns_progress = 0;
            $campaigns_progress_text = 'Unlimited';
        } else {
            $campaigns_progress = 100 * $campaigns_used / $campaigns_quota;
            $campaigns_progress_text = $campaigns_progress . " %";
        }

        $data['campaigns'] = [
            'used' => $campaigns_used,
            'quota' => $campaigns_quota,
            'progress' => $campaigns_progress,
            'progress_text' => $campaigns_progress_text
        ];


        $subscribers_used = $customer->subscribersCount();
        $subscribers_quota = $customer->getOption('subscriber_max');
        if ($subscribers_quota == -1) {
            $subscribers_quota = '∞';
            $subscribers_progress = 0;
            $subscribers_progress_text = 'Unlimited';
        } else {
            $subscribers_progress = 100 * $subscribers_used / $subscribers_quota;
            $subscribers_progress_text = $subscribers_progress . " %";
        }

        $data['subscribers'] = [
            'used' => $subscribers_used,
            'quota' => $subscribers_quota,
            'progress' => $subscribers_progress,
            'progress_text' => $subscribers_progress_text
        ];

        return $data;
    }

    public function email_dashboard()
    {
        $opens_models = Campaign::topOpens(5, $this->selected_customer)->get();
        $opens_data = [];
        foreach ($opens_models as $item) {
            $opens_data[] = [
                "name" => $item->name,
                "recipients" => $item->displayRecipients(),
                "opens" => $item->aggregate,
                "opens_unique" => $item->readCache('UniqOpenCount'),
                "last_open" => (null !== $item->lastOpen()) ? Tool::formatDateTime($item->lastOpen()->created_at) : "",
            ];
        }

        $clicks_models = Campaign::topClicks(5, $this->selected_customer)->get();
        $clicks_data = [];
        foreach ($clicks_models as $item) {
            $clicks_data[] = [
                "name" => $item->name,
                "recipients" => $item->displayRecipients(),
                "clicks" => $item->aggregate,
                "urls" => $item->urlCount(),
                "last_click" => (null !== $item->lastClick()) ? Tool::formatDateTime($item->lastClick()->created_at) : "",
            ];
        }

        $links_models = Campaign::topLinks(5, $this->selected_customer)->get();
        $links_data = [];
        foreach ($links_models as $item) {
            $links_data[] = [
                "url" => $item->url,
                "campaigns" => $item->campaigns()->count(),
                "clicks" => $item->aggregate,
                "last_click" => (null !== $item->lastClick($this->selected_customer)) ? Tool::formatDateTime($item->lastClick($this->selected_customer)->created_at) : ""
            ];
        }

        $dashboard = [];
        $dashboard['top_opens'] = $opens_data;
        $dashboard['top_clicks'] = $clicks_data;
        $dashboard['top_links'] = $links_data;
        $dashboard['recent_campaigns'] = $this->selected_customer->sentCampaigns()->get();
        $dashboard['activities'] = $this->selected_customer->logs()->take(20)->get();
        $dashboard['credit_usage'] = $this->credits_usage_data();

        return response()->json($dashboard);
    }

    function sync_status()
    {
        return response()->json([
            'sync_status' => SyncStatus::getSyncStatus($this->shop)
        ]);
    }

    public function home_dashboard()
    {
        $dashboard = [];
        $dashboard['recent_reviews'] = $this->selected_customer->shopifyReviews()->orderBy('id', 'desc')->take(20)->get();
        $dashboard['recent_chats'] = $this->selected_customer->chat_sessions()->whereNotNull('last_message_at')->orderBy('last_message_at', 'desc')->take(20)->get();
        return response()->json($dashboard);
    }

    function reports(Request $request): JsonResponse
    {
        return response()->json([
            'shopify_orders' => ShopifyOrder::getDailyReport($request, $this->selected_customer),
            'emailwish_shopify_orders' => ShopifyOrder::getDailyReport($request, $this->selected_customer, true),
            'subscribers' => Subscriber::getDailyReport($request, $this->selected_customer),
            'reviews' => ShopifyReview::getDailyReport($request, $this->selected_customer),
            'emails' => TrackingLog::getEmailsReport($request, $this->selected_customer),
            'chats' => ChatSession::getChatReport($request, $this->selected_customer),
            'revenue_report' => ShopifyOrder::getRevenueReport($request, $this->selected_customer),
            'sales_breakdown' => ShopifyOrder::getSalesBreakdown($request, $this->selected_customer)
        ]);
    }
    function chat_summary(): JsonResponse
    {
        return response()->json([
            'chat_summary' => ChatSession::getSummary($this->selected_customer)
        ]);
    }
    function popup_summary(): JsonResponse
    {
        return response()->json([
            'popup_summary' => Popup::getSummary($this->selected_customer)
        ]);
    }

    public function shopify_subscription(): JsonResponse
    {
        return response()->json([
            'subscription' => $this->selected_customer->getActiveShopifyRecurringApplicationCharge()
        ]);
    }

    public function modules_status(): JsonResponse
    {
        return response()->json([
            'chat_module_enabled' => $this->shop->enable_chat_script,
            'review_module_enabled' => $this->shop->enable_review_script,
            'popup_module_enabled' => $this->shop->enable_popup_script,
        ]);
    }

    public function chat_module_status(Request $request): JsonResponse
    {
        if ($request->getMethod() == "POST") {
            $helper = new ShopifyHelper($this->shop);
            if (!$helper->isWidgetScriptInstalled())
                $helper->installWidgetScriptTag();
            $this->shop->enable_chat_script = $request->post('action') == 'install';
            $this->shop->save();
            return response()->json([
                'message' => "Chat module updated",
                'chat_module_enabled' => $this->shop->enable_chat_script,
            ]);
        }
        return response()->json([
            'chat_module_enabled' => $this->shop->enable_chat_script,
        ]);
    }

    public function review_module_status(Request $request): JsonResponse
    {
        if ($request->getMethod() == "POST") {
            $helper = new ShopifyHelper($this->shop);
            if (!$helper->isWidgetScriptInstalled())
                $helper->installWidgetScriptTag();
            $this->shop->enable_review_script = $request->post('action') == 'install';
            $this->shop->save();
            return response()->json([
                'message' => "Review module updated",
                'review_module_enabled' => $this->shop->enable_review_script,
            ]);
        }
        return response()->json([
            'review_module_enabled' => $this->shop->enable_review_script,
        ]);
    }

    public function popup_module_status(Request $request): JsonResponse
    {
        if ($request->getMethod() == "POST") {
            $helper = new ShopifyHelper($this->shop);
            if (!$helper->isWidgetScriptInstalled())
                $helper->installWidgetScriptTag();
            $this->shop->enable_popup_script = $request->post('action') == 'install';
            $this->shop->save();
            return response()->json([
                'message' => "Popup module updated",
                'popup_module_enabled' => $this->shop->enable_popup_script,
            ]);
        }
        return response()->json([
            'popup_module_enabled' => $this->shop->enable_popup_script,
        ]);
    }

    public function ip_location(): JsonResponse
    {
        $location = null; //Location::get();

        return response()->json([
            'location' => $location
        ]);
    }

    function update_automation_import_timestamp()
    {
        $this->selected_customer->automations_imported_at = Carbon::now();
        $this->selected_customer->save();
    }

    function update_popup_import_timestamp()
    {
        $this->selected_customer->popups_imported_at = Carbon::now();
        $this->selected_customer->save();
    }


    function cb_snippets()
    {
        $response = Response::view('cb_content', [
            'selected_customer' => $this->selected_customer
        ]);
        $response->header("Content-Type", "text/javascript");

        return $response;
    }
}
