<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\TargetMarketGraph;
use CannaPlan\Http\Requests\TargetMargetGraphRequest;

class TargetMargetGraphController extends Controller
{
    public function index()
    {
        //
    }

    public function store(TargetMargetGraphRequest $request)
    {
        if(Pitch::find($request->input('pitch_id'))){
            if(Pitch::is_user_pitch($request->input('pitch_id'))!=false) {
                $input = $request->all();
                $target_marget_graph = TargetMarketGraph::create($input);
                return response()->success($target_marget_graph,'Target Market Graph Created Successfully');
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

        $target_marget_graph = TargetMarketGraph::find($id);

        if($target_marget_graph && $user->id==$target_marget_graph->created_by) {
            return response()->success($target_marget_graph,'Target Market Graph Fetched Successfully');
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
    public function updateTargetMargetGraph(TargetMargetGraphRequest $request, $id)
    {
        $user=Auth::user();
        $target_marget_graph=TargetMarketGraph::find($id);
        if($target_marget_graph && $target_marget_graph->id==$user->id) {
            $target_marget_graph = TargetMarketGraph::where('id', $id)->update($request->all());

            return response()->success($request->all(),'Target Market Graph Updated Successfully');

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
        $target_marget_graph=TargetMarketGraph::find($id);
        if($target_marget_graph && $target_marget_graph->id==$user->id) {
            $target_marget_graph = TargetMarketGraph::destroy($id);


            return response()->success([],'Target Market Graph Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
