<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TargetMarketGraphRequest extends FormRequest
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
            'pitch_id' => 'required',
            'segment_name' => 'required|max:50',
            'segment_prospect' => 'required|numeric|min:0',
            'prospect_cost' => 'required|numeric|min:0'
        ];
    }
}
