<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Automation2;
use Acelle\Model\Customer;
use Acelle\Model\Popup;
use Acelle\Model\ShopifyShop;
use Acelle\Model\User;
use Illuminate\Http\Request;
use Acelle\Model\Notification as AppNotification;

class NotificationController extends Controller
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

    public function index(Request $request)
    {
        $notifications = AppNotification::where(AppNotification::COLUMN_customer_id, $this->selected_customer->id)
            ->paginate($request->per_page ?? 20);

        if ($request->expectsJson()) {
            return response()->json([
                'items' => $notifications,
                'new_automations_count' => Automation2::getNewPublicAutomationsCount($this->selected_customer),
                'new_popups_count' => Popup::getNewPublicPopupsCount($this->selected_customer)
            ]);
        }
        return view('notifications.index', ['notifications' => $notifications]);
    }

    public function destroy($id)
    {
        $notification = AppNotification::findByUid($id);
        $notification->delete();
        return response(null, 200);
    }

    public function hide($id)
    {
        $notification = AppNotification::findByUid($id);
        $notification->hide();
        return response(null, 200);
    }
}
