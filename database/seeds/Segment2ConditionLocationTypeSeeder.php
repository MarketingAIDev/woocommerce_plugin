<?php

use Acelle\Model\Segment2ConditionLocationType;
use Illuminate\Database\Seeder;

class Segment2ConditionLocationTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Segment2ConditionLocationType::truncate();
        $m = new Segment2ConditionLocationType();
        $m->code = Segment2ConditionLocationType::CODE_INSIDE_EU;
        $m->name = 'Inside European Union';
        $m->save();

        $m = new Segment2ConditionLocationType();
        $m->code = Segment2ConditionLocationType::CODE_OUTSIDE_EU;
        $m->name = 'Outside European Union';
        $m->save();

        $m = new Segment2ConditionLocationType();
        $m->code = Segment2ConditionLocationType::CODE_INSIDE_US;
        $m->name = 'Inside USA';
        $m->save();

        $m = new Segment2ConditionLocationType();
        $m->code = Segment2ConditionLocationType::CODE_OUTSIDE_US;
        $m->name = 'Outside USA';
        $m->save();
    }
}
