<?php

namespace CannaPlan\Http\Controllers;

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
    public function store(Request $request)
    {


        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast)
        {
            if($input['revenue_type']=="billable")
            {
                $billable=Billable::create(['hour'=>$input['hour'],'revenue_start_date'=>$input['revenue_start_date'],'hourly_rate'=>$input['hourly_rate']]);
                $revenue=new Revenue();
                $revenue->name=$input['name'];
                $revenue->forecast_id=$forecast->id;
                $billable->revenues()->save($revenue);

            }
            else if($input['revenue_type']=="unit_sale")
            {
                $unit_sale=UnitSale::create(['unit_sold'=>$input['unit_sold'],'revenue_start_date'=>$input['revenue_start_date'],'unit_price'=>$input['unit_price']]);
                $revenue=new Revenue();
                $revenue->name=$input['name'];
                $revenue->forecast_id=$forecast->id;
                $unit_sale->revenues()->save($revenue);
            }
            else if($input['revenue_type']=="revenue_only")
            {
                $revenue_only=RevenueOnly::create(['type'=>$input['type'],'start_date'=>$input['start_date'],
                    'amount_m_1'=>$input['amount_m_1']]);
                $revenue=new Revenue();
                $revenue->name=$input['name'];
                $revenue->forecast_id=$forecast->id;
                $revenue_only->revenues()->save($revenue);

            }
            return response()->success($revenue,'Revenue Created Successfully');

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
        if($revenue) {
            return response()->success($revenue,'Revenue Fetched Successfully');
        }
        else{
            return response()->fail('Revenue Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateRevenue(Request $request, $id)
    {
        $revenue = Revenue::where('id', $id)->update($request->all());

        if($revenue){
            return response()->success($request->all(),'Revenue Updated Successfully');
        }
        else{
            return response()->fail('Revenue Not Found');
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
        $revenue = Revenue::destroy($id);

        if($revenue){
            return response()->success([],'Revenue Deleted Successfully');
        }
        else{
            return response()->fail('Revenue Not Found');
        }
    }
}
