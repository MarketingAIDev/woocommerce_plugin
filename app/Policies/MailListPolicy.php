<?php

namespace Acelle\Policies;

use Acelle\Model\Customer;
use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;
use Acelle\Model\MailList;

class MailListPolicy
{
    use HandlesAuthorization;
    
    private function customer_id_exists($customer_id, User $user) {
        foreach ($user->customers as $customer) {
            if($customer->id == $customer_id)
                return true;
        }
        return false;
    }

    public function read(User $user, MailList $item)
    {
        return $this->customer_id_exists($item->customer_id, $user);
    }

    public function create(User $user)
    {
        return true;
        $customer = request()->selected_customer;
        $max = $customer->getOption('list_max');

        return $max > $customer->lists()->count() || $max == -1;
    }

    public function update(User $user, MailList $item)
    {
        return $this->customer_id_exists($item->customer_id, $user);
    }

    public function delete(User $user, MailList $item)
    {
        if($item->shopify_shop_id) return false;
        return $this->customer_id_exists($item->customer_id, $user);
    }

    public function addMoreSubscribers(User $user, MailList $mailList, $numberOfSubscribers, Customer $customer)
    {
        $max = $customer->getOption('subscriber_max');
        $maxPerList = $customer->getOption('subscriber_per_list_max');
        return $customer->id == $mailList->customer_id &&
            ($max >= $customer->subscribersCount() + $numberOfSubscribers || $max == -1) &&
            ($maxPerList >= $mailList->subscribersCount() + $numberOfSubscribers || $maxPerList == -1);
    }

    public function import(User $user, MailList $item)
    {
        /** @var Customer $customer */
        $customer = request()->selected_customer;
        $can = $customer->getOption('list_import');

        return ($can == 'yes' && $item->customer_id == $customer->id);
    }

    public function export(User $user, MailList $item)
    {
        $customer = request()->selected_customer;
        $can = $customer->getOption('list_export');

        return ($can == 'yes' && $item->customer_id == $customer->id);
    }
}
