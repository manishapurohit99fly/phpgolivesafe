<?php

namespace App\Http\Controllers\api\v1;

use App\Models\Countries;
use Illuminate\Http\Request;

class CountriesController extends BaseApiController
{
    public function getActiveCountries()
    {
        $countries = Countries::active()->get();

        if ($countries->isEmpty()) {
            return $this->sendError(__('messages.no_countries_found'), 400);
        }
        return $this->sendResponse($countries, __('messages.country_list_retrieved'));
    }
}
