<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Milestone;
use CannaPlan\Models\Pitch;
use CannaPlan\Http\Requests\MilestoneRequest;
use Illuminate\Support\Facades\Auth;

class MilestoneController extends Controller
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
    public function store(MilestoneRequest $request)
    {
        if(Pitch::find($request->input('pitch_id'))){
            if(Pitch::is_user_pitch($request->input('pitch_id'))!=false) {
                $input = $request->all();
                $milestone = Milestone::create($input);
                return response()->success($milestone,'Milestone Created Successfully');
            }
            else{
                return response()->fail("No Pitch In Company With This Identifier");
            }
        }
        else{
            return response()->fail("Pitch Not Found");
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

        $milestone = Milestone::find($id);

        if($milestone &&$user->id==$milestone->created_by) {
            return response()->success($milestone,'Milestone Fetched Successfully');
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
    public function updateMilestone(MilestoneRequest $request, $id)
    {
        $user=Auth::user();
        $milestone=Milestone::find($id);
        if($milestone && $milestone->id==$user->id) {
            $milestone = Milestone::where('id', $id)->update($request->all());

            return response()->success($request->all(),'Milestone Updated Successfully');

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
        $milestone=Milestone::find($id);
        if($milestone && $milestone->id==$user->id) {
            $milestone = Milestone::destroy($id);

            return response()->success([],'Milestone Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
