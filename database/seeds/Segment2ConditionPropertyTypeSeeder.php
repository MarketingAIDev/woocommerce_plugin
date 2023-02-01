<?php

use Acelle\Model\Segment2ConditionPropertyType;
use Illuminate\Database\Seeder;

class Segment2ConditionPropertyTypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        Segment2ConditionPropertyType::truncate();
        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_ACCEPTS_MARKETING;
        $m->name = 'Accepts Marketing';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_BOOLEAN;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_ACCEPTS_MARKETING_UPDATE_DATE;
        $m->name = 'Accepts Marketing Last Updated';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_DATE;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_CURRENCY;
        $m->name = 'Currency';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_JOINING_DATE;
        $m->name = 'Joining Date';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_DATE;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_EMAIL_ADDRESS;
        $m->name = 'Email Address';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_FIRST_NAME;
        $m->name = 'First Name';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_LAST_NAME;
        $m->name = 'Last Name';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_NOTE;
        $m->name = 'Note';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_NUMBER_OF_ORDERS;
        $m->name = 'Number Of Orders';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_NUMBER;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_PHONE;
        $m->name = 'Phone Number';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_ACCOUNT_STATE;
        $m->name = 'Account State';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_TEXT;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_TOTAL_SPENT;
        $m->name = 'Total Amount Spent';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_NUMBER;
        $m->save();

        $m = new Segment2ConditionPropertyType();
        $m->code = Segment2ConditionPropertyType::CODE_VERIFIED_EMAIL;
        $m->name = 'Has verified email';
        $m->data_type = Segment2ConditionPropertyType::DATA_TYPE_BOOLEAN;
        $m->save();

    }
}
