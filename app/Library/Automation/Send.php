<?php

namespace Acelle\Library\Automation;

use Acelle\Model\DebugLog;
use Acelle\Model\Email;

class Send extends Action
{
    public function execute()
    {
        $subscriber = $this->autoTrigger->subscriber;
        $debug_data = [
            "subscriber" => $subscriber->email,
            "status" => $subscriber->status,
        ];
        DebugLog::automation2Log("execute_send", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, $debug_data);
        if (!$subscriber->isActive()) {
            $this->autoTrigger->logger()->warning(sprintf('Subscriber "%s" is not active (current status: "%s")', $subscriber->email, $subscriber->status));
            DebugLog::automation2Log("execute_send_cancelled_1", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, $debug_data);
            return false;
        }

        if ($this->options['init'] == 'false' || $this->options['init'] == false) {
            $this->autoTrigger->logger()->warning('Email not set up yet');

            DebugLog::automation2Log("execute_send_cancelled_2", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, $debug_data);
            return false;
        }

        if ($this->autoTrigger->automation2->customer->overQuota()){
            $this->autoTrigger->logger()->warning("Customer has reached sending limits!");
            DebugLog::automation2Log("execute_send_cancelled_3", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, $debug_data);
            return false;
        }

        // IMPORTANT
        // If this is the latest also the last action of the workflow
        // no more execute, just return true
        if (!is_null($this->last_executed)) {
            $this->autoTrigger->logger()->warning('Send action already executed');

            DebugLog::automation2Log("already_executed", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, $debug_data);
            return true; // in case of adding new element after this, but duplicate log
        }

        $email = $this->getEmail();

        if (config('app.demo') != true) {
            sleep(1); // to avoid same date/time with previous wait, wrong timeline order
            // dispatch(new DeliverEmail($email, $this->autoTrigger->subscriber));
            // $queue = true;
            $context = ShopifyAutomationContext::fromExtraData($this->autoTrigger->extra_data);
            $email->queueDeliverTo($this->autoTrigger->subscriber, $this->autoTrigger->id, $context); // queue, do not send immediately
        }

        $this->autoTrigger->logger()->info(sprintf('Send email entitled "%s" to "%s", queued', $email->subject, $this->autoTrigger->subscriber->email));

        $this->recordLastExecutedTime();
        $this->evaluationResult = true;

        return true;
    }

    public function getEmail()
    {
        return Email::findByUid($this->options['email_uid']);
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('User %s receives email entitled "%s"', $nameOrEmail, $this->getEmail()->subject);
    }
}
