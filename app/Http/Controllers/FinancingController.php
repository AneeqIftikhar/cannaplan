<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Financing;
use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use CannaPlan\Http\Requests\FinancingRequest;
use Illuminate\Support\Facades\Auth;
use DateTime;
class FinancingController extends Controller
{
    private function addFinancingHelper($input,$fund ,$start_of_forecast)
    {
        if(isset($input['fund_type']) && $input['fund_type']=="loan")
        {
            $start_date=new DateTime($start_of_forecast);
            $receive_date=new DateTime($input['receive_date']);
            if(isset($input['receive_date']) && $receive_date<$start_date)//before start of plan
            {
                $loan=Financing::addLoan($input['receive_date'],$input['amount'],$input['interest_rate'],null, $input['remaining_amount']);
                $loan->funds()->save($fund);
                return true;
            }
            else{//after start of plan
                $loan=Financing::addLoan($input['receive_date'],$input['amount'],$input['interest_rate'],$input['interest_months'], null);
                $loan->funds()->save($fund);
                return true;
            }

        }
        else if(isset($input['fund_type']) && $input['fund_type']=="investment")
        {
            if(isset($input['amount_type']) && $input['amount_type']=='one_time')
            {
                $investment=Financing::addInvestment($input['amount_type'],$input['investment_start_date'],$input['amount'] , null);
                $investment->funds()->save($fund);
                return true;
            }
            else if(isset($input['amount_type']) && $input['amount_type']=='constant')
            {
                $investment=Financing::addInvestment($input['amount_type'],$input['investment_start_date'],$input['amount'] , $input['payable_span']);
                $investment->funds()->save($fund);
                return true;
            }

        }
        else if(isset($input['fund_type']) && $input['fund_type']=="other")
        {
            $other=Financing::addOther($input['annual_interest'] , $input['is_payable']);

            $array=array();
            $total=0;
            for($i=1 ; $i<13 ; $i++)
            {
                if(isset($input['funding_amount_m_'.$i]))
                {
                    $array['amount_m_'.$i]=$input['funding_amount_m_'.$i];
                    $total=$total+$array['amount_m_'.$i];
                }
                else
                {
                    $array['amount_m_'.$i]=null;
                }
            }
            for($i=1 ; $i<6 ; $i++)
            {
                if($i==1)
                {
                    $array['amount_y_'.$i]=$total;
                }
                else if(isset($input['funding_amount_y_'.$i]))
                {
                    $array['amount_y_'.$i]=$input['funding_amount_y_'.$i];
                }
                else
                {
                    $array['amount_y_'.$i]=null;
                }
            }
            $other=Financing::addFunding($array,$other);

            $array=array();
            $total=0;
            for($i=1 ; $i<13 ; $i++)
            {
                if(!isset($input['payment_amount_m_'.$i]))
                {
                    $array['amount_m_'.$i]=null;
                }
                else
                {
                    $array['amount_m_'.$i]=$input['payment_amount_m_'.$i];
                    $total=$total+$array['amount_m_'.$i];
                }
            }
            for($i=1 ; $i<6 ; $i++)
            {
                if($i==1)
                {
                    $array['amount_y_'.$i]=$total;
                }
                else if(!isset($input['payment_amount_y_'.$i]))
                {
                    $array['amount_y_'.$i]=null;
                }
                else
                {
                    $array['amount_y_'.$i]=$input['payment_amount_y_'.$i];
                }
            }
            $other=Financing::addPayment($array,$other);

            $other->funds()->save($fund);

            return true;
        }
        else
        {
            return false;
        }
    }

    public function store(FinancingRequest $request)
    {

        $user=Auth::user();
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==$user->id)
        {
            $fund=new Financing();
            $fund->name=$input['name'];
            $fund->forecast_id=$forecast->id;
            $start_of_forecast=$forecast->company->start_of_forecast;

            if($this->addFinancingHelper($input,$fund, $start_of_forecast))
            {
                $fund->save();
            }
            return response()->success($fund,'Financing Created Successfully');

        }
        else
        {
            return response()->fail('User Not Authorized');
        }


    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $fund = Financing::find($id);
        $fund->fundable;
        if($fund) {
            return response()->success($fund,'Financing Fetched Successfully');
        }
        else{
            return response()->fail('Financing Not Found');
        }
    }

    public function getFinancingByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $financing=Financing::getFinancingByForecastId($id);
            $financing['projected_cash_flow']=Financing::getProjectedCashFlow($id);
            return response()->success($financing,'Financing Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }

    public function updateFinancing(FinancingRequest $request, $id)
    {
        $input = $request->all();
        $fund = Financing::find($id);
        $user=Auth::user();
        $forecast=Forecast::find($input['forecast_id']);

        if($forecast && $fund && $fund->created_by==$user->id){
            //if user has a fundable already set

            $fundable=$fund->fundable;
            $start_of_forecast=$forecast->company->start_of_forecast;

            if(isset($input['fund_type']) && $fund->fundable_type==$input['fund_type'])
            {
                if(isset($input['name']))
                {
                    $fund->name=$input['name'];
                    $fund->save();
                }

                if (isset($input['fund_type']) && $input['fund_type'] == "loan") {

                    if(isset($input['receive_date']) && $input['receive_date']<$start_of_forecast)//before start of plan
                    {
                        $loan = Financing::updateLoan($input['receive_date'],$input['amount'],$input['interest_rate'],null, $input['remaining_amount'] , $fundable);
                    }
                    else{//after start of plan
                        $loan=Financing::updateLoan($input['receive_date'],$input['amount'],$input['interest_rate'],$input['interest_months'], null , $fundable);
                    }

                }
                else if (isset($input['fund_type']) && $input['fund_type'] == "investment") {
                    if(isset($input['amount_type']) && $input['amount_type']=='one_time')
                    {
                        $investment=Financing::updateInvestment($input['amount_type'],$input['investment_start_date'],$input['amount'] , null , $fundable);
                    }
                    else if(isset($input['amount_type']) && $input['amount_type']=='constant')
                    {
                        $investment=Financing::updateInvestment($input['amount_type'],$input['investment_start_date'],$input['amount'] , $input['payable_span'] , $fundable);
                    }

                }
                else if(isset($input['fund_type']) && $input['fund_type'] == "other") {
                    $other=Financing::updateOther($input['annual_interest'] , $input['is_payable'] , $fundable);

                    $array=array();
                    $total=0;
                    for($i=1 ; $i<13 ; $i++)
                    {
                        if(!isset($input['funding_amount_m_'.$i]))
                        {
                            $array['amount_m_'.$i]=null;
                        }
                        else
                        {
                            $array['amount_m_'.$i]=$input['funding_amount_m_'.$i];
                            $total=$total+$array['amount_m_'.$i];
                        }
                    }
                    for($i=1 ; $i<6 ; $i++)
                    {
                        if($i==1)
                        {
                            $array['amount_y_'.$i]=$total;
                        }
                        else if(!isset($input['funding_amount_y_'.$i]))
                        {
                            $array['amount_y_'.$i]=null;
                        }
                        else
                        {
                            $array['amount_y_'.$i]=$input['funding_amount_y_'.$i];
                        }
                    }
                    $other=Financing::updateFunding($array,$fundable);

                    $array=array();
                    $total=0;
                    for($i=1 ; $i<13 ; $i++)
                    {
                        if(!isset($input['payment_amount_m_'.$i]))
                        {
                            $array['amount_m_'.$i]=null;
                        }
                        else
                        {
                            $array['amount_m_'.$i]=$input['payment_amount_m_'.$i];
                            $total=$total+$array['amount_m_'.$i];
                        }
                    }
                    for($i=1 ; $i<6 ; $i++)
                    {
                        if($i==1)
                        {
                            $array['amount_y_'.$i]=$total;
                        }
                        else if(!isset($input['payment_amount_y_'.$i]))
                        {
                            $array['amount_y_'.$i]=null;
                        }
                        else
                        {
                            $array['amount_y_'.$i]=$input['payment_amount_y_'.$i];
                        }
                    }
                    $other=Financing::updatePayment($array,$fundable);

                }
            }
            else
            {
                //deleting previous finance
                $fundable->delete();

                if($this->addFinancingHelper($input,$fund ,$start_of_forecast)) //adding new fundable
                {
                    if(isset($input['name']))
                    {
                        $fund->name=$input['name'];
                        $fund->save();
                    }
                }
            }

            $fund->fundable;
            return response()->success($fund,'Financing Updated Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $fund=Financing::find($id);
        $user=Auth::user();
        if($fund && $fund->created_by==$user->id){
            $fund->delete();
            return response()->success([],'Financing Deleted Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
