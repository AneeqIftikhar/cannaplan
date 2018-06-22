<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Milestone;
use CannaPlan\Models\Pitch;
use CannaPlan\Http\Requests\MilestoneRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

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
        $pitch=Pitch::find($request->input('pitch_id'));
        if($pitch && $pitch->created_by==Auth::user()->id){
            $input = $request->all();
            $milestone=$pitch->milestones()->create($input);

            if($milestone) {
                return response()->success($milestone,'Milestone Created Successfully');
            }
            else{
                return response()->fail('Milestone Could Not Be Added');
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

        $milestone = Milestone::find($id);

        if($milestone && $user->id==$milestone->created_by) {
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
            //$milestone = Milestone::where('id', $id)->update($request->all());

            $milestone->update(Input::all());

            return response()->success($milestone,'Milestone Updated Successfully');

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
        if($milestone && $milestone->created_by==$user->id) {
            $milestone = Milestone::destroy($id);

            return response()->success([],'Milestone Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
