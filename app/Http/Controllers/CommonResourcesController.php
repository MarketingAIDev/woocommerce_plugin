<?php

namespace Acelle\Http\Controllers;

use Acelle\Library\Tool;
use Acelle\Model\Country;
use Illuminate\Http\JsonResponse;

class CommonResourcesController extends Controller
{
    public function getCSRFToken(): JsonResponse
    {
        return response()->json(['token' => csrf_token()]);
    }

    public function getCountries(): JsonResponse
    {
        return response()->json(['countries' => Country::getAll()]);
    }

    public function getTimezones(): JsonResponse
    {
        $timezones = Tool::allTimeZones();
        $arr = [];
        foreach ($timezones as $timezone) {
            $row = ['value' => $timezone['zone'], 'label' => $timezone['text']];
            $arr[] = $row;
        }

        return response()->json(['timezones' => $arr]);
    }
}