<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\RevenueRequest;
use CannaPlan\Models\Forecast;
use CannaPlan\Models\RevenueOnly;
use CannaPlan\Models\UnitSale;
use Illuminate\Http\Request;
use CannaPlan\Models\Revenue;
use CannaPlan\Models\Billable;
use Illuminate\Support\Facades\Auth;

class RevenueController extends Controller
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
            $revenue=Revenue::where('created_by',$user->id);
            return response()->success($revenue,"Revenue Streams Fetched Successfully");
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
    private function addRevenueHelper($input,$revenue)
    {
        if(isset($input['revenue_type']) && $input['revenue_type']=="billable")
        {
            $billable=Revenue::addBillable($input['hour'],$input['revenue_start_date'],$input['hourly_rate']);
            $billable->revenues()->save($revenue);
            return true;
        }
        else if(isset($input['revenue_type']) && $input['revenue_type']=="unit_sale")
        {
            $unit_sale=Revenue::addUnitSale($input['unit_sold'],$input['revenue_start_date'],$input['unit_price']);
            $unit_sale->revenues()->save($revenue);
            return true;
        }
        else if(isset($input['revenue_type']) && $input['revenue_type']=="revenue_only")
        {
            if($input['type']=="varying")
            {
                $array=array();
                for($i=1;$i<13;$i++)
                {
                    $array['amount_m_'.$i]=$input['amount_m_'.$i];
                }
                $revenue_only=Revenue::addRevenueOnlyVarying($input['revenue_start_date'],$array);
                $revenue_only->revenues()->save($revenue);
            }
            else
            {
                $revenue_only=Revenue::addRevenueOnlyConstant($input['amount'],$input['amount_duration'],$input['revenue_start_date']);
                $revenue_only->revenues()->save($revenue);
            }
            return true;
        }
        else
        {
            return false;
        }
    }
    public function store(RevenueRequest $request)
    {

        $user=Auth::user();
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==$user->id)
        {
            $revenue=new Revenue();
            $revenue->name=$input['name'];
            $revenue->forecast_id=$forecast->id;
            if($this->addRevenueHelper($input,$revenue))
            {
                $revenue->save();
            }
            return response()->success($revenue,'Revenue Created Successfully');

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
        $revenue = Revenue::find($id);
        $revenue->revenuable;
        if($revenue) {
            return response()->success($revenue,'Revenue Fetched Successfully');
        }
        else{
            return response()->fail('Revenue Not Found');
        }
    }
    public function getRevenueByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $forecast=Revenue::getRevenueByForecastId($id);
            return response()->success($forecast,'Revenue Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }


    public function updateRevenue(RevenueRequest $request, $id)
    {
        $input = $request->all();
        $revenue = Revenue::find($id);
        $user=Auth::user();
        if($revenue && $revenue->created_by==$user->id){
            //if user has a revenuable already set
            if($revenuable=$revenue->revenuable)
            {
                if(isset($input['name']))
                {
                    $revenue->name=$input['name'];
                    $revenue->save();
                }
                //if the revenuable is same then it will be updated
                //else previous will be deleted and new will be inserted
                if(isset($input['revenue_type']) && $revenue->revenuable_type==$input['revenue_type'])
                {
                    if ($input['revenue_type'] == "billable") {
                        $billable = Revenue::updateBillable($input['hour'], $input['revenue_start_date'], $input['hourly_rate'],$revenuable);
                    } else if (isset($input['revenue_type']) && $input['revenue_type'] == "unit_sale") {
                        $unit_sale = Revenue::updateUnitSale($input['unit_sold'], $input['revenue_start_date'], $input['unit_price'],$revenuable);

                    } else if (isset($input['revenue_type']) && $input['revenue_type'] == "revenue_only") {
                        if ($input['type'] == "varying") {
                            $array = array();
                            for ($i = 1; $i < 13; $i++) {
                                $array['amount_m_' . $i] = $input['amount_m_' . $i];
                            }
                            $revenue_only = Revenue::updateRevenueOnlyVarying($input['revenue_start_date'], $array,$revenuable);
                        } else {
                            $revenue_only = Revenue::updateRevenueOnlyConstant($input['amount'], $input['amount_duration'], $input['revenue_start_date'],$revenuable);
                        }

                    }
                }
                else
                {
                    //deleting previous revenuable
                    $revenuable->delete();
                    if($this->addRevenueHelper($input,$revenue)) //adding new revenuable
                    {
                        if(isset($input['name']))
                        {
                            $revenue->name=$input['name'];
                            $revenue->save();
                        }
                    }
                }
            }
            else//revenuable was nerver set
            {
                //setting a new revenuable
                if($this->addRevenueHelper($input,$revenue))
                {
                    if(isset($input['name']))
                    {
                        $revenue->name=$input['name'];
                        $revenue->save();
                    }
                }
            }
            $revenue->revenuable;
            return response()->success($revenue,'Revenue Updated Successfully');
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
        $revenue=Revenue::find($id);
        $user=Auth::user();
        if($revenue && $revenue->created_by==$user->id){
            $revenue->delete();
            return response()->success([],'Revenue Deleted Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
