<?php

namespace Acelle\Library\Automation;

use Acelle\Model\DebugLog;
use Acelle\Model\Email;
use Acelle\Model\EmailLink;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyReview;

class Evaluate extends Action
{
    protected $childYes;
    protected $childNo;

    const CONDITION_open = 'open';
    const CONDITION_click = 'click';
    const CONDITION_review_image = 'review_image';
    const CONDITION_review_stars = 'review_stars';
    const CONDITION_review_submitted = 'review_submitted';
    const CONDITION_chat_stars = 'chat_stars';
    const CONDITION_discount_coupon_used = 'discount_coupon_used';
    const CONDITION_checkout_abandoned = 'checkout_abandoned';

    public function __construct($params = [])
    {
        parent::__construct($params);

        $this->childYes = array_key_exists('childYes', $params) ? $params['childYes'] : null;
        $this->childNo = array_key_exists('childNo', $params) ? $params['childNo'] : null;
    }

    public function toJson()
    {
        $json = parent::toJson();
        $json = array_merge($json, [
            'childYes' => $this->childYes,
            'childNo' => $this->childNo,
        ]);

        return $json;
    }

    public function execute()
    {
        DebugLog::automation2Log("execute_evaluate", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, []);
        try {
            // IMPORTANT
            // If this is the latest also the last action of the workflow
            // no more execute, just return true
            // UPDATE: check always, wait for open/click anyway! if it is the last action
            // if (!is_null($this->last_executed)) {
            //     $this->autoTrigger->logger()->info('Latest also last action');
            //     return true;
            // }

            $result = $this->evaluateCondition();

            if (config('app.demo') == true) {
                $result = (bool)random_int(0, 1);
            }

            $this->evaluationResult = $result;

            $this->recordLastExecutedTime();

            // always return true, not evaluation result!
            return true;
        } catch (\Exception $ex) {
            // @todo automation error
            // show it up to UI
            $this->autoTrigger->logger()->warning(sprintf('Error while executing Condition %s. Error message: %s', $this->getId(), $ex->getMessage()));

            return false;
        }
    }

    public function evaluateCondition()
    {
        $criterion = $this->getOption('type');
        $result = null;

        switch ($criterion) {
            case self::CONDITION_open:
                if (empty($this->getOption('email'))) {
                    throw new \Exception('Email missing for open condition');
                }
                $result = $this->evaluateEmailOpenCondition();
                break;
            case self::CONDITION_click:
                if (empty($this->getOption('email_link'))) {
                    throw new \Exception('URL missing for click condition');
                }
                $result = $this->evaluateEmailClickCondition();
                break;
            case self::CONDITION_review_image:
                $result = $this->evaluateReviewImageCondition();
                break;
            case self::CONDITION_review_stars:
                $result = $this->evaluateReviewStarsCondition();
                break;
            case self::CONDITION_review_submitted:
                $result = $this->evaluateReviewSubmittedCondition();
                break;
            case self::CONDITION_chat_stars:
                $result = $this->evaluateChatStarsCondition();
                break;
            case self::CONDITION_discount_coupon_used:
                $result = $this->evaluateDiscountCouponUsedCondition();
                break;
            case self::CONDITION_checkout_abandoned:
                $result = $this->evaluateCartAbandonedCondition();
                break;
            default:
                # code...
                break;
        }

        return $result;
    }

    public function evaluateEmailOpenCondition(): bool
    {
        $emailUid = $this->getOption('email');
        $email = Email::findByUid($emailUid);

        return $email->isOpened($this->autoTrigger->subscriber);
    }

    public function evaluateEmailClickCondition(): bool
    {
        $linkUid = $this->getOption('email_link');
        $email = EmailLink::findByUid($linkUid)->email;

        return $email->isClicked($this->autoTrigger->subscriber);
    }

    public function evaluateCartAbandonedCondition(): bool
    {
        $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
        $checkout = $context->shopifyAbandonedCheckout;
        return $checkout != null && $checkout->shopify_completed_at == null;
    }

    public function evaluateReviewImageCondition(): bool
    {
        $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
        DebugLog::automation2Log('evaluate_review_image', $this->autoTrigger->automation2->name, $context ? $context->toArray() : []);
        $review = $context->shopifyReview;
        return $review != null && $review->images()->count() > 0;
    }

    public function evaluateReviewStarsCondition(): bool
    {
        $review_stars_lte = (int)$this->getOption('review_stars_lte');
        $review_stars_gte = (int)$this->getOption('review_stars_gte');
        $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
        $review = $context->shopifyReview;
        return $review != null && $review->stars <= $review_stars_lte && $review->stars >= $review_stars_gte;
    }

    public function evaluateReviewSubmittedCondition(): bool
    {
        $review_products_type_all = $this->getOption('review_products_type') == "all";

        $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
        $order = $context->shopifyOrder;

        if (!$order) return false;

        $items = $order->getShopifyModel()->line_items;
        $shopify_product_ids = [];
        foreach ($items as $item) {
            $shopify_product_ids[] = $item->product_id;
        }

        $automation_started_date = $this->autoTrigger->created_at;

        $review_exists = [];
        foreach ($shopify_product_ids as $shopify_product_id) {
            $reviews = ShopifyReview::findByProductAfterDate($shopify_product_id, $automation_started_date);
            $review_exists[] = count($reviews) > 0;
        }

        if ($review_products_type_all) {
            return count($review_exists) > 0 && array_product($review_exists);
        } else {
            return count($review_exists) > 0 && array_sum($review_exists);
        }
    }

    public function evaluateDiscountCouponUsedCondition(): bool
    {
        $coupon = $this->getOption('discount_coupon');
        $customer = $this->autoTrigger->subscriber->shopify_customer;

        return $customer && $coupon && $customer->hasUsedDiscountCoupon($coupon);
    }

    public function evaluateChatStarsCondition(): bool
    {
        $chat_stars_lte = (int)$this->getOption('chat_stars_lte');
        $chat_stars_gte = (int)$this->getOption('chat_stars_gte');
        $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
        $chatSession = $context->chatSession;
        return $chatSession != null && $chatSession->feedback_rating <= $chat_stars_lte && $chatSession->feedback_rating >= $chat_stars_gte;
    }

    public function getActionDescription(): string
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();
        $options = $this->getOptions();

        $criterion = $this->getOption('type');
        switch ($criterion) {
            case self::CONDITION_open:
                if (empty($this->getOption('email'))) {
                    throw new \Exception('Email missing for open condition');
                }
                $emailUid = $this->getOption('email');
                $email = Email::findByUid($emailUid);
                return sprintf('Tracking: waiting for user %s to READ email entitled "%s"', $nameOrEmail, $email->subject);
            case self::CONDITION_click:
                if (empty($this->getOption('email_link'))) {
                    throw new \Exception('URL missing for click condition');
                }
                $linkUid = $this->getOption('email_link');
                $email = EmailLink::findByUid($linkUid)->email;
                return sprintf('Tracking: waiting for user %s to CLICK email entitled "%s"', $nameOrEmail, $email->subject);
            case self::CONDITION_review_image:
                return 'Condition: checking review image';
            case self::CONDITION_review_stars:
                return 'Condition: checking review stars';
            case self::CONDITION_review_submitted:
                return 'Condition: checking review submitted';
            case self::CONDITION_chat_stars:
                return 'Condition: checking chat stars';
            case self::CONDITION_discount_coupon_used:
                return 'Condition: checking discount coupon';
            case self::CONDITION_checkout_abandoned:
                return 'Condition: checking abandoned checkouts';
            default:
                # code...
                break;
        }
        return '';
    }

    public function hasChild($e)
    {
        if (is_null($this->childYes) && is_null($this->childNo)) {
            return false;
        }

        return $e->getId() == $this->childYes || $e->getId() == $this->childNo;
    }

    public function getNextActionId()
    {
        if ($this->evaluationResult) {
            return $this->childYes;
        } else {
            return $this->childNo;
        }
    }

    public function getChildYesId()
    {
        return $this->childYes;
    }

    public function getChildNoId()
    {
        return $this->childNo;
    }
}
