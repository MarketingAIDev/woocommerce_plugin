<?php

/**
 * Contact class.
 *
 * Model class for contacts
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

namespace Acelle\Model;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

/**
 * Class Contact
 * @package Acelle\Model
 *
 * @property integer id
 * @property string uid
 * @property string first_name
 * @property string last_name
 * @property string company
 * @property string address_1
 * @property string address_2
 * @property integer country_id
 * @property string state
 * @property string zip
 * @property string phone
 * @property string url
 * @property Carbon|string created_at
 * @property Carbon|string updated_at
 * @property string email
 * @property string city
 * @property string tax_number
 * @property string billing_address
 */
class Contact extends Model
{
    const COLUMN_id = "id";
    const COLUMN_uid = "uid";
    const COLUMN_first_name = "first_name";
    const COLUMN_last_name = "last_name";
    const COLUMN_company = "company";
    const COLUMN_address_1 = "address_1";
    const COLUMN_address_2 = "address_2";
    const COLUMN_country_id = "country_id";
    const COLUMN_state = "state";
    const COLUMN_zip = "zip";
    const COLUMN_phone = "phone";
    const COLUMN_url = "url";
    const COLUMN_created_at = "created_at";
    const COLUMN_updated_at = "updated_at";
    const COLUMN_email = "email";
    const COLUMN_city = "city";
    const COLUMN_tax_number = "tax_number";
    const COLUMN_billing_address = "billing_address";
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'email', 'first_name', 'last_name', 'address_1', 'address_2', 'city', 'zip', 'url', 'company', 'phone', 'state', 'country_id',
        'tax_number', 'billing_address',
    ];

    /**
     * The rules for validation.
     *
     * @var array
     */
    public static $rules = array(
        'email' => 'required|email',
        'first_name' => 'required',
        'last_name' => 'required',
        'address_1' => 'required',
        'city' => 'required',
        'state' => 'required',
        'zip' => 'required',
        'phone' => 'required',
        'url' => 'regex:/^https{0,1}:\/\//',
        //'url' => 'url', # do not use the default 'url' validator of Laravel, otherwise, error: preg_match(): Compilation failed: invalid range in character class at offset 20
        'company' => 'required',
        'country_id' => 'required',
    );

    /**
     * Bootstrap any application services.
     */
    public static function boot()
    {
        parent::boot();

        // Create uid when creating list.
        static::creating(function ($item) {
            // Create new uid
            $uid = uniqid();
            while (Contact::where('uid', '=', $uid)->count() > 0) {
                $uid = uniqid();
            }
            $item->uid = $uid;
        });
    }

    public function country()
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Display contact name.
     *
     * @var string
     */
    public function name(): string
    {
        return $this->first_name . ' ' . $this->last_name;
    }

    /**
     * Display contact country name.
     *
     * @var string
     */
    public function countryName()
    {
        return is_object($this->country) ? $this->country->name : '';
    }

    function getAddress($glue = ', '): string
    {
        return implode($glue, [$this->address_1, $this->address_2, $this->city, $this->state]);
    }
}
