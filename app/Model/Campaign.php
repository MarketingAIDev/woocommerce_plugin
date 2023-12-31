<?php

/**
 * Campaign class.
 *
 * Model class for campaigns related functionalities.
 * This is the center of the application
 *
 * LICENSE: This product includes software developed at
 * the Acelle Co., Ltd. (http://acellemail.com/).
 *
 * @category   MVC Model
 *
 * @author     N. Pham <n.pham@acellemail.com>
 * @author     L. Pham <l.pham@acellemail.com>
 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Helpers\EmailImageHelper;
use Acelle\Library\Log as MailLog;
use Acelle\Exceptions\CampaignPausedException;
use Acelle\Exceptions\CampaignErrorException;
use Exception;
use Illuminate\Support\Facades\DB;


class Campaign extends AbsCampaign
{
    public function saveImages()
    {
        $content = $this->html;
        // find all img tags in html content
        preg_match_all('/(<img[^>]+src=)([\'"])(?<src>.*?)(\2)/i', $content, $matches);
        $srcs = array_unique($matches['src']);

        foreach ($srcs as $src) {
            if (empty(trim($src))) {
                continue;
            }
            $asset_url_root = route('campaign_assets', ['uid' => $this->uid], false);
            $storage_folder = $this->getStoragePath();
            $new_path = EmailImageHelper::saveImage($asset_url_root, $storage_folder, $src);

            try {
                $content = preg_replace('/(<img[^>]+src=)([\'"])(' . preg_quote($src, '/') . ')(\2)/', '$1$2' . $new_path . '$4', $content);
            } catch (\ErrorException $ex) {
                // preg_replace failed
                // use the old and non-safe way!!!!
                $content = str_replace($src, $new_path, $content);
            }

        }

        $this->html = $content;
    }

    /**
     * Start the campaign.
     */
    public function start($trigger = null)
    {
        $this->log('start', $this->customer, ['started to run']);
        try {
            // make sure the same campaign is not started twice
            if (!$this->refreshStatus()->isReady()) {
                MailLog::info("Campaign ID: {$this->id} is not ready or already started");
                $this->log("Campaign ID: {$this->id} is not ready or already started", $this->customer, ["Campaign ID: {$this->id} is not ready or already started"]);

                return;
            }

            if (!$this->customer->shopify_shop->active) {
                throw new \Exception('Shopify shop is inactive, please activate shop.');
                $this->pause();
                return;
            }

            // clean up the tracker to prevent the log file from growing very big
            $this->customer->cleanupQuotaTracker();

            // lock the campaign, marked as sending
            $this->sending();
            MailLog::info('Starting campaign `' . $this->name . '`');
            $this->log('Starting campaig', $this->customer, ['Starting campaign `' . $this->name . '`']);

            // Reset max_execution_time so that command can run for a long time without being terminated
            self::resetMaxExecutionTime();

            // Only run multi-process if pcntl is enabled
            $processes = (int)$this->customer->getOption('max_process');
            if (extension_loaded('pcntl') && $processes > 1) {
                MailLog::info('Run in multi-process mode');
                $this->log('Run in multi-process mode', $this->customer, ['Run in multi-process mode']);
                $this->runMultiProcesses();
            } else {
                MailLog::info('Run in single-process mode');
                $this->log('Run in single-process mode', $this->customer, ['Run in single-process mode']);
                $this->run();
            }
            MailLog::info('Finish campaign `' . $this->name . '`');
            $this->log('Finish campaign', $this->customer, ['Finish campaign `' . $this->name . '`']);

            // runMultiProcesses() and run() are responsible for setting DONE status
        } catch (Exception $ex) {
            // Set ERROR status
            $this->error($ex->getMessage());
            MailLog::error('Starting campaign failed. ' . $ex->getMessage());
            $this->log('Starting campaign faile', $this->customer, ['Starting campaign failed. ' . $ex->getMessage()]);
        }
    }

    static function getTopSellingCampaigns(Customer $customer)
    {
        return self::where(AbsCampaign::COLUMN_customer_id, $customer->id)
            ->orderBy(AbsCampaign::COLUMN_sales_total, "desc")
            ->orderBy(AbsCampaign::COLUMN_total_number_of_sales, "desc")
            ->paginate();
    }

    /**
     * Start the campaign, using PHP fork() to launch multiple processes.
     *
     * @return mixed
     */
    public function runMultiProcesses()
    {
        // processes to fork
        $count = (int)$this->customer->getOption('max_process');
        $count = ($count > 2) ? 2 : $count; // @todo do not use more than 2 processes

        MailLog::info("Forking {$count} process(es)");
        $parentPid = getmypid();
        $children = [];
        for ($i = 0; $i < $count; $i += 1) {
            $pid = pcntl_fork();

            // for child process only
            if (!$pid) {
                // Reconnect to the DB to prevent connection closed issue when using fork
                DB::reconnect('mysql');

                // Re-initialize logging to capture the child process' PID
                MailLog::fork();

                MailLog::info(sprintf('Start child process %s of %s (forked from %s)', $i + 1, $count, $parentPid));
                sleep(self::WORKER_DELAY);
                $partition = [$i, $count];
                $this->run($partition);

                exit($i + 1);
                // end child process
            } else {
                $children[] = $pid;
            }
        }

        // wait for child processes to finish
        foreach ($children as $child) {
            $pid = pcntl_wait($status);
            if (pcntl_wifexited($status)) {
                $code = pcntl_wexitstatus($status);
                MailLog::info("Child process $pid finished, status code: $code");
            } else {
                MailLog::warning("Child process $pid did not normally exit");
                $this->error("Child process $pid did not normally exit");
            }
        }

        // after all child processes are done
        $this->refreshStatus();

        // If all child processes finish sucessfully, just mark campaign as done
        // Otherwise, mark campaign with error status (by child process)
        //
        // There are conventions here:
        //   + Child process does not update status from SENDING to DONE, it is left for the parent process
        //   + Child process only updates status from SENDING to ERROR
        //   + Parent process only updates status to DONE when current status is SENDING
        //     indicating that all child processes finish sucessfully
        //   + In case one child process update the status from SENDING to ERROR, it is left as the final status
        //     the latest error is kept
        if ($this->status == self::STATUS_SENDING) {
            $this->done();
        } else {
            // leave the latest error reported by child process
        }
    }

    /**
     * Send Campaign
     * Iterate through all subscribers and send email.
     */
    public function run($partition = null)
    {
        // check if the method is trigger by a child process (triggered by startMultiProcess method)
        $asChildProcess = !($partition == null);

        // try/catch to make sure child process does not stop without reporting any error
        try {
            $i = 0;
            $this->getPendingSubscribers($partition, function ($subscribers, $page, $total) use (&$i) {
                MailLog::info("Fetching page $page (count: {$subscribers->count()})");
                $this->log('Fetching page', $this->customer, ["Fetching page $page (count: {$subscribers->count()})"]);

                foreach ($subscribers as $subscriber) {
                    // @todo: customerneedcheck
                    $quota_check_count = 0;
                    while ($this->customer->overQuota()) {
                        $quota_check_count++;
                        if ($quota_check_count > 60)
                            throw new \Exception('Customer has reached sending limit');
                        MailLog::warning(sprintf('Campaign `%s` (%s) halted, user exceeds sending limit', $this->name, $this->uid));
                        $log_data = [
                            sprintf('Campaign `%s` (%s) halted, user exceeds sending limit', $this->name, $this->uid),
                            $this->customer->debugQuota()
                        ];
                        $this->log('user exceeds sending limit', $this->customer, $log_data);
                        sleep(60);
                    }

                    $i += 1;
                    MailLog::info("sending to subscriber `{$subscriber->email}` ({$i}/{$total})");
                    $this->log('sending to subscriber', $this->customer, ["sending to subscriber `{$subscriber->email}` ({$i}/{$total})"]);

                    // Pick up an available sending server
                    // Throw exception in case no server available
                    $server = $this->pickSendingServer();

                    MailLog::info("preparing email `{$subscriber->email}` ({$i}/{$total})");
                    $this->log('preparing email', $this->customer, ["preparing email `{$subscriber->email}` ({$i}/{$total})"]);
                    list($message, $msgId) = $this->prepareEmail($subscriber, $server);

                    // randomly check for campaign PAUSED status after 10 emails
                    // @todo performance concern here
                    // @todo need an exclusive lock, what if the campaign is PAUSED, then RESTARTED
                    //       after the status check and before actually sending?
                    if ($this->refreshStatus()->isPaused()) {
                        $this->updateCache();
                        throw new CampaignPausedException();
                    }

                    MailLog::info("sending email `{$subscriber->email}` ({$i}/{$total})");
                    $this->log('sending email', $this->customer, ["sending email `{$subscriber->email}` ({$i}/{$total})"]);
                    $sent = $server->send($message);

                    // additional log
                    MailLog::info("Sent to subscriber `{$subscriber->email}`\n\n");
                    $this->log('Sent to subscribe', $this->customer, ["Sent to subscriber `{$subscriber->email}`\n\n"]);

                    $this->trackMessage($sent, $subscriber, $server, $msgId);
                }
            });

            MailLog::info("{$i} emails sent for the process");

            // only mark campaign as done when running as its own process
            // as a child process, just finish and leave the parent process to update campaign status
            if (!$asChildProcess) {
                $this->done();
            }
        } catch (CampaignPausedException $e) {
            // just finish
            MailLog::warning('Campaign paused');
        } catch (CampaignErrorException $e) {
            // just finish
            MailLog::warning('Campaign terminated: ' . $e->getMessage());
        } catch (Exception $e) {
            MailLog::error($e->getMessage());
            $this->error($e->getMessage());
        } finally {
            // reset server pools: just in case DeliveryServerAmazonSesWebApi
            // --> setup SNS requires using from_email of the corresponding campaign
            // but SNS is only made once when the server is initiated
            // UPDATE: this is no longer the case as one process now only deals with one campaign
            //         however, the method is still needed to save sending servers''
            MailList::resetServerPools();

            // update the campaign cache information
            $this->updateCache();
        }
    }

    /**
     * Transform Tags
     * Transform tags to actual values before sending.
     */
    public function tagMessage($message, $subscriber, $msgId, $server = null)
    {
        // DEPRECATED
        if (!is_null($server) && $server->isElasticEmailServer()) {
            $message = $server->addUnsubscribeUrl($message);
        }

        $sig = $this->customer->signature;
        $shop = $this->customer->shopify_shop;

        $tags = array(
            'IN_WEBSITE' => $sig ? $sig->website : "",
            'IN_ADDRESS' => $this->customer->getContact()->getAddress(),
            'IN_BRANDNAME' => $this->customer->shopify_shop->name,
            'IN_EMAIL' => $this->customer->user->email,
            'IN_FIRSTNAME' => $this->customer->user->first_name,
            'IN_FULLNAME' => $sig ? $sig->full_name : "",
            'IN_POSITION' => $sig ? $sig->designation : "",
            'IN_PHONE' => $sig ? $sig->phone : "",
            'IN_FACEBOOK' => $sig ? $sig->facebook : "",
            'IN_INSTAGRAM' => $sig ? $sig->instagram : "",
            'IN_LINKEDIN' => $sig ? $sig->linkedin : "",
            'IN_TWITTER' => $sig ? $sig->twitter : "",
            'IN_SKYPE' => $sig ? $sig->skype : "",
            'IN_YOUTUBE' => $sig ? $sig->youtube : "",
            'IN_TIKTOK' => $sig ? $sig->tiktok : "",
            'IN_PINTEREST' => $sig ? $sig->pinterest : "",
            'IN_LOGO_URL' => $sig && $sig->logo_url ? $sig->logo_url : "/cb/assets/minimalist-blocks/images/logoplaceholder.png",
            'IN_THEME_FONT_FAMILY' => $shop->theme['font_family'] ?? 'Roboto',
            'IN_THEME_PRIMARY_BACKGROUND_COLOR' => $shop->theme['primary_background_color'] ?? '#000000ff',
            'IN_THEME_PRIMARY_TEXT_COLOR' => $shop->theme['primary_text_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_BACKGROUND_COLOR' => $shop->theme['secondary_background_color'] ?? '#ffffffff',
            'IN_THEME_SECONDARY_TEXT_COLOR' => $shop->theme['secondary_text_color'] ?? '#000000ff',
            'CAMPAIGN_NAME' => $this->name,
            'CAMPAIGN_UID' => $this->uid,
            'CAMPAIGN_SUBJECT' => $this->subject,
            'CAMPAIGN_FROM_EMAIL' => $this->from_email,
            'CAMPAIGN_FROM_NAME' => $this->from_name,
            'CAMPAIGN_REPLY_TO' => $this->reply_to,
            'SUBSCRIBER_UID' => $subscriber->uid,
            'CURRENT_YEAR' => date('Y'),
            'CURRENT_MONTH' => date('m'),
            'CURRENT_DAY' => date('d'),
            'CONTACT_NAME' => $this->defaultMailList->contact->company,
            'CONTACT_COUNTRY' => $this->defaultMailList->contact->countryName(),
            'CONTACT_STATE' => $this->defaultMailList->contact->state,
            'CONTACT_CITY' => $this->defaultMailList->contact->city,
            'CONTACT_ADDRESS_1' => $this->defaultMailList->contact->address_1,
            'CONTACT_ADDRESS_2' => $this->defaultMailList->contact->address_2,
            'CONTACT_PHONE' => $this->defaultMailList->contact->phone,
            'CONTACT_URL' => $this->defaultMailList->contact->url,
            'CONTACT_EMAIL' => $this->defaultMailList->contact->email,
            'LIST_NAME' => $this->defaultMailList->name,
            'LIST_SUBJECT' => $this->defaultMailList->default_subject,
            'LIST_FROM_NAME' => $this->defaultMailList->from_name,
            'LIST_FROM_EMAIL' => $this->defaultMailList->from_email,
        );

        # Subscriber specific
        if (!$this->isStdClassSubscriber($subscriber)) {
            $tags['UPDATE_PROFILE_URL'] = $this->generateUpdateProfileUrl($subscriber);
            $tags['UNSUBSCRIBE_URL'] = $this->generateUnsubscribeUrl($msgId, $subscriber);
            $tags['WEB_VIEW_URL'] = $this->generateWebViewerUrl($msgId);

            # Subscriber custom fields
            foreach ($this->defaultMailList->fields as $field) {
                $tags['SUBSCRIBER_' . $field->tag] = $subscriber->getValueByField($field);
            }
        } else {
            $tags['SUBSCRIBER_EMAIL'] = $subscriber->email;
        }

        // Actually transform the message
        foreach ($tags as $tag => $value) {
            $message = str_ireplace('{' . $tag . '}', $value, $message);
        }

        return $message;
    }

    /**
     * Subscribers.
     *
     * @return collect
     */
    public function subscribers($params = [], $extra = ['email_verifications'])
    {
        // get subscribers from auto campaign or normal campaign
        if ($this->isAuto()) {
            // Get subscriber from auto event -- deprecated
        } else {
            if ($this->listsSegments->isEmpty()) {
                // this is a trick for returning an empty builder
                return Subscriber::limit(0);
            }
            $query = Subscriber::query()->select('subscribers.*');
            // Tell MySQL to use the right index
            // $query->from(\DB::raw(\DB::getTablePrefix() . 'subscribers FORCE INDEX (subscribers_mail_list_id_email_index)'));
            // Email verification join
            if (in_array('email_verifications', $extra)) {
                $query = $query->leftJoin('email_verifications', 'email_verifications.subscriber_id', '=', 'subscribers.id');
            }
            // Get subscriber from mailist and segment
            $conditions = [];
            foreach ($this->listsSegments as $lists_segment) {
                if (!empty($lists_segment->segment_id)) {
                    // Segment
                    $conds = $lists_segment->segment->getSubscribersConditions();
                    if (!empty($conds['joins'])) {
                        foreach ($conds['joins'] as $joining) {
                            $query = $query->leftJoin($joining['table'], function ($join) use ($joining) {
                                $join->on($joining['ons'][0][0], '=', $joining['ons'][0][1]);
                                if (isset($joining['ons'][1])) {
                                    $join->on($joining['ons'][1][0], '=', $joining['ons'][1][1]);
                                }
                            });
                        }
                    }
                    // WHERE...
                    if (!empty($conds['conditions'])) {
                        $conditions[] = $conds['conditions'];
                    }
                } else if (!empty($lists_segment->segment2_id)) {
                    //$lists_segment->segment2->applyFilterGroups($query); this will take A LOT of time to process. Use the synced data as below
                    $query->where('subscribers.mail_list_id', $lists_segment->mail_list_id);
                    $query->whereHas('segment2s', function ($query) use ($lists_segment) {
                        $query->where('segment2s.id', $lists_segment->segment2_id);
                    });
                } else {
                    // Entire list
                    $conditions[] = '(' . table('subscribers.mail_list_id') . ' = ' . $lists_segment->mail_list_id . ')';
                }
            }

            if (!empty($conditions)) {
                $query = $query->whereRaw('(' . implode(' OR ', $conditions) . ')');
            }
        }

        // Filters
        $filters = isset($params['filters']) ? $params['filters'] : null;
        if ((isset($filters) && (isset($filters['open']) || isset($filters['click']) || isset($filters['tracking_status'])))
        ) {
            $query = $query->leftJoin('tracking_logs', 'tracking_logs.subscriber_id', '=', 'subscribers.id');
            $query = $query->whereNotNull('tracking_logs.id');
            $query = $query->where('tracking_logs.campaign_id', '=', $this->id);
        }

        if (isset($filters)) {
            if (isset($filters['open'])) {
                $equal = ($filters['open'] == 'opened') ? 'whereNotNull' : 'whereNull';
                $query = $query->leftJoin('open_logs', 'tracking_logs.message_id', '=', 'open_logs.message_id')
                    ->$equal('open_logs.id');
            }
            if (isset($filters['click'])) {
                $equal = ($filters['click'] == 'clicked') ? 'whereNotNull' : 'whereNull';
                $query = $query->leftJoin('click_logs', 'tracking_logs.message_id', '=', 'click_logs.message_id')
                    ->$equal('click_logs.id');
            }
            if (isset($filters['tracking_status'])) {
                $val = ($filters['tracking_status'] == 'not_sent') ? null : $filters['tracking_status'];
                $query = $query->where('tracking_logs.status', '=', $val);
            }
        }
        // keyword
        if (isset($params['keyword']) && !empty(trim($params['keyword']))) {
            foreach (explode(' ', trim($params['keyword'])) as $keyword) {
                $query = $query->leftJoin('subscriber_fields', 'subscribers.id', '=', 'subscriber_fields.subscriber_id');
                $query = $query->where(function ($q) use ($keyword) {
                    $q->orwhere('subscribers.email', 'like', '%' . $keyword . '%')
                        ->orWhere('subscriber_fields.value', 'like', '%' . $keyword . '%');
                });
            }
        }

        return $query;
    }

    /**
     * Get Pending Subscribers
     * Select only subscribers that are ready for sending. Those whose status is `blacklisted`, `pending` or `unconfirmed` are not included.
     */
    public function getPendingSubscribers($partition = null, $callback)
    {
        /* *
         *
         * Using LEFT JOIN is much slower than NOT IN
         *
        $subscribers = $this->subscribers()
                ->leftJoin(DB::raw('(SELECT * FROM '.DB::getTablePrefix().'tracking_logs WHERE campaign_id = '.$this->id.') log'), 'subscribers.id', '=', 'subscriber_id')
                ->whereRaw('log.id IS NULL')
                ->whereRaw(DB::getTablePrefix()."subscribers.status = '".Subscriber::STATUS_SUBSCRIBED."'")
                ->select('subscribers.*')
                ->get();
        */

        $builder = $this->subscribers([], ['email_verifications'])
            ->whereRaw(sprintf(table('subscribers') . '.email NOT IN (SELECT email FROM %s t JOIN %s s ON t.subscriber_id = s.id WHERE t.campaign_id = %s)', table('tracking_logs'), table('subscribers'), $this->id))
            ->whereRaw(DB::getTablePrefix() . "subscribers.status = '" . Subscriber::STATUS_SUBSCRIBED . "'");

        if (!is_null($partition)) {
            // retrieve the subscribers list for this partition only
            // partitioning is based on email to prevent one email from being taken by more than one process
            // The idea is to generate a unique integer for an email address, then partition base on the number
            list($partitionId, $count) = $partition;
            $builder = $builder->whereRaw(sprintf('CONV(SUBSTR(MD5(%s),1, 4), 16, 10) MOD %s = %s', table('subscribers.email'), $count, $partitionId));
        }

        $total = $builder->count(); // IMPORTNT: Count before group, otherwise it will always return 0 or 1
        $builder = $builder->groupBy(DB::raw(table('subscribers.email')));

        // the array_unique_by functions out-perform the Collection::unique of Laravel!!!
        // The following code snipet is no longer needed as the list is already unique by email
        //     $unique = array_unique_by($subscribers, function ($r) {
        //         return $r['email'];
        //     });

        $page = 1;
        $limit = 2000;
        while ($builder->count()) { // IMPORTANT: count() for a grouped query will always return 0 or 1
            // @note eager loading will result in a very long query
            // which could exceed the [max_allowed_packet] setting of MySQL
            // so the value of $limit must be considered carefully
            $subscribers = $builder->limit($limit)->with(['mailList', 'subscriberFields'])->get();
            $callback($subscribers, $page, $total);
            $page += 1;
        }

        /*
         * @todo the paginate helper does not work here since data table will be changed during the retrieving process
        paginate($subscribers, function ($subset, $page) use ($callback, $total) {
            $callback($subset, $page, $total);
        }, ['count' => $total]);
        */
    }

    /**
     * Queue campaign for sending.
     */
    public function queue($trigger = null, $subscribers = null, $delay = 0)
    {
        $this->log('queue started', $this->customer, ['sent to queue']);
        $this->ready();
        $job = (new \Acelle\Jobs\RunCampaignJob($this))->delay($this->getDelayInSeconds());
        dispatch($job);
        $this->log('queue eded', $this->customer, ['sent to queue']);
    }

    /**
     * Queue campaign for sending.
     */
    public function clearAllJobs()
    {
        // cleanup jobs and system_jobs
        // @todo data should be a JSON field instead
        $systemJobs = SystemJob::where('name', 'Acelle\Jobs\RunCampaignJob')->where('data', $this->id)->get();
        foreach ($systemJobs as $systemJob) {
            // @todo what if jobs were already started? check `reserved` field?
            $systemJob->clear();
        }
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function getBuilderTemplates($customer)
    {
        $result = [];

        // Gallery
        $templates = Template::where('customer_id', '=', null)
            ->orWhere('customer_id', '=', $customer->id)
            ->orderBy('customer_id')
            ->get();

        foreach ($templates as $template) {
            $result[] = [
                'name' => $template->name,
                'url' => action('CampaignController@templateChangeTemplate', ['uid' => $this->uid, 'change_uid' => $template->uid]),
                'thumbnail' => $template->getThumbUrl(),
            ];
        }

        return $result;
    }

    /**
     * Get builder templates.
     *
     * @return mixed
     */
    public function changeTemplate($template)
    {
        \Acelle\Library\Tool::xcopy($template->getStoragePath(), $this->getUploadDir());
        $this->html = $template->content;
        $this->image = $template->image;
        // replace image url
        $this->html = str_replace($template->getUploadUri(), $this->getUploadUri(), $this->html);
        $this->save();
    }

    function getContent(): string
    {
        if (str_starts_with($this->html, '<div class="container_bg"'))
            return $this->html;

        return '<div class="container_bg" style="background-color:#f5f5f5"><div class="container">' . $this->html . '</div></div>';
    }
}
