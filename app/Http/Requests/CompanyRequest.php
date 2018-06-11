<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompanyRequest extends FormRequest
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
            'title' => 'required',
            'business_stage' => 'required',
            'start_of_forecast' => 'required',
            'length_of_forecast' => 'required',
            'monthly_detail' => 'required',
            'currency_id' => 'required',
        ];
    }
}
