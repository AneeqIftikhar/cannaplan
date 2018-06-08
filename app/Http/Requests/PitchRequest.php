<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class PitchRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'company_name'=> 'max:150',
            'logo'=> 'image|mimes:jpg,png,jpeg|max:2048',
            'headline'=> 'max:255',
            'problem'=> 'max:255',
            'solution'=> 'max:255',
            'funds_required'=> 'max:255',
            'funds_usage_description'=> 'max:255',
            'sales_channel'=> 'max:255',
            'marketing_activities'=> 'max:255',
            'forecast_revenue'=> 'max:255',
            'forecast_cost'=> 'max:255',
            'forecast_type'=> 'max:20',

        ];
    }
}
