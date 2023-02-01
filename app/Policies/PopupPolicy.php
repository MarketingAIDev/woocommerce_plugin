<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;
use Acelle\Model\Popup;

class PopupPolicy
{
    use HandlesAuthorization;

    public function view(User $user, Popup $popup)
    {
        return $popup->customer_id == request()->selected_customer->id;
    }

    public function update(User $user, Popup $popup)
    {
        return $popup->customer_id == request()->selected_customer->id;
    }

    public function copy(User $user, Popup $popup)
    {
        if ($popup->default_for_new_customers) return true;
        return $popup->customer_id == request()->selected_customer->id;
    }
}
