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
            $revenue=new Revenue();
            $revenue->name=$input['name'];
            $revenue->forecast_id=$forecast->id;
            if(isset($input['revenue_type']) && $input['revenue_type']=="billable")
            {
                $billable=Billable::create(['hour'=>$input['hour'],'revenue_start_date'=>$input['revenue_start_date'],'hourly_rate'=>$input['hourly_rate']]);

                $billable->revenues()->save($revenue);

            }
            else if(isset($input['revenue_type']) && $input['revenue_type']=="unit_sale")
            {
                $unit_sale=UnitSale::create(['unit_sold'=>$input['unit_sold'],'revenue_start_date'=>$input['revenue_start_date'],'unit_price'=>$input['unit_price']]);
                $unit_sale->revenues()->save($revenue);
            }
            else if(isset($input['revenue_type']) && $input['revenue_type']=="revenue_only")
            {
                if($input['type']=="varying")
                {
                    $total=$input['amount_m_1']+$input['amount_m_2']+$input['amount_m_3']+$input['amount_m_4']+$input['amount_m_5']+$input['amount_m_6']
                        +$input['amount_m_7']+$input['amount_m_8']+$input['amount_m_9']+$input['amount_m_10']+$input['amount_m_11']+$input['amount_m_12'];
                    $array=array();
                        $array['type']=$input['type'];
                        $array['start_date']=$input['start_date'];
                        $array['amount_m_1']=$input['amount_m_1'];
                        $array['amount_m_2']=$input['amount_m_2'];
                        $array['amount_m_3']=$input['amount_m_3'];
                        $array['amount_m_4']=$input['amount_m_4'];
                        $array['amount_m_5']=$input['amount_m_5'];
                        $array['amount_m_6']=$input['amount_m_6'];
                        $array['amount_m_7']=$input['amount_m_7'];
                        $array['amount_m_8']=$input['amount_m_8'];
                        $array['amount_m_9']=$input['amount_m_9'];
                        $array['amount_m_10']=$input['amount_m_10'];
                        $array[ 'amount_m_11']=$input['amount_m_11'];
                        $array['amount_m_12']=$input['amount_m_12'];
                        $array[ 'amount_y_1']=$total;
                        $array['amount_y_2']=$total;
                        $array['amount_y_3']=$total;
                        $array['amount_y_4']=$total;
                        $array['amount_y_5']=$total;

                }
                else if($input['type']=="constant")
                {
                    $amount=$input['amount'];
                    $amount_duration=$input['amount_duration'];
                    $array=array();
                    if($amount_duration=="year")
                    {
                        $total=$amount;
                        for($i=1;$i<12;$i++)
                        {
                            $array['amount_m_'.$i]=floor(($amount/(13-$i)));
                            $amount=$amount-floor(($amount/(13-$i)));
                        }
                        $array['amount_m_12']=$amount;
                    }
                    else if($amount_duration=="month")
                    {
                        $total=$amount*12;
                        for($i=1;$i<13;$i++)
                        {
                            $array['amount_m_'.$i]=$amount;
                        }
                    }
                    $array['amount_y_1']=$total;
                    $array['amount_y_2']=$total;
                    $array['amount_y_3']=$total;
                    $array['amount_y_4']=$total;
                    $array['amount_y_5']=$total;
                    $array['type']=$input['type'];
                    $array['start_date']=$input['start_date'];



                }
                $revenue_only=RevenueOnly::create($array);
                $revenue_only->revenues()->save($revenue);


            }
            else
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
        $total_arr=array();
        for ($j = 1; $j < 13; $j++) {
            $total_arr['amount_m_' . $j] = 0;
        }
        for ($j = 1; $j < 6; $j++) {
            $total_arr['amount_y_' . $j] = 0;
        }
        if($forecast && $forecast->created_by==$user->id)
        {
            $forecast=$forecast->with(['company','revenues','revenues.revenuable'])->first();
            for ($i=0;$i<count($forecast->revenues);$i++)
            {
                if(isset($forecast->revenues[$i]->revenuable_type)) {
                    if ($forecast->revenues[$i]->revenuable_type !== 'revenue_only') {
                        $multiplyer = 1;
                        $multiplicand = 1;
                        if ($forecast->revenues[$i]->revenuable_type == 'unit_sale') {
                            $multiplyer = $forecast->revenues[$i]['revenuable']['unit_sold'];
                            $multiplicand = $forecast->revenues[$i]['revenuable']['unit_price'];
                        } else {
                            $multiplyer = $forecast->revenues[$i]['revenuable']['hour'];
                            $multiplicand = $forecast->revenues[$i]['revenuable']['hourly_rate'];
                        }
                        $forecast->revenues[$i]['revenuable']['amount_m_1'] = 250;
                        for ($j = 1; $j < 13; $j++) {
                            $forecast->revenues[$i]['revenuable']['amount_m_' . $j] = $multiplyer * $multiplicand;
                        }
                        $total = $multiplyer * $multiplicand * 12;
                        $forecast->revenues[$i]['revenuable']['amount_y_1'] = $total;
                        $forecast->revenues[$i]['revenuable']['amount_y_2'] = $total;
                        $forecast->revenues[$i]['revenuable']['amount_y_3'] = $total;
                        $forecast->revenues[$i]['revenuable']['amount_y_4'] = $total;
                        $forecast->revenues[$i]['revenuable']['amount_y_5'] = $total;
                    }
                    for ($j = 1; $j < 13; $j++) {
                        $total_arr['amount_m_' . $j] = $total_arr['amount_m_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_m_' . $j];
                    }
                    for ($j = 1; $j < 6; $j++) {
                        $total_arr['amount_y_' . $j] = $total_arr['amount_y_' . $j]+ $forecast->revenues[$i]['revenuable']['amount_y_' . $j];
                    }

                    $forecast['total'] = $total_arr;
                }

            }
            return response()->success($forecast,'Revenue Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }

    }


    public function updateRevenue(Request $request, $id)
    {
        $revenue = Revenue::find($id);
        if($revenue){
            $revenue->update($request->all());
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
