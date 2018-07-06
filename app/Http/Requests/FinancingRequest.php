<?php

namespace CannaPlan\Http\Requests;

use CannaPlan\Models\Forecast;
use Illuminate\Foundation\Http\FormRequest;

class FinancingRequest extends FormRequest
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

        if($this->request->has('fund_type') && $this->request->get('fund_type')=='loan')
        {
            $forecast_id=$this->request->get('forecast_id');
            $forecast=Forecast::where('id','=',$forecast_id)->first();
            $start_of_forecast=$forecast->company->start_of_forecast;

            $rules['receive_date'] = 'required';
            if($start_of_forecast>$this->request->get('receive_date'))//before start of plan
            {
                $rules['remaining_amount'] = 'required|numeric';
                $rules['interest_rate'] = 'required|numeric';
                $rules['amount'] = 'required|numeric';
            }
            else{//after start of plan
                $rules['amount'] = 'required|numeric';
                $rules['interest_rate'] = 'required|numeric';
                $rules['interest_months'] = 'required';
            }
        }
        else if($this->request->has('fund_type') && $this->request->get('fund_type')=='investment')
        {
            $rules['amount_type'] = 'required';
            if($this->request->get('amount_type')=='one_time')
            {
                $rules['amount'] = 'required|numeric';
                $rules['investment_start_date'] = 'required';
            }
            else if($this->request->get('amount_type')=='constant'){
                $rules['amount'] = 'required|numeric';
                $rules['payable_span'] = 'required';
                $rules['investment_start_date'] = 'required';
            }

        }
        else if($this->request->has('fund_type') && $this->request->get('fund_type')=='other')
        {
            $rules['annual_interest'] = 'required';
            $rules['is_payable'] = 'required';

            $rules['funding_amount_m_1']='required';
            $rules['funding_amount_m_2']='required';
            $rules['funding_amount_m_3']='required';
            $rules['funding_amount_m_4']='required';
            $rules['funding_amount_m_5']='required';
            $rules['funding_amount_m_6']='required';
            $rules['funding_amount_m_7']='required';
            $rules['funding_amount_m_8']='required';
            $rules['funding_amount_m_9']='required';
            $rules['funding_amount_m_10']='required';
            $rules['funding_amount_m_11']='required';
            $rules['funding_amount_m_12']='required';
            $rules['funding_amount_y_1']='required';
            $rules['funding_amount_y_2']='required';
            $rules['funding_amount_y_3']='required';
            $rules['funding_amount_y_4']='required';
            $rules['funding_amount_y_5']='required';

            $rules['payment_amount_m_1']='required';
            $rules['payment_amount_m_2']='required';
            $rules['payment_amount_m_3']='required';
            $rules['payment_amount_m_4']='required';
            $rules['payment_amount_m_5']='required';
            $rules['payment_amount_m_6']='required';
            $rules['payment_amount_m_7']='required';
            $rules['payment_amount_m_8']='required';
            $rules['payment_amount_m_9']='required';
            $rules['payment_amount_m_10']='required';
            $rules['payment_amount_m_11']='required';
            $rules['payment_amount_m_12']='required';
            $rules['payment_amount_y_1']='required';
            $rules['payment_amount_y_2']='required';
            $rules['payment_amount_y_3']='required';
            $rules['payment_amount_y_4']='required';
            $rules['payment_amount_y_5']='required';

        }
        else
        {
            $rules['fund_type'] = 'required';
        }
        return $rules;
    }
}
