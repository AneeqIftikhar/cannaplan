<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Cost;
use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class CostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user=Auth::user();
        if($user) {
            $cost=Cost::where('created_by',$user->id);
            return response()->success($cost,"Cost Fetched Successfully");
        }
        else{
            return response()->fail("User Not Authenticated");
        }

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function addCostHelper($input,$cost)
    {
        if(isset($input['charges_type']) && $input['charges_type']=="direct")
        {
            if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="general_cost")
            {
                $general_cost=Cost::addGeneral($input['amount'] , $input['cost_start_date']);
                $general_cost->direct_costs()->save($cost);
                return true;
            }
            else if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="cost_on_revenue")
            {

            }
            //$direct=Cost::addDirect();
            //$direct->charges()->save($cost);
            return true;
        }
        else if(isset($input['charges_type']) && $input['charges_type']=="labor")
        {
            $labor=Cost::addLabor($input['number_of_employees'], $input['labor_type'], $input['pay'], $input['start_date'], $input['staff_role_type']);
            $labor->charges()->save($cost);
            return true;
        }
        else
        {
            return false;
        }
    }
    public function store(Request $request)
    {

        $user=Auth::user();
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==$user->id)
        {
            $cost=new Cost();
            $cost->name=$input['name'];
            $cost->forecast_id=$forecast->id;
            if(!$this->addCostHelper($input,$cost))
            {
                $cost->save();
            }
            return response()->success($cost,'Cost Created Successfully');

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
        $cost = Cost::find($id);
        $cost->charge;
        if($cost) {
            return response()->success($cost,'Cost Fetched Successfully');
        }
        else{
            return response()->fail('Cost Not Found');
        }
    }
    public function getCostByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $forecast=Cost::getCostByForecastId($id);
            return response()->success($forecast,'Cost Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }


    public function updateCost(Request $request, $id)
    {
        $input = $request->all();
        $cost = Cost::find($id);
        $user=Auth::user();
        if($cost && $cost->created_by==$user->id){
            //if user has a charge already set
            if($charge=$cost->charge)
            {
                //if the charge is same then it will be updated
                //else previous will be deleted and new will be inserted
                if(isset($input['charges_type']) && $cost->charge_type==$input['charges_type'])
                {
                    if ($input['charges_type'] == "billable") {
                        $billable = Cost::updateBillable($input['hour'], $input['cost_start_date'], $input['hourly_rate'],$charge);
                    } else if (isset($input['charges_type']) && $input['charges_type'] == "unit_sale") {
                        $unit_sale = Cost::updateUnitSale($input['unit_sold'], $input['cost_start_date'], $input['unit_price'],$charge);

                    } else if (isset($input['charges_type']) && $input['charges_type'] == "cost_only") {
                        if ($input['type'] == "varying") {
                            $array = array();
                            for ($i = 1; $i < 13; $i++) {
                                $array['amount_m_' . $i] = $input['amount_m_' . $i];
                            }
                            $cost_only = Cost::updateCostOnlyVarying($input['cost_start_date'], $array,$charge);
                        } else {
                            $cost_only = Cost::updateCostOnlyConstant($input['amount'], $input['amount_duration'], $input['cost_start_date'],$charge);
                        }

                    }
                }
                else
                {
                    //deleting previous charge
                    $charge->delete();
                    if(!$this->addCostHelper($input,$cost)) //adding new charge
                    {
                        if(isset($input['name']))
                        {
                            $cost->name=$input['name'];
                            $cost->save();
                        }
                    }
                }
            }
            else//charge was nerver set
            {
                //setting a new charge
                if(!$this->addCostHelper($input,$cost))
                {
                    if(isset($input['name']))
                    {
                        $cost->name=$input['name'];
                        $cost->save();
                    }
                }
            }
            $cost->charge;
            return response()->success($cost,'Cost Updated Successfully');
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
        $cost=Cost::find($id);
        $user=Auth::user();
        if($cost && $cost->created_by==$user->id){
            $cost->delete();
            return response()->success([],'Cost Deleted Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
