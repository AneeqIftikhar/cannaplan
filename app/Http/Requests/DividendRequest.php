<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DividendRequest extends FormRequest
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
            'forecast_id' => 'required',
            'name' => 'required|max:255',
            'amount_type' => 'required|max:100',
            'amount' => 'required',
            'start_date' => 'required',
        ];
    }
}
