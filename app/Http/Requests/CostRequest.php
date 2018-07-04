<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CostRequest extends FormRequest
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
        $rules=[];
        if($this->request->has('charge_type') && $this->request->get('charge_type')=='direct')
        {
            $rules['name'] = 'required|max:100';
            if($this->request->has('direct_cost_type') && $this->request->get('direct_cost_type')=='general_cost')
            {
                $rules['amount'] = 'required';
                $rules['cost_start_date'] = 'required';
            }
            else if($this->request->has('direct_cost_type') && $this->request->get('direct_cost_type')=='cost_on_revenue')
            {
                $rules['revenue_id'] = 'required';
                $rules['amount'] = 'required';
            }
            else
            {
                $rules['direct_cost_type'] = 'required';
            }
        }
        else if ($this->request->has('charge_type') && $this->request->get('charge_type')=='labor')
        {
            $rules['name'] = 'required|max:100';
            $rules['number_of_employees'] = 'required';
            $rules['labor_type'] = 'required';
            $rules['pay'] = 'required';
            $rules['start_date'] = 'required';
            $rules['staff_role_type'] = 'required|max:100';
            $rules['annual_raise_percent'] = 'required';
        }
        else
        {
            $rules['charge_type'] = 'required';
        }
        return $rules;
    }
}
