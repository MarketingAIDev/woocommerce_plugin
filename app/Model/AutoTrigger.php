<?php

/**
 * Automation Event Trigger class.
 *
 * Model class for logging triggered events
 *
 *
 * @category   MVC Model
 *

 * @copyright  Acelle Co., Ltd
 * @license    Acelle Co., Ltd
 *
 * @version    1.0
 *
 * @link       http://acellemail.com
 */

namespace Acelle\Model;

use Acelle\Library\Automation\Action;
use Carbon\Carbon;
use Closure;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Monolog\Logger;

/**
 * Class AutoTrigger
 * @package Acelle\Model
 * @property integer id
 * @property integer subscriber_id
 * @property Subscriber subscriber
 * @property array extra_data
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property string data
 * @property integer automation2_id
 * @property Automation2 automation2
 * @property string executed_index
 *
 * @property Timeline[] timelines
 */
class AutoTrigger extends Model
{
    protected $dates = ['created_at', 'updated_at'];

    protected $casts = [
        'extra_data' => 'array'
    ];

    public static function boot()
    {
        parent::boot();

        self::saving(function (self $model) {
            $model->updateExecutedIndex();
        });

        self::retrieved(function (self $model) {
            // IMPORTANT: in case of any change made to the parent automation2
            $model->updateWorkflow();
        });
    }

    public function subscriber(): BelongsTo
    {
        return $this->belongsTo(Subscriber::class);
    }

    public function automation2(): BelongsTo
    {
        return $this->belongsTo(Automation2::class);
    }

    public function timelines(): HasMany
    {
        return $this->hasMany(Timeline::class)->orderBy('created_at', 'DESC');
    }

    // getLatestAction from trigger
    public function getLatestAction()
    {
        $action = null;
        $this->getActions(function (Action $instance) use (&$action) {
            if ($instance->isLatest()) {
                $action = $instance;
            }
        });

        return $action;
    }

    // Mark the next action as "latest" and return it
    public function moveToNextAction()
    {
        $nextId = $this->getLatestAction()->getNextActionId();
        if (is_null($nextId)) {
            return;
        }

        $selected = null;
        $this->getActions(function ($instance) use ($nextId, &$selected) {
            if ($instance->getId() == $nextId) {
                $instance->markAsLatest(true);
                $selected = $instance;
            } else {
                $instance->markAsLatest(false);
            }
            $this->updateAction($instance);
        });

        return $selected;
    }

    public function recordToTimeline($action)
    {
        $this->timelines()->create([
            'automation2_id' => $this->automation2_id,
            'subscriber_id' => $this->subscriber_id,
            'activity' => $action->getActionDescription(),
            'activity_type' => $action->getType(),
        ]);
    }

    public function check()
    {
        DebugLog::automation2Log("trigger_update", $this->automation2->uid.' '.$this->automation2->name, []);
        $this->logger()->info(sprintf('UPDATE > Trigger %s for "%s" - "%s" Start checking', $this->id, $this->automation2->name, $this->subscriber->email));
        $last = $this->getLatestAction();

        if ($last == null) {
            $trigger = $this->getTrigger();
            $trigger->markAsLatest(true);
            $this->updateAction($trigger);
            $last = $trigger;
        }

        $this->logger()->info(sprintf('UPDATE > %s >Start checking "%s" from last action', $this->id, $last->getTitle()));

        // For example, if Send already executed but it is the last element
        if (is_null($last->getLastExecuted())) {
            $firstCheck = true;
        } else {
            $firstCheck = false;
        }

        // * Send => always true
        // * Evaluation => always true
        // * Trigger => always true
        // * Wait => true or false
        while ($last->execute() == true) { // true means: done with this step, should proceed next
            $this->updateAction($last); // update 'last_checked_at' field of Wait for example

            if ($firstCheck) {
                $this->recordToTimeline($last);
            }

            $last = $this->moveToNextAction();
            if ($last == null) {
                DebugLog::automation2Log("move_to_next", $this->automation2->uid.' '.$this->automation2->name, []);
                $this->logger()->info(sprintf('UPDATE > %s > End of flow, no more action to check %s %s', $this->id, $this->automation2->name, $this->subscriber->email));
                break;
            }

            if (is_null($last->getLastExecuted())) {
                $firstCheck = true;
            } else {
                $firstCheck = false;
            }

            DebugLog::automation2Log("move_to_next", $this->automation2->uid.' '.$this->automation2->name, []);
            $this->logger()->info(sprintf('UPDATE > %s > Checking "%s"', $this->id, $last->getTitle()));
        }

        if (!is_null($last)) {
            $this->updateAction($last); // update 'last_checked_at' field of Wait in case it returns false and jump here
            $this->logger()->info(sprintf('UPDATE > %s > Pending at "%s"', $this->id, $last->getTitle()));
        }
    }

    public function updateAction($action)
    {
        $json = $this->getJson();
        if (array_key_exists($action->getId(), $json)) {
            $current = $json[$action->getId()];
            $updated = array_merge($current, $action->toJson());
            $json[$action->getId()] = $updated;
        } else {
            $json[$action->getId()] = $action->toJson();
        }

        $this->data = json_encode($json);

        $this->save();
    }

    private function updateExecutedIndex()
    {
        $executed = [];
        $this->getActions(function ($action) use (&$executed) {
            if (!is_null($action->getLastExecuted())) {
                $executed[] = $action->getId();
            }
        });

        $this->executed_index = implode(',', $executed);
    }

    /**
     * @param Closure $callback
     */
    public function getActions($callback)
    {
        $json = $this->getJson();
        foreach ($json as $key => $attributes) {
            $attributes['id'] = $key;
            $instance = $this->getAction($attributes);
            $instance->setAutoTrigger($this);
            $callback($instance);
        }
    }

    public function getAction($attributes): Action
    {
        return $this->automation2->getAction($attributes);
    }

    public function updateWorkflow()
    {
        $origins = json_decode($this->automation2->data, true);
        $newJson = [];

        foreach ($origins as $origin) {
            $newAction = $this->getAction($origin);
            $currentAction = $this->getActionById($newAction->getId());

            if (is_null($currentAction)) {
                $newJson[$newAction->getId()] = $newAction->toJson();
            } else {
                $mergedJson = array_merge($currentAction->toJson(), $origin); // IMPORTANT: do not use newAction->toJson() --> null values will overwrite existing values
                $newJson[$newAction->getId()] = $mergedJson;
            }
        }
        $this->automation2->logger()->info('updateWorkflow');
        $this->data = json_encode($newJson);
        $this->save();
    }

    public function getJson()
    {
        return is_null($this->data) ? [] : json_decode($this->data, true);
    }

    // for debugging only
    public function getTrigger()
    {
        $trigger = null;
        $this->getActions(function ($e) use (&$trigger) {
            if ($e->getType() == 'ElementTrigger') {
                $trigger = $e;
            }
        });

        return  $trigger;
    }

    public function logger(): Logger
    {
        return $this->automation2->logger();
    }

    public function getActionById($id)
    {
        $selected = null;
        $this->getActions(function ($action) use ($id, &$selected) {
            if ($action->getId() == $id) {
                $selected = $action;
            }
        });

        return $selected;
    }

    public function isActionExecuted($id)
    {
        return !is_null($this->getActionById($id)->getLastExecuted());
    }

    public function isLatest($id)
    {
        return $this->getActionById($id)->isLatest();
    }

    public function isComplete()
    {
        $last = $this->getLatestAction();
        if ($last == null) {
            $last = $this->getTrigger();
        }
        $nextId = $last->getNextActionId();
        if (is_null($nextId)) {
            return true;
        } else {
            return false;
        }
    }
}
