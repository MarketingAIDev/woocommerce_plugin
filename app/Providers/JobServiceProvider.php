<?php

namespace Acelle\Providers;

use Illuminate\Support\Facades\Queue;
use Illuminate\Support\ServiceProvider;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessing;
use Acelle\Library\Log as MailLog;

class JobServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     */
    public function boot()
    {
        // Initialize the MailLog which writes logs to mail.log
        $this->initMailLog();

        // mark the SystemJob record as RUNNING
        Queue::before(function (JobProcessing $event) {
            $job = $this->getJobObject($event);

            // associate the queue job with system_job
            // @todo IMPORTANT job_id is only available when the job has actually started
            $systemJob = $job->getSystemJob();
            $systemJob->job_id = $event->job->getJobId();
            $systemJob->save();

            $job->setStarted();
        });

        // mark the SystemJob record as DONE
        Queue::after(function (JobProcessed $event) {
            $job = $this->getJobObject($event);
            $job->setDone();
        });

        // mark the SystemJob record as FAILED
        Queue::failing(function (JobFailed $event) {
            $error = $event->exception->getMessage();
            $error .= "\r\n".$event->exception->getTraceAsString();
            $job = $this->getJobObject($event); // Job object, not SystemJob model
            $job->setFailed($error);
        });
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        //
    }

    /**
     * Register the application services.
     */
    private function getJobObject($event)
    {
        $data = $event->job->payload();

        return unserialize($data['data']['command']);
    }

    /**
     * Init the MailLog.
     */
    private function initMailLog()
    {
        MailLog::configure(storage_path() . '/logs/' . php_sapi_name() . '/mail.log');
    }
}
