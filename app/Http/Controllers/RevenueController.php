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

        $user=Auth::user();
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==$user->id)
        {
            $revenue=Revenue::addRevenue($input);
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


    public function updateRevenue(Request $request, $id)
    {
        $input = $request->all();
        $revenue = Revenue::find($id);
        $user=Auth::user();
        if($revenue && $revenue->created_by==$user->id){
            $revenue=Revenue::updateRevenue($input,$id);
            $revenue->revenuable;
            return response()->success($revenue,'Revenue Updated Successfully');
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
