<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\AssetRequest;
use CannaPlan\Models\Asset;
use CannaPlan\Models\Current;
use CannaPlan\Models\Forecast;
use CannaPlan\Models\LongTerm;
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
        
    }



    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(AssetRequest $request)
    {
        $input = $request->all();
        $forecast=Forecast::find($input['forecast_id']);
        if($forecast && $forecast->created_by==Auth::user()->id){
            $input = $request->all();
            $asset=new Asset();
            $asset->name=$input['name'];
            $asset->amount_type=$input['amount_type'];
            $asset->amount=$input['amount'];
            $asset->start_date=$input['start_date'];
            $asset->forecast_id=$forecast->id;
            if($input['asset_duration']=='current')
            {
                $current=Current::create(['month'=>$input['month']]);
                $current->asset_durations()->save($asset);
            }
            else if($input['asset_duration']=='long_term')
            {
                $array=[];
                $array['year']=$input['year'];
                $array['will_sell']=$input['will_sell'];
                if($array['will_sell']==true)
                {
                    $array['selling_amount']=$input['selling_amount'];
                    $array['selling_date']=$input['selling_date'];
                }

                $long_term=LongTerm::create($array);
                $long_term->asset_durations()->save($asset);
            }
            $asset->asset_duration;
            return response()->success($asset,'Asset Created Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }


    public function show($id)
    {
        $user=Auth::user();

        $asset = Asset::find($id);

        if($asset && $user->id==$asset->created_by) {
            $asset=Asset::getAssetByForecast($id);
            return response()->success($asset,'Asset Fetched Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
    public function getAssetByForecast($id)
    {
        $user=Auth::user();

        $forecast=Forecast::find($id);
        if($forecast && $forecast->created_by==$user->id)
        {
            $asset=Asset::getAssetByForecast($id);
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
        $input = $request->all();
        if($asset && $asset->created_by==$user->id) {
            if($input['asset_duration']==$asset->asset_duration_type)
            {
                $asset->update(Input::all());
                $asset->asset_duration()->update(Input::all());
            }
            else
            {
                $asset->asset_duration->delete();
                if($input['asset_duration']=='current')
                {
                    $current=Current::create(['month'=>$input['month']]);
                    $current->asset_durations()->save($asset);
                }
                else if($input['asset_duration']=='long_term')
                {
                    $array=[];
                    $array['year']=$input['year'];
                    $array['will_sell']=$input['will_sell'];
                    if($array['will_sell']==true)
                    {
                        $array['selling_amount']=$input['selling_amount'];
                        $array['selling_date']=$input['selling_date'];
                    }

                    $long_term=LongTerm::create($array);
                    $long_term->asset_durations()->save($asset);
                }
            }


            return response()->success([],'Asset Updated Successfully');

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
