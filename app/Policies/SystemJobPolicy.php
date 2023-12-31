<?php

namespace Acelle\Policies;

use Illuminate\Auth\Access\HandlesAuthorization;
use Acelle\Model\User;
use Acelle\Model\SystemJob;

class SystemJobPolicy
{
    use HandlesAuthorization;
    
    public $jobs = [
        'Acelle\Jobs\ImportSubscribersJob',
        'Acelle\Jobs\ExportSubscribersJob',
        'Acelle\Jobs\ExportSegmentsJob',
    ];

    public function delete(User $user, SystemJob $item)
    {
        if (in_array($item->name, $this->jobs)) {
            $data = json_decode($item->data);
            $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);

            $customer_ids = [];
            foreach ($user->customers as $customer) {
                $customer_ids[] = $customer->id;
            }

            return in_array($list->customer_id, $customer_ids) && !$item->isRunning();
        }

        return false;
    }

    public function downloadImportLog(User $user, SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);

        $customer_ids = [];
        foreach ($user->customers as $customer) {
            $customer_ids[] = $customer->id;
        }

        return in_array($list->customer_id, $customer_ids) &&
            $item->name == 'Acelle\Jobs\ImportSubscribersJob' &&
            $data->status == 'done';
    }

    public function downloadExportCsv(User $user, SystemJob $item)
    {
        $data = json_decode($item->data);
        $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);

        $customer_ids = [];
        foreach ($user->customers as $customer) {
            $customer_ids[] = $customer->id;
        }

        return in_array($list->customer_id, $customer_ids) &&
            ($item->name == 'Acelle\Jobs\ExportSubscribersJob' || $item->name == 'Acelle\Jobs\ExportSegmentsJob') &&
            $data->status == 'done';
    }

    public function cancel(User $user, SystemJob $item)
    {
        if (in_array($item->name, $this->jobs)) {
            $data = json_decode($item->data);
            $list = \Acelle\Model\MailList::findByUid($data->mail_list_uid);

            $customer_ids = [];
            foreach ($user->customers as $customer) {
                $customer_ids[] = $customer->id;
            }

            return in_array($list->customer_id, $customer_ids) &&
                ($item->isRunning() || $item->isNew());
        }

        return false;
    }
}
