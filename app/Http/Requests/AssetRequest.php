<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class AssetRequest extends FormRequest
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
        $rules = [];
        $rules['name'] = 'required|max:100';
        $rules['forecast_id'] = 'required';
        $rules['amount_type'] = 'required';
        $rules['amount'] = 'required';
        $rules['start_date'] = 'required';
        $rules['asset_duration'] = 'required';
        if($this->request->has('asset_duration') && $this->request->get('asset_duration')=='current')
        {
            $rules['month'] = 'required';
        }
        else if($this->request->has('asset_duration') && $this->request->get('asset_duration')=='long_term')
        {
            $rules['year'] = 'required';
            $rules['will_sell'] = 'required';
            if($this->request->has('will_sell') && $this->request->get('will_sell')==true)
            {
                $rules['selling_amount'] = 'required';
                $rules['selling_date'] = 'required';
            }

        }
        return $rules;
    }
}
