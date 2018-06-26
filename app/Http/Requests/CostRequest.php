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
        $rules['name'] = 'required|max:100';
        if($this->request->has('charges_type') && $this->request->get('charges_type')=='direct')
        {
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
        }
        else if ($this->request->has('charges_type') && $this->request->get('charges_type')=='labor')
        {
            $rules['number_of_employees'] = 'required';
            $rules['labor_type'] = 'required';
            $rules['pay'] = 'required';
            $rules['start_date'] = 'required';
            $rules['staff_role_type'] = 'required|max:100';
        }
    }
}
