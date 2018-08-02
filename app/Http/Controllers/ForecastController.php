<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\ForecastRequest;
use CannaPlan\Models\Company;
use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class ForecastController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ForecastRequest $request)
    {
        $user=Auth::user();
        $company=Company::find($request['company_id']);
        if($company && $user->id == $company->created_by) {
            $input = $request->all();
            $forecast=$company->forecasts()->create($input);
            if($forecast) {
                $forecast->taxes()->create(['coorporate_tax'=>null , 'sales_tax'=>null]);
                $forecast->save();
                return response()->success($forecast,'Forecast Created Successfully');
            }
            else{
                return response()->fail('Forecast Could Not Be Added');
            }
        }
        else{
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
        $user=Auth::user();

        $forecast = Forecast::find($id);

        if($forecast && $user->id==$forecast->created_by) {
            return response()->success($forecast,'Forecast Fetched Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }

    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateForecast(ForecastRequest $request, $id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id) {

            $forecast->update($request->all());

            return response()->success($forecast,'Forecast Updated Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }

    public static function getForecastByCompany($id)
    {
        $user=Auth::user();
        $company=Company::find($id);
        if($company && $company->created_by==$user->id) {
            $forecast=$company->forecasts;

            return response()->success($forecast,'Forecast Fetched Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }

    public static function getProfitLossByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $profit_loss=Forecast::getProfitLossByForecastId($id);
            return response()->success($profit_loss,'Profit And Loss Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }
    }

    public static function changeBurdenRate(Request $request, $id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);

        $validator = Validator::make($request->all(),  [
            'burden_rate' => 'numeric|min:0|required'

        ]);

        if ($validator->fails()) {
            return response()->fail($validator->errors());
        }

        if($forecast && $forecast->created_by==$user->id) {

            $forecast->burden_rate=$request->burden_rate;
            $forecast->save();
            return response()->success($forecast,'Forecast Fetched Successfully');
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
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id) {
            $forecast = Forecast::destroy($id);

            return response()->success([],'Forecast Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
