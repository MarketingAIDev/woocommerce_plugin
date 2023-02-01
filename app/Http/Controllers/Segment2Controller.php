<?php

namespace Acelle\Http\Controllers;

use Acelle\Model\Customer;
use Acelle\Model\MailList;
use Acelle\Model\Segment2;
use Acelle\Model\Segment2ConditionAction;
use Acelle\Model\Segment2ConditionActionType;
use Acelle\Model\Segment2ConditionLocationType;
use Acelle\Model\Segment2ConditionProperty;
use Acelle\Model\Segment2ConditionPropertyType;
use Acelle\Model\ShopifyCustomer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;

class Segment2Controller extends Controller
{
    public function __construct()
    {
        $this->middleware('selected_customer');
        parent::__construct();
    }

    public function test()
    {
        $model = new Segment2();
        $query = ShopifyCustomer::where('id', '>', 1);
        $query->where(function ($query) {
            //$query->where('test', '>', 1);
            $query->orWhere('test', '>', 1);
            $query->orWhere('test', '>', 1);
            $query->orWhere('test', '>', 1);
            $query->orWhere('test', '>', 1);
        });
        //$query->where(Closure::fromCallable(array($model, 'filter')));

        $debug = vsprintf(str_replace(array('?'), array('\'%s\''), $query->toSql()), $query->getBindings());
        dd($debug);
    }

    public function selectBox(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;

        $list = MailList::findByUid($request->list_uid);
        $default_list = $customer->getDefaultMailingList();
        $options = [];

        if ($list->uid == $default_list->uid) {
            foreach ($customer->segment2s as $item)
                $options[] = ['value' => $item->uid, 'text' => $item->name];
        }

        return response()->json([
            'options' => $options,
            'index' => $request->index
        ]);
    }

    function customers(Request $request, Segment2 $segment2)
    {
        // Gate::authorize('view', $segment2);
        return response()->json([
            'segment2' => $segment2,
            'customers' => $segment2->subscribers()->paginate($request->per_page ?? 20)
        ]);
    }

    public function update(Request $request, Segment2 $segment2)
    {
        Gate::authorize('update', $segment2);

        DB::transaction(function () use ($request, $segment2) {
            $segment2->update_segment2($request->all());
        });

        return response()->json([
            'segment2' => $segment2->where('id', $segment2->id)->withAllConditions()->first()
        ]);
    }

    public function store(Request $request)
    {
        Gate::authorize('create', Segment2::class);

        /** @var Customer $customer */
        $customer = $request->selected_customer;

        $data = $request->validate(Segment2::store_segment2_rules());
        $segment2 = Segment2::store_segment2($customer, $data);

        return response()->json([
            'segment2' => $segment2->where('id', $segment2->id)->withAllConditions()->first()
        ]);
    }

    public function listing(Request $request)
    {
        /** @var Customer $customer */
        $customer = $request->selected_customer;
        $records = Segment2::where('customer_id', $customer->id)->paginate($request->per_page ?? 20);

        return response()->json([
            'segment2s' => $records
        ]);
    }

    public function view(Segment2 $segment2)
    {
        Gate::authorize('view', $segment2);
        return response()->json([
            'segment2' => $segment2->where('id', $segment2->id)->withAllConditions()->first()
        ]);
    }

    public function destroy(Segment2 $segment2)
    {
        Gate::authorize('delete', $segment2);
        $segment2->delete();
        response('', 201);
    }

    public function deleteMany(Request $request)
    {
        $items = Segment2::whereIn('uid', explode(',', $request->uids));
        $deleted_count = 0;

        foreach ($items->get() as $item) {
            // authorize
            if (Gate::allows('delete', $item)) {
                $item->delete();
                $deleted_count++;
            }
        }

        if ($request->wantsJson()) {
            return response()->json([
                'message' => $deleted_count . ' items deleted',
            ]);
        }
        echo "Segments deleted";
        return null;
    }

    public function condition_type_options()
    {
        return response()->json([
            'location_types' => Segment2ConditionLocationType::all(),
            'action_types' => Segment2ConditionActionType::all(),
            'action_count_types' => Segment2ConditionAction::COUNT_TYPES,
            'action_count_types__value_required_fields' => Segment2ConditionAction::COUNT_TYPES_VALUE_REQUIRED,
            'action_date_types' => Segment2ConditionAction::DATE_TYPES,
            'action_date_types__value_one_date_required_fields' => Segment2ConditionAction::DATE_TYPES_VALUE_1_DATE_REQUIRED,
            'action_date_types__value_two_date_required_fields' => Segment2ConditionAction::DATE_TYPES_VALUE_2_DATE_REQUIRED,
            'action_date_types__value_one_int_required_fields' => Segment2ConditionAction::DATE_TYPES_VALUE_1_INTEGER_REQUIRED,
            'action_date_types__value_two_int_required_fields' => Segment2ConditionAction::DATE_TYPES_VALUE_2_INTEGER_REQUIRED,
            'action_date_types__period_required_fields' => Segment2ConditionAction::DATE_TYPES_PERIOD_REQUIRED,
            'action_date_periods' => Segment2ConditionAction::DATE_PERIODS,
            'property_types' => Segment2ConditionPropertyType::all(),
            'property_text_comparison_types' => Segment2ConditionProperty::TEXT_COMPARISON_TYPES,
            'property_number_comparison_types' => Segment2ConditionProperty::NUMBER_COMPARISON_TYPES,
            'property_boolean_comparison_types' => Segment2ConditionProperty::BOOLEAN_COMPARISON_TYPES,
        ]);
    }

    public function property_comparison_options(Segment2ConditionPropertyType $propertyType)
    {
        $options = [];

        switch ($propertyType->data_type) {
            case Segment2ConditionPropertyType::DATA_TYPE_TEXT:
                $options = Segment2ConditionProperty::TEXT_COMPARISON_TYPES;
                break;
            case Segment2ConditionPropertyType::DATA_TYPE_DATE:
                $options = Segment2ConditionProperty::DATE_COMPARISON_TYPES;
                break;
            case Segment2ConditionPropertyType::DATA_TYPE_NUMBER:
                $options = Segment2ConditionProperty::NUMBER_COMPARISON_TYPES;
                break;
            case Segment2ConditionPropertyType::DATA_TYPE_BOOLEAN:
                $options = Segment2ConditionProperty::BOOLEAN_COMPARISON_TYPES;
                break;
        }

        return response()->json($options);
    }
}
