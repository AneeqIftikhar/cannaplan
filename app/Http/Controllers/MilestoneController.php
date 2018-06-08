<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Milestone;
use CannaPlan\Http\Requests\MilestoneRequest;

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
        $input = $request->all();
        $milestone = Milestone::create($input);
        return response()->success($milestone,'Milestone Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $milestone = Milestone::find($id);
        if($milestone) {
            return response()->success($milestone,'Milestone Fetched Successfully');
        }
        else{
            return response()->fail('Milestone Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(MilestoneRequest $request, $id)
    {
        $milestone = Milestone::where('id', $id)->update($request->all());
        if($milestone){
            return response()->success($request->all(),'Milestone Updated Successfully');
        }
        else{
            return response()->fail('Milestone Not Found');
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
        $milestone = Milestone::destroy($id);

        if($milestone){
            return response()->success([],'Milestone Deleted Successfully');
        }
        else{
            return response()->fail('Milestone Not Found');
        }
    }
}
