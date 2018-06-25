<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Asset;
use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class AssetController extends Controller
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
    public function store(Request $request)
    {
        $forecast=Forecast::find($request->input('forecast_id'));
        if($forecast && $forecast->created_by==Auth::user()->id){
            $input = $request->all();
            $asset=$forecast->assets()->create($input);

            if($asset) {
                return response()->success($asset,'Asset Created Successfully');
            }
            else{
                return response()->fail('Asset Could Not Be Added');
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

        $asset = Asset::find($id);

        if($asset && $user->id==$asset->created_by) {
            return response()->success($asset,'Asset Fetched Successfully');
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
    public function updateAsset(Request $request, $id)
    {
        $user=Auth::user();
        $asset=Asset::find($id);
        if($asset && $asset->created_by==$user->id) {

            $asset->update(Input::all());

            return response()->success($asset,'Asset Updated Successfully');

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
        $asset=Asset::find($id);
        if($asset && $asset->created_by==$user->id) {
            $asset = Asset::destroy($id);

            return response()->success([],'Asset Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
