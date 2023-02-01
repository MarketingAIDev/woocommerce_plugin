<?php

namespace Acelle\Library\Automation;

use Acelle\Model\DebugLog;

class Trigger extends Action
{
    public function execute()
    {
        DebugLog::automation2Log("execute_trigger", $this->autoTrigger->automation2->uid . ' ' . $this->autoTrigger->automation2->name, []);
        $this->recordLastExecutedTime();
        $this->evaluationResult = true;

        return true;
    }

    public function getActionDescription()
    {
        $nameOrEmail = $this->autoTrigger->subscriber->getFullNameOrEmail();

        return sprintf('User %s subscribes to mail list, automation triggered!', $nameOrEmail);
    }
}
