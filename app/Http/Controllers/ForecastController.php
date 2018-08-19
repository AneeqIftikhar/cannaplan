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
                $company->selected_forecast=$forecast->id;
                $forecast->taxes()->create(['coorporate_tax'=>null , 'sales_tax'=>null]);
                $forecast->initialBalanceSettings()->create(['cash'=>null, 'accounts_receivable'=>null , 'inventory'=>null , 'long_term_assets'=>null , 'accumulated_depreciation'=>null , 'other_current_assets'=>null , 'accounts_payable'=>null, 'corporate_taxes_payable'=>null, 'sales_taxes_payable'=>null, 'prepaid_revenue'=>null, 'short_term_debt'=>null, 'long_term_debt'=>null, 'paid_in_capital'=>null]);
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

    public static function getBalanceSheetByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $balance_sheet=Forecast::getBalanceSheetByForecastId($id);
            return response()->success($balance_sheet,'Balance Sheet Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }
    }

    public static function getCashFlowByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $cash_flow=Forecast::getCashFlowByForecastId($id);
            return response()->success($cash_flow,'Cash Flow Fetched Successfully');
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
