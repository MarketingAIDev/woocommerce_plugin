<?php


namespace Acelle\Library\Automation;


use Acelle\Model\ChatSession;
use Acelle\Model\PopupResponse;
use Acelle\Model\ShopifyCheckout;
use Acelle\Model\ShopifyCustomer;
use Acelle\Model\ShopifyFulfillment;
use Acelle\Model\ShopifyOrder;
use Acelle\Model\ShopifyReview;
use Acelle\Model\ShopifyShop;
use Acelle\Model\Subscriber;

class ShopifyAutomationContext
{
    const AUTOMATION_TRIGGER_CHAT_ENDED = 'chat-ended';
    const AUTOMATION_TRIGGER_REVIEW_SUBMITTED = 'review-submitted';
    const AUTOMATION_TRIGGER_REVIEW_UPDATED = 'review-updated';
    const AUTOMATION_TRIGGER_POPUP_SUBMITTED = 'popup-submitted';
    const AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED = 'shopify-order-placed';
    const AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED = 'shopify-order-fulfilled';
    const AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED = 'shopify-order-delivered';
    const AUTOMATION_TRIGGER_SHOPIFY_CHECKOUT_ABANDONED = 'shopify-checkout-abandoned';

    const AUTOMATION_TRIGGERS = [
        self::AUTOMATION_TRIGGER_CHAT_ENDED,
        self::AUTOMATION_TRIGGER_REVIEW_SUBMITTED,
        self::AUTOMATION_TRIGGER_REVIEW_UPDATED,
        self::AUTOMATION_TRIGGER_POPUP_SUBMITTED,
        self::AUTOMATION_TRIGGER_SHOPIFY_ORDER_PLACED,
        self::AUTOMATION_TRIGGER_SHOPIFY_ORDER_FULFILLED,
        self::AUTOMATION_TRIGGER_SHOPIFY_ORDER_DELIVERED,
        self::AUTOMATION_TRIGGER_SHOPIFY_CHECKOUT_ABANDONED,
    ];

    const ADMIN_AUTOMATION_TRIGGER_UNATTENDED_CHAT = 'admin-unattended-chat';
    const ADMIN_AUTOMATION_TRIGGER_FIRST_CHAT = 'admin-first-chat';
    const ADMIN_AUTOMATION_TRIGGER_FIRST_POPUP_RESPONSE = 'admin-first-popup-response';
    const ADMIN_AUTOMATION_TRIGGER_FIRST_REVIEW = 'admin-first-review';
    const ADMIN_AUTOMATION_TRIGGER_ADDITIONAL_SHOP_ADDED = 'admin-additional-shop-added';
    const ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED = 'admin-emailwish-order-placed';
    const ADMIN_AUTOMATION_TRIGGER_HIGH_MISSED_CHATS = 'admin-high-missed-chats';
    const ADMIN_AUTOMATION_TRIGGER_HIGH_SALES = 'admin-high-sales';
    const ADMIN_AUTOMATION_TRIGGER_LOW_SALES = 'admin-low-sales';
    const ADMIN_AUTOMATION_TRIGGER_HIGH_UNSUBSCRIBE_RATE = 'admin-high-unsubscribe-rate';
    const ADMIN_AUTOMATION_TRIGGER_HIGH_SPAM_RATE = 'admin-high-spam-rate';
    const ADMIN_AUTOMATION_TRIGGER_HIGH_BOUNCE_RATE = 'admin-high-bounce-rate';

    const ADMIN_AUTOMATION_TRIGGERS = [
        self::ADMIN_AUTOMATION_TRIGGER_UNATTENDED_CHAT,
        self::ADMIN_AUTOMATION_TRIGGER_FIRST_CHAT,
        self::ADMIN_AUTOMATION_TRIGGER_FIRST_POPUP_RESPONSE,
        self::ADMIN_AUTOMATION_TRIGGER_FIRST_REVIEW,
        self::ADMIN_AUTOMATION_TRIGGER_ADDITIONAL_SHOP_ADDED,
        self::ADMIN_AUTOMATION_TRIGGER_EMAILWISH_ORDER_PLACED,
        self::ADMIN_AUTOMATION_TRIGGER_HIGH_MISSED_CHATS,
        self::ADMIN_AUTOMATION_TRIGGER_HIGH_SALES,
        self::ADMIN_AUTOMATION_TRIGGER_LOW_SALES,
        self::ADMIN_AUTOMATION_TRIGGER_HIGH_UNSUBSCRIBE_RATE,
        self::ADMIN_AUTOMATION_TRIGGER_HIGH_SPAM_RATE,
        self::ADMIN_AUTOMATION_TRIGGER_HIGH_BOUNCE_RATE,
    ];

    const SUBSCRIBER_UID = 'subscriber_uid';
    const SHOPIFY_CUSTOMER_UID = 'shopify_customer_uid';
    const SHOPIFY_FULFILLMENT_UID = 'shopify_fulfillment_uid';
    const SHOPIFY_ORDER_UID = 'shopify_order_uid';
    const SHOPIFY_CHECKOUT_UID = 'shopify_checkout_uid';
    const SHOPIFY_SHOP_UID = 'shopify_shop_uid';
    const CHAT_SESSION_UID = 'chat_session_uid';
    const REVIEW_UID = 'review_uid';
    const POPUP_RESPONSE_UID = 'popup_response_uid';

    /** @var Subscriber|null */
    public $subscriber;

    /** @var ShopifyCustomer|null */
    public $shopifyCustomer;

    /** @var ShopifyFulfillment|null */
    public $shopifyFulfillment;

    /** @var ShopifyOrder|null */
    public $shopifyOrder;

    /** @var ShopifyCheckout|null */
    public $shopifyAbandonedCheckout;

    /** @var ShopifyShop|null */
    public $shopifyShop;

    /** @var ChatSession|null */
    public $chatSession;

    /** @var ShopifyReview|null */
    public $shopifyReview;

    /** @var PopupResponse|null */
    public $popupResponse;


    function toArray()
    {
        $json = [];
        $json['subscriber'] = $this->subscriber ? $this->subscriber->toArray() : [];
        $json['shopifyCustomer'] = $this->shopifyCustomer ? $this->shopifyCustomer->toArray() : [];
        $json['shopifyFulfillment'] = $this->shopifyFulfillment ? $this->shopifyFulfillment->toArray() : [];
        $json['shopifyOrder'] = $this->shopifyOrder ? $this->shopifyOrder->toArray() : [];
        $json['shopifyAbandonedCheckout'] = $this->shopifyAbandonedCheckout ? $this->shopifyAbandonedCheckout->toArray() : [];
        $json['shopifyShop'] = $this->shopifyShop ? $this->shopifyShop->toArray() : [];
        $json['chatSession'] = $this->chatSession ? $this->chatSession->toArray() : [];
        $json['shopifyReview'] = $this->shopifyReview ? $this->shopifyReview->toArray() : [];
        return $json;
    }

    function toExtraData(): array
    {
        $data = [];
        if ($this->subscriber)
            $data[self::SUBSCRIBER_UID] = $this->subscriber->uid;
        if ($this->shopifyCustomer)
            $data[self::SHOPIFY_CUSTOMER_UID] = $this->shopifyCustomer->uid;
        if ($this->shopifyOrder)
            $data[self::SHOPIFY_ORDER_UID] = $this->shopifyOrder->uid;
        if ($this->shopifyFulfillment)
            $data[self::SHOPIFY_FULFILLMENT_UID] = $this->shopifyFulfillment->uid;
        if ($this->shopifyAbandonedCheckout)
            $data[self::SHOPIFY_CHECKOUT_UID] = $this->shopifyAbandonedCheckout->uid;
        if ($this->shopifyShop)
            $data[self::SHOPIFY_SHOP_UID] = $this->shopifyShop->uid;
        if ($this->chatSession)
            $data[self::CHAT_SESSION_UID] = $this->chatSession->id;
        if ($this->shopifyReview)
            $data[self::REVIEW_UID] = $this->shopifyReview->uid;
        if ($this->popupResponse)
            $data[self::POPUP_RESPONSE_UID] = $this->popupResponse->id;

        return $data;
    }

    static function fromExtraData(array $data): self
    {
        $self = new self();
        if (!empty($data[self::SUBSCRIBER_UID]))
            $self->subscriber = Subscriber::findByUid($data[self::SUBSCRIBER_UID]);
        if (!empty($data[self::SHOPIFY_CUSTOMER_UID]))
            $self->shopifyCustomer = ShopifyCustomer::findByUid($data[self::SHOPIFY_CUSTOMER_UID]);
        if (!empty($data[self::SHOPIFY_ORDER_UID]))
            $self->shopifyOrder = ShopifyOrder::findByUid($data[self::SHOPIFY_ORDER_UID]);
        if (!empty($data[self::SHOPIFY_FULFILLMENT_UID]))
            $self->shopifyFulfillment = ShopifyFulfillment::findByUid($data[self::SHOPIFY_FULFILLMENT_UID]);
        if (!empty($data[self::SHOPIFY_CHECKOUT_UID]))
            $self->shopifyAbandonedCheckout = ShopifyCheckout::findByUid($data[self::SHOPIFY_CHECKOUT_UID]);
        if (!empty($data[self::SHOPIFY_SHOP_UID]))
            $self->shopifyShop = ShopifyShop::findByUid($data[self::SHOPIFY_SHOP_UID]);
        if (!empty($data[self::CHAT_SESSION_UID]))
            $self->chatSession = ChatSession::findByid($data[self::CHAT_SESSION_UID]);
        if (!empty($data[self::REVIEW_UID]))
            $self->shopifyReview = ShopifyReview::findByUid($data[self::REVIEW_UID]);
        if (!empty($data[self::POPUP_RESPONSE_UID]))
            $self->popupResponse = PopupResponse::findByUid($data[self::POPUP_RESPONSE_UID]);
        return $self;
    }
}