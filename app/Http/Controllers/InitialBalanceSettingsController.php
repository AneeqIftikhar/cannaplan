<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Forecast;
use CannaPlan\Models\InitialBalanceSettings;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Validator;

class InitialBalanceSettingsController extends Controller
{
    public function getInitialBalanceSettingsByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {

           $initial_balance_settings=$forecast->initialBalanceSettings;
           $initial_balance_settings['company']=$forecast->company;

            return response()->success($initial_balance_settings,'Initial Balance Settings Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }
    }

    public function updateInitialBalanceSettings(Request $request, $id)
    {
        $initial_balance_settings = InitialBalanceSettings::find($id);

        $user=Auth::user();
        if($initial_balance_settings && $initial_balance_settings->created_by==$user->id){

            if($initial_balance_settings->forecast_id!=$request['forecast_id'])
            {
                return response()->fail('User Not Authorized');
            }
            $validator = Validator::make($request->all(),[
                'days_to_get_paid' => 'required',
                'depreciation_period' => 'required',
                'amortization_period' => 'required',
                'days_to_pay' => 'required',
            ]);

            if ($validator->fails()) {
                return response()->fail($validator->errors());
            }

            $initial_balance_settings->update(Input::all());
            return response()->success($initial_balance_settings,'Initial Balance Settings Updated Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
