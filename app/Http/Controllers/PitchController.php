<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\PitchRequest;
use CannaPlan\Models\Pitch;
use Illuminate\Http\Request;
use CannaPlan\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

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

    //Pitch is created in company so we dont need it
//    public function store(PitchRequest $request)
//    {
//        $pitch=Pitch::create($request->all());
//        if($pitch) {
//            return response()->success($pitch,'Pitch Created Successfully');
//        }
//        else {
//            return response()->fail('Pitch Could Not be Created');
//        }
//
//    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $user=Auth::user();

        $pitch = Pitch::find($id);
        if($pitch && $user->id == $pitch->created_by) {
            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            return response()->success($pitch,'Pitch Fetched Successfully');

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
    public function updatePitch(PitchRequest $request, $id)
    {
        $user=Auth::user();

        $pitch = Pitch::find($id);
        if($pitch && $user->id == $pitch->created_by) {
            $input_array=$request->all();
            if ($request->hasFile('logo')) {
                Helper::deleteImage($pitch->logo);
                $input_array['logo']=Helper::uploadImage($request->logo);
            }
            //$pitch=Pitch::where('id', $id)->update($input_array);

            $pitch->update(Input::all());

            return response()->success($pitch,'Pitch Updated Successfully');
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
    // It is not needed
//    public function destroy($id)
//    {
//        $user=Auth::user();
//
//        $pitch = Pitch::find($id);
//        if($pitch && $user->id == $pitch->created_by) {
//            $pitch = Pitch::find($id);
//            $pitch->delete();
//            return response()->success([],'Pitch Deleted Successfully');
//        }
//        else{
//            return response()->fail('User Not Authorized');
//        }
//    }
}
