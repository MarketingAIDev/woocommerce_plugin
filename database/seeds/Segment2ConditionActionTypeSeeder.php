<?php

use Acelle\Model\Segment2ConditionActionType;
use Illuminate\Database\Seeder;

class Segment2ConditionActionTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Segment2ConditionActionType::truncate();
        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_PLACED_ORDER;
        $m->name = 'Placed order';
        $m->save();

        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_ABANDONED_CART;
        $m->name = 'Abandoned cart';
        $m->save();

        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_SUBMITTED_REVIEW;
        $m->name = 'Submitted review';
        $m->save();

        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_CHATTED;
        $m->name = 'Chatted';
        $m->save();

        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_ORDER_FULFILLED;
        $m->name = 'Order fulfilled';
        $m->save();

        $m = new Segment2ConditionActionType();
        $m->code = Segment2ConditionActionType::CODE_ORDER_DELIVERED;
        $m->name = 'Order delivered';
        $m->save();
    }
}
