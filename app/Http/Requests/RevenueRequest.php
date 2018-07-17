<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;


class RevenueRequest extends FormRequest
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
        if($this->request->has('revenue_type') && $this->request->get('revenue_type')=='billable')
        {
            $rules['hour'] = 'required';
            $rules['revenue_start_date'] = 'required';
            $rules['hourly_rate'] = 'required';
        }
        else if($this->request->has('revenue_type') && $this->request->get('revenue_type')=='unit_sale')
        {
            $rules['unit_sold'] = 'required';
            $rules['revenue_start_date'] = 'required';
            $rules['unit_price'] = 'required';
        }
        else if($this->request->has('revenue_type') && $this->request->get('revenue_type')=='revenue_only')
        {
            if($this->request->has('type') && $this->request->get('type')=='varying')
            {
                $rules['amount_m_1'] = 'numeric';
                $rules['amount_m_2'] = 'numeric';
                $rules['amount_m_3'] = 'numeric';
                $rules['amount_m_4'] = 'numeric';
                $rules['amount_m_5'] = 'numeric';
                $rules['amount_m_6'] = 'numeric';
                $rules['amount_m_7'] = 'numeric';
                $rules['amount_m_8'] = 'numeric';
                $rules['amount_m_9'] = 'numeric';
                $rules['amount_m_10'] = 'numeric';
                $rules['amount_m_11'] = 'numeric';
                $rules['amount_m_12'] = 'numeric';

            }
            else if($this->request->has('type') && $this->request->get('type')=='constant')
            {
                $rules['amount'] = 'required';
                $rules['revenue_start_date'] = 'required';
                $rules['amount_duration'] = 'required';
            }
            else
            {
                $rules['type'] = 'required';
            }
        }
        return $rules;

    }
}
