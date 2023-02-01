<?php

use Acelle\Model\Contact;
use Acelle\Model\Customer;
use Illuminate\Database\Migrations\Migration;

class FixCustomerContactId extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        /** @var Customer[] $customers */
        $customers = Customer::query()->whereNull('contact_id')->get();
        foreach ($customers as $customer) {

            /** @var Contact $contact */
            $contact = Contact::query()->where(Contact::COLUMN_email, $customer->user->email)->first();
            if ($contact) {
                $customer->contact_id = $contact->id;
                $customer->save();
            }
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
    }
}
