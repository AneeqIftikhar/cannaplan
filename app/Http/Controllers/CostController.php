<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\CostRequest;
use CannaPlan\Models\Cost;
use CannaPlan\Models\Direct;
use CannaPlan\Models\Forecast;
use CannaPlan\Models\Revenue;
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

    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    private function addCostHelper($input,$cost)
    {
        if(isset($input['charge_type']) && $input['charge_type']=="direct")
        {
            $direct=new Direct();
            $direct->name=$input['name'];
            if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="general_cost")
            {
                $general_cost=Cost::addGeneral($input['amount'] , $input['cost_start_date']);
                $general_cost->direct_costs()->save($direct);
                $direct->charges()->save($cost);
                return true;
            }
            else if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="cost_on_revenue")
            {
                $cost_on_revenue=Cost::addCostOnRevenue($input['revenue_id'], $input['amount']);
                $cost_on_revenue->direct_costs()->save($direct);
                $direct->charges()->save($cost);
                return true;
            }

        }
        else if(isset($input['charge_type']) && $input['charge_type']=="labor")
        {
            $labor=Cost::addLabor($input['name'], $input['number_of_employees'], $input['labor_type'], $input['pay'], $input['start_date'], $input['staff_role_type'], $input['annual_raise_percent']);
            $labor->charges()->save($cost);
            return true;
        }
        else
        {
            return false;
        }
    }
    public function store(CostRequest $request)
    {
        $user=Auth::user();
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==$user->id)
        {
            $cost=new Cost();
            $cost->forecast_id=$forecast->id;

            if($this->addCostHelper($input,$cost))
            {
                $cost->charge;
                return response()->success($cost,'Cost Created Successfully');
            }
            else{
                return response()->fail('Something went wrong');
            }
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
        $cost = Cost::where('id','=',$id)->with('charge')->first();

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
            $cost=Cost::getCostByForecastId($id);
            return response()->success($cost,'Cost Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }

    public function getPersonnelByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $cost=Cost::getPersonnelByForecastId($id);
            return response()->success($cost,'Cost Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }

    public function updateCost(CostRequest $request, $id)
    {
        $input = $request->all();
        $cost = Cost::find($id);

        $user=Auth::user();
        if($cost && $cost->created_by==$user->id){
            $charge=$cost->charge;

            if(isset($input['charge_type']) && $cost->charge_type==$input['charge_type'])//updating the cost
            {
                if($cost->charge_type=='direct')//updating direct
                {
                    if(isset($input['direct_cost_type']) && $cost->charge->direct_cost_type==$input['direct_cost_type'])
                    {//if direct cost is same

                        $cost->charge->name=$input['name'];
                        $charge->save();

                        $charge=$charge->direct_cost;

                        if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="general_cost")
                        {
                            Cost::updateGeneral($input['amount'] , $input['cost_start_date'] , $charge);
                        }
                        else if(isset($input['direct_cost_type']) && $input['direct_cost_type']=="cost_on_revenue")
                        {
                            Cost::updateCostOnRevenue($input['revenue_id'], $input['amount'] , $charge);
                        }
                    }
                    else{// if direct cost is changed then delete previous direct cost

                        $charge->direct_cost->delete();
                        $charge->delete();
                        if($this->addCostHelper($input,$cost)) //adding new cost
                        {
                            if(isset($input['name']))
                            {
                                $cost->charge->name=$input['name'];
                                $cost->save();
                                $cost->charge->direct_cost;
                            }
                        }
                    }
                }
                else{//labor will be updated here
                    Cost::updateLabor($input['name'], $input['number_of_employees'], $input['labor_type'], $input['pay'], $input['start_date'], $input['staff_role_type'], $input['annual_raise_percent'] ,$charge);
                }
            }
            else{//if cost is changed
                if($cost->charge_type=='direct')
                {
                    $charge->direct_cost->delete();
                }
                $charge->delete();


                if($this->addCostHelper($input,$cost)) //adding new cost

                $cost->charge_type=$input['charge_type'];

                if($this->addCostHelper($input,$cost)) //adding new cost

                {
                    if(isset($input['name']))
                    {
                        $cost->charge->name=$input['name'];
                        $cost->save();
                        $cost->charge->direct_cost;
                    }
                    else{
                        $cost->save();
                        $cost->charge;
                    }

                }
            }
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
