<?php

use Illuminate\Database\Seeder;

class Segment2Seeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $this->call(Segment2ConditionActionTypeSeeder::class);
        $this->call(Segment2ConditionPropertyTypeSeeder::class);
        $this->call(Segment2ConditionLocationTypeSeeder::class);
    }
}
