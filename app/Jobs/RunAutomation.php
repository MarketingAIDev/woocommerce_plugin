<?php

namespace Acelle\Jobs;

use Acelle\Model\Automation2;
use Acelle\Model\DebugLog;
use Exception;

class RunAutomation extends SystemJob
{
    /** @var Automation2 */
    protected $automation;

    public function __construct(Automation2 $automation)
    {
        $this->automation = $automation;
        if (!$this->automation->allowApiCall()) {
            throw new Exception(sprintf('Automation "%s" is not set up to be triggered via API. Cannot start!', $this->automation->name));
        }
        parent::__construct();
        DebugLog::automation2Log("run", $this->automation->uid.' '.$this->automation->name, []);
    }


    public function handle()
    {
        if (!$this->automation->allowApiCall()) {
            $this->automation->logger()->info(sprintf('Automation "%s" is not set up to be triggered via API (job handle)', $this->automation->name));
            throw new Exception("Automation is not set up to be triggered via API (job handle)");
        }
        $this->automation->logger()->info(sprintf('Actually run automation "%s" in response to API call', $this->automation->name));
        $this->automation->execute();
        DebugLog::automation2Log("execute", $this->automation->uid.' '.$this->automation->name, []);
    }
}
