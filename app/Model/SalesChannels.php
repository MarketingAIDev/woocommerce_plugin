<?php

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class SalesChannels
 * @package Acelle\Model
 *
 * @property integer id
 * @property string|Carbon created_at
 * @property string|Carbon updated_at
 * @property integer customer_id
 * @property Customer customer
 * @property double number_of_sales_from_chats
 * @property double number_of_sales_from_emails
 * @property double number_of_sales_from_popups
 * @property double sales_total_from_chats
 * @property double sales_total_from_emails
 * @property double sales_total_from_popups
 */
class SalesChannels extends Model
{
    const COLUMN_customer_id = 'customer_id';
    const COLUMN_number_of_sales_from_chats = 'number_of_sales_from_chats';
    const COLUMN_number_of_sales_from_emails = 'number_of_sales_from_emails';
    const COLUMN_number_of_sales_from_popups = 'number_of_sales_from_popups';
    const COLUMN_sales_total_from_chats = 'sales_total_from_chats';
    const COLUMN_sales_total_from_emails = 'sales_total_from_emails';
    const COLUMN_sales_total_from_popups = 'sales_total_from_popups';

    function customer()
    {
        return $this->belongsTo(Customer::class, self::COLUMN_customer_id);
    }

    static function createSalesChannel(Customer $customer): self
    {
        $model = new self();
        $model->number_of_sales_from_chats = 0;
        $model->number_of_sales_from_emails = 0;
        $model->number_of_sales_from_popups = 0;
        $model->sales_total_from_chats = 0;
        $model->sales_total_from_emails = 0;
        $model->sales_total_from_popups = 0;

        $customer->salesChannels()->save($model);
        return $model;
    }
}
