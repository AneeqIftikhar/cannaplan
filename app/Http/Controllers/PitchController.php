<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\PitchRequest;
use CannaPlan\Models\Pitch;
use Illuminate\Http\Request;
use CannaPlan\Helpers\Helper;
class PitchController extends Controller
{
    /**
     * Display a listing of the resource.
     * Commenting it as we might never use this functionality.
     *
//     */
//    public function index()
//    {
//    }

    /**
     * Store a newly created pitch in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(PitchRequest $request)
    {
        $pitch=Pitch::create($request->all());
        if($pitch) {
            return response()->success($pitch,'Pitch Created Successfully');
        }
        else {
            return response()->fail('Pitch Could Not be Created');
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
        $pitch = Pitch::find($id);
        if($pitch) {
            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            return response()->success($pitch,'Pitch Fetched Successfully');
        }
        else {
            return response()->fail('Pitch Could Not be Fetched');
        }

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updatePitch(PitchRequest $request, $id)
    {
        $input_array=$request->all();
        if ($request->hasFile('logo')) {
            $input_array['logo']=Helper::uploadImage($request->logo);
        }
        $pitch=Pitch::where('id', $id)->update($input_array);
        if($pitch) {
            return response()->success([],'Pitch Updated Successfully');
        }
        else{
            return response()->fail('Pitch Update Failed');
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
        $pitch = Pitch::find($id);
//        $pitch->competitors->delete();
//        $pitch->targetMarketGraphs->delete();
//        $pitch->teamRoles->delete();
        $pitch->delete();
        return response()->success([],'Pitch Deleted Successfully');
    }
}
