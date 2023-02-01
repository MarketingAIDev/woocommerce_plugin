<?php

namespace Acelle\Providers;

use Acelle\Model\Admin;
use Acelle\Model\AdminGroup;
use Acelle\Model\Automation2;
use Acelle\Model\Blacklist;
use Acelle\Model\BounceHandler;
use Acelle\Model\Campaign;
use Acelle\Model\Contact;
use Acelle\Model\Currency;
use Acelle\Model\Customer;
use Acelle\Model\CustomerGroup;
use Acelle\Model\EmailVerificationServer;
use Acelle\Model\FeedbackLoopHandler;
use Acelle\Model\Language;
use Acelle\Model\Layout;
use Acelle\Model\MailList;
use Acelle\Model\PaymentMethod;
use Acelle\Model\Plan;
use Acelle\Model\Popup;
use Acelle\Model\Segment;
use Acelle\Model\Segment2;
use Acelle\Model\Sender;
use Acelle\Model\SendingDomain;
use Acelle\Model\SendingServer;
use Acelle\Model\ShopifyShop;
use Acelle\Model\SubAccount;
use Acelle\Model\Subscriber;
use Acelle\Model\SystemJob;
use Acelle\Model\Template;
use Acelle\Model\TrackingDomain;
use Acelle\Model\User;
use Acelle\Policies\AdminGroupPolicy;
use Acelle\Policies\AdminPolicy;
use Acelle\Policies\Automation2Policy;
use Acelle\Policies\BlacklistPolicy;
use Acelle\Policies\BounceHandlerPolicy;
use Acelle\Policies\CampaignPolicy;
use Acelle\Policies\ContactPolicy;
use Acelle\Policies\CurrencyPolicy;
use Acelle\Policies\CustomerGroupPolicy;
use Acelle\Policies\CustomerPolicy;
use Acelle\Policies\EmailVerificationServerPolicy;
use Acelle\Policies\FeedbackLoopHandlerPolicy;
use Acelle\Policies\LanguagePolicy;
use Acelle\Policies\LayoutPolicy;
use Acelle\Policies\MailListPolicy;
use Acelle\Policies\PaymentMethodPolicy;
use Acelle\Policies\PlanPolicy;
use Acelle\Policies\PopupPolicy;
use Acelle\Policies\Segment2Policy;
use Acelle\Policies\SegmentPolicy;
use Acelle\Policies\SenderPolicy;
use Acelle\Policies\SendingDomainPolicy;
use Acelle\Policies\SendingServerPolicy;
use Acelle\Policies\SettingPolicy;
use Acelle\Policies\ShopifyShopPolicy;
use Acelle\Policies\SubAccountPolicy;
use Acelle\Policies\SubscriberPolicy;
use Acelle\Policies\SystemJobPolicy;
use Acelle\Policies\TemplatePolicy;
use Acelle\Policies\TrackingDomainPolicy;
use Acelle\Policies\UserPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Acelle\Model\Setting;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        Admin::class => AdminPolicy::class,
        AdminGroup::class => AdminGroupPolicy::class,
        Automation2::class => Automation2Policy::class,
        Blacklist::class => BlacklistPolicy::class,
        BounceHandler::class => BounceHandlerPolicy::class,
        Campaign::class => CampaignPolicy::class,
        Contact::class => ContactPolicy::class,
        Currency::class => CurrencyPolicy::class,
        Customer::class => CustomerPolicy::class,
        CustomerGroup::class => CustomerGroupPolicy::class,
        EmailVerificationServer::class => EmailVerificationServerPolicy::class,
        FeedbackLoopHandler::class => FeedbackLoopHandlerPolicy::class,
        Language::class => LanguagePolicy::class,
        Layout::class => LayoutPolicy::class,
        MailList::class => MailListPolicy::class,
        PaymentMethod::class => PaymentMethodPolicy::class,
        Plan::class => PlanPolicy::class,
        Popup::class => PopupPolicy::class,
        Segment::class => SegmentPolicy::class,
        Segment2::class => Segment2Policy::class,
        Sender::class => SenderPolicy::class,
        SendingDomain::class => SendingDomainPolicy::class,
        SendingServer::class => SendingServerPolicy::class,
        Setting::class => SettingPolicy::class,
        ShopifyShop::class => ShopifyShopPolicy::class,
        Subscriber::class => SubscriberPolicy::class,
        SubAccount::class => SubAccountPolicy::class,
        SystemJob::class => SystemJobPolicy::class,
        Template::class => TemplatePolicy::class,
        TrackingDomain::class => TrackingDomainPolicy::class,
        User::class => UserPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot()
    {
        $this->registerPolicies();
    }
}
