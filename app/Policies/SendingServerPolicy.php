<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\SendingServer;
use Acelle\Model\User;

class SendingServerPolicy
{
    use HandlesAuthorization;

    public function read(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_server_read') != 'no';
                break;
            case 'customer':
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN;
                break;
        }

        return $can;
    }

    public function readAll(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_server_read') == 'all';
                break;
            case 'customer':
                $can = false;
                break;
        }

        return $can;
    }

    public function create(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $can = $user->admin->getPermission('sending_server_create') == 'yes';
                break;
            case 'customer':
                $max = request()->selected_customer->getOption('sending_servers_max');
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN
                    && (request()->selected_customer->sendingServersCount() < $max || $max == -1);
                $can = $can && (!isset($item->type) || request()->selected_customer->isAllowCreateSendingServerType($item->type));
                break;
        }

        return $can;
    }

    public function update(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_server_update');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN
                        && request()->selected_customer->id == $item->customer_id;
                break;
        }

        return $can;
    }

    public function delete(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_server_delete');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN
                    && request()->selected_customer->id == $item->customer_id;
                break;
        }

        return $can;
    }

    public function disable(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_server_update');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN
                    && request()->selected_customer->id == $item->customer_id;
                break;
        }

        return $can && $item->status != "inactive";
    }

    public function enable(User $user, SendingServer $item, $role)
    {
        switch ($role) {
            case 'admin':
                $ability = $user->admin->getPermission('sending_server_update');
                $can = $ability == 'all'
                    || ($ability == 'own' && $user->admin->id == $item->admin_id);
                break;
            case 'customer':
                $can = request()->selected_customer->getOption('sending_server_option') == \Acelle\Model\Plan::SENDING_SERVER_OPTION_OWN
                    && request()->selected_customer->id == $item->customer_id;
                break;
        }

        return $can && $item->status != "active";
    }

    public function test(User $user, SendingServer $item, $role)
    {
        return $this->update($user, $item, $role) || !isset($item->id);
    }
}
