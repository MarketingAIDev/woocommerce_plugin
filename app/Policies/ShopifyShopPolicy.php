<?php

namespace Acelle\Policies;

use Acelle\Model\ShopifyShop;
use Acelle\Model\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShopifyShopPolicy
{
    use HandlesAuthorization;

    public function update(User $user, ShopifyShop $item)
    {
        return $item->customer->user->id == $user->id;
    }

    public function delete(User $user, ShopifyShop $item)
    {
        return $item->customer->user->id == $user->id;
    }
}
