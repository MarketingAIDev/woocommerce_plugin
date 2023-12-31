<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;

class UserPolicy
{
    use HandlesAuthorization;
    
    public function read(User $user)
    {
        $can = $user->admin->getPermission('user_read') != 'no';

        return $can;
    }
    
    public function read_all(User $user)
    {
        $can = $user->admin->getPermission('user_read') == 'all';

        return $can;
    }

    public function create(User $user)
    {
        $can = $user->admin->getPermission('user_create') == 'yes';

        return $can;
    }

    public function profile(User $user, User $item)
    {
        return $user->id == $item->id;
    }

    public function update(User $user, User $item)
    {
        $ability = $user->admin->getPermission('user_update');
        $can = $ability == 'all'
                || ($ability == 'own' && $user->id == $item->user_id);

        return $can;
    }

    public function delete(User $user, User $item)
    {
        $ability = $user->admin->getPermission('user_delete');
        $can = $ability == 'all'
                || ($ability == 'own' && $user->id == $item->user_id);
        $can = $can && $user->id != $item->id;

        return $can;
    }

    public function switch_user(User $user, User $item)
    {
        $ability = $user->admin->getPermission('user_switch');
        $can = $ability == 'all'
                || ($ability == 'own' && $user->id == $item->user_id);
        $can = $can && $item->id != $user->id;

        return $can;
    }
    
    public function customer_access(User $user)
    {
        return true;//count($request->selected_customer)>0;
    }
    
    public function admin_access(User $user)
    {        
        return is_object($user->admin);
    }
    
    public function reseller_access(User $user)
    {        
        return is_object($user->reseller);
    }
    
    public function change_group(User $user)
    {
        $ability = $user->admin->getPermission('user_update');
        $can = $ability == 'all';

        return $can;
    }
}
