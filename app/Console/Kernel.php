<?php

namespace Acelle\Console;

use Acelle\Helpers\ShopifyHelper;
use Acelle\Model\Automation2;
use Acelle\Model\Campaign;
use Acelle\Model\ChatSession;
use Acelle\Model\MonthlyCharge;
use Acelle\Model\Segment2;
use Acelle\Model\ShopifyProduct;
use Acelle\Model\ShopifyRecurringApplicationCharge;
use Acelle\Model\ShopifyShop;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        // Commands\Inspire::class,
        /* no longer needed as of Laravel 5.5
        Commands\TestCampaign::class,
        Commands\UpgradeTranslation::class,
        Commands\RunHandler::class,
        Commands\ImportList::class,
        Commands\VerifySender::class,
        Commands\SystemCleanup::class,
        Commands\GeoIpCheck::class,
        TinkerCommand::class,
        */
    ];

    /**
     * Define the application's command schedule.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    protected function schedule(Schedule $schedule)
    {
        // Log last execution time
        // Move the event into a schedule::call to prevent it from triggering every time "php artisan" command is executed
        $schedule->call(function () {
            event(new \Acelle\Events\CronJobExecuted());
        })->name('cronjob_event:log')->everyMinute();

        // Shopify shop init
        $schedule->call(function () {
            ShopifyHelper::initShops();
        })->name('shopify_shops:init_shops')->everyMinute();

        // Shopify shop sync
        $schedule->call(function () {
            ShopifyHelper::syncDiscountCodesAndWebhooks();
        })->name('shopify_shops:syncDiscountCodesAndWebhooks')->cron('0 */3 * * *');

        // Segment2s
        $schedule->call(function () {
            Segment2::syncAll();
        })->name('segment2:sync')->everyMinute();

        // Trigger product review fetch
        $schedule->call(function () {
            ShopifyProduct::trigger_review_fetch();
        })->name('shopify_product:review_fetch')->everyMinute();

        // Bounce/feedback handler
        $schedule->command('handler:run')->everyThirtyMinutes();

        // Sender verifying
        $schedule->command('sender:verify')->everyFiveMinutes();

        // System clean up
        $schedule->command('system:cleanup')->daily();

        // GeoIp database check
        $schedule->command('geoip:check')->everyMinute()->withoutOverlapping();

        // Create monthly charges
        $schedule->call(function () {
            /** @var ShopifyRecurringApplicationCharge[] $active_charges */
            $active_charges = ShopifyRecurringApplicationCharge::findActiveCharges();
            foreach ($active_charges as $active_charge) {
                if ($active_charge->isStillActive())
                    MonthlyCharge::createMonthlyCharges($active_charge);
            }
        })->name('monthly:charges:create')->monthlyOn(1, '8:30');

        // Bill monthly charges
        $schedule->call(function () {
            /** @var MonthlyCharge[] $pending_charges */
            $pending_charges = MonthlyCharge::findChargesToBill();
            foreach ($pending_charges as $charge) {
                $charge->billUsageCharge();
            }
        })->name('monthly:charges:bill')->dailyAt("6:45");

        // Send monthly charges to affiliates
        $schedule->call(function () {
            /** @var MonthlyCharge[] $pending_charges */
            $pending_charges = MonthlyCharge::findChargesToSendToAffiliates();
            foreach ($pending_charges as $charge) {
                $charge->sendToAffiliate();
            }
        })->name('monthly:charges:affiliate')->dailyAt("7:45");

        $schedule->call(function () {
            Campaign::updateCaches();
        })->name('campaign:caches')->everyMinute();

        // Run campaigns and automations at the end of cron jobs to make sure that
        // any other job which is supposed to make changes (update a list, a segment etc)
        // is done before automations/campaigns are processed

        // Queued import/export/campaign
        $schedule->command('queue:work --once --tries=3')->everyMinute();

        // Automation
        $schedule->call(function () {
            Automation2::run();
        })->name('automation:run')->everyMinute();

        // Admin Automations
        $schedule->call(function () {
            ChatSession::checkForUnattendedChats();
        })->name('admin:automations')->everyMinute();

        // Admin weekly Automations
        $schedule->call(function () {
            ShopifyShop::triggerWeeklyAutomations();
        })->name('admin:weekly_automations')->weekly();
    }

    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
