<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\DividendRequest;
use CannaPlan\Models\Dividend;
use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class DividendController extends Controller
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
    public function store(DividendRequest $request)
    {
        $forecast=Forecast::find($request->input('forecast_id'));
        if($forecast && $forecast->created_by==Auth::user()->id){
            $input = $request->all();
            $dividend=$forecast->dividends()->create($input);

            if($dividend) {
                return response()->success($dividend,'Dividend Created Successfully');
            }
            else{
                return response()->fail('Dividend Could Not Be Added');
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

        $dividend = Dividend::find($id);

        if($dividend && $user->id==$dividend->created_by) {
            return response()->success($dividend,'Dividend Fetched Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }

    }

    public function getDividendByForecast($id)
    {
        $user=Auth::user();

        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $dividend=Dividend::getDividendByForecast($id);
            return response()->success($dividend,'Dividend Fetched Successfully');
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
    public function updateDividend(DividendRequest $request, $id)
    {
        $user=Auth::user();
        $dividend=Dividend::find($id);
        if($dividend && $dividend->created_by==$user->id) {

            $dividend->update(Input::all());

            return response()->success($dividend,'Dividend Updated Successfully');

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
        $dividend=Dividend::find($id);
        if($dividend && $dividend->created_by==$user->id) {
            $dividend = Dividend::destroy($id);

            return response()->success([],'Dividend Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
