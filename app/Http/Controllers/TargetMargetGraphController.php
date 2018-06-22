<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Pitch;
use Illuminate\Http\Request;
use CannaPlan\Models\TargetMarketGraph;
use CannaPlan\Http\Requests\TargetMargetGraphRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class TargetMargetGraphController extends Controller
{
    public function index()
    {
        //
    }

    public function store(TargetMargetGraphRequest $request)
    {
        $pitch=Pitch::find($request->input('pitch_id'));
        if($pitch && $pitch->created_by==Auth::user()->id){
            $input = $request->all();
            $target_marget_graph=$pitch->targetMargetGraphs()->create($input);
            if($target_marget_graph) {
                return response()->success($target_marget_graph,'Target Market Graph Created Successfully');
            }
            else{
                return response()->fail('Target Market Graph Could Not Be Added');
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
        if($target_marget_graph && $target_marget_graph->created_by==$user->id) {
            //$target_marget_graph = TargetMarketGraph::where('id', $id)->update($request->all());

            $target_marget_graph->update(Input::all());

            return response()->success($target_marget_graph,'Target Market Graph Updated Successfully');

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
        if($target_marget_graph && $target_marget_graph->created_by==$user->id) {
            $target_marget_graph = TargetMarketGraph::destroy($id);


            return response()->success([],'Target Market Graph Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
}
