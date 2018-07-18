<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Forecast;
use CannaPlan\Models\Tax;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TaxController extends Controller
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

    public function getTaxByForecast($id)
    {
        $user=Auth::user();
        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $tax=Tax::getTaxByForecastId($id);
            return response()->success($tax,'Tax Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }
    }

    public function updateTax(Request $request, $id)
    {
        $tax = Tax::find($id);

        $user=Auth::user();
        if($tax && $tax->created_by==$user->id){

            if(isset($request['coorporate_tax']) || isset($request['sales_tax']))
            {
                $tax->is_started=true;
                $tax->update(['coorporate_tax'=>$request['coorporate_tax'] , 'sales_tax'=>$request['sales_tax']]);
                if(isset($request->revenue_id))
                {
                    $tax->revenues()->attach($request->revenue_id);
                }
            }
            else if($request['coorporate_tax']==null && $request['sales_tax']==null)
            {
                $tax->is_started=false;
                $tax->update(['coorporate_tax'=>$request['coorporate_tax'] , 'sales_tax'=>$request['sales_tax']]);
                foreach ($tax->revenueTaxes()->get() as $revenueTaxes) {
                    $revenueTaxes->delete();
                }
            }
            return response()->success($tax,'Tax Updated Successfully');

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
        $tax=Tax::find($id);
        $user=Auth::user();
        if($tax && $tax->created_by==$user->id){
            $tax->delete();
            return response()->success([],'Tax Deleted Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
