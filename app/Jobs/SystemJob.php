<?php

/**
 * SystemJob class.
 *
 * The abstract class for Laravel Jobs
 * All jobs created shall extend this
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

namespace Acelle\Jobs;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

use Acelle\Model\SystemJob as SystemJobModel;

class SystemJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels, Dispatchable;

    protected $systemJob;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($objectId = null, $objectName = null)
    {
        // init the SystemJob record to keep track of
        $systemJob = SystemJobModel::create(array(
            'status' => SystemJobModel::STATUS_NEW,
            'name' => get_called_class(),
            'object_id' => $objectId,
            'object_name' => $objectName,
        ));
        
        $this->systemJob = $systemJob;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //
    }

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function getSystemJob() {
        return SystemJobModel::find($this->systemJob->id);
        // @todo: what if job cannot be found?
    }

    /**
     * Cark the SystemJob record as RUNNING
     *
     * @return void
     */
    public function setStarted() {
        $systemJob = $this->getSystemJob();
        $systemJob->setStarted();
    }

    /**
     * Cark the SystemJob record as DONE
     *
     * @return void
     */
    public function setDone() {
        $systemJob = $this->getSystemJob();
        $systemJob->setDone();
    }

    /**
     * Mark the SystemJob record as FAILED
     *
     * @return void
     */
    public function setFailed($msg = null) {
        $systemJob = $this->getSystemJob();
        $systemJob->setFailed($msg);
    }
}
