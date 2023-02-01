<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;
use Acelle\Model\Automation2;

class Automation2Policy
{
    use HandlesAuthorization;

    public function create(User $user, Automation2 $automation)
    {
        return true;
        $customer = request()->selected_customer;
        $max = $customer->getOption('automation_max');

        return $max > $customer->automationsCount() || $max == -1;
    }

    public function view(User $user, Automation2 $automation)
    {
        return $automation->customer_id == request()->selected_customer->id;
    }

    public function update(User $user, Automation2 $automation)
    {
        return $automation->customer_id == request()->selected_customer->id;
    }


    public function enable(User $user, Automation2 $automation)
    {
        return $automation->customer_id == request()->selected_customer->id &&
            in_array($automation->status, [
                Automation2::STATUS_INACTIVE
            ]);
    }

    public function disable(User $user, Automation2 $automation)
    {
        return $automation->customer_id == request()->selected_customer->id &&
            in_array($automation->status, [
                Automation2::STATUS_ACTIVE
            ]);
    }

    public function delete(User $user, Automation2 $automation)
    {
        return $automation->customer_id == request()->selected_customer->id &&
            in_array($automation->status, [
                Automation2::STATUS_ACTIVE,
                Automation2::STATUS_INACTIVE
            ]);
    }

    public function copy(User $user, Automation2 $automation)
    {
        if ($automation->default_for_new_customers) return true;
        return $automation->customer_id == request()->selected_customer->id;
    }
}
