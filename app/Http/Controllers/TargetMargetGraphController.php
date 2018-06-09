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
        $input = $request->all();
        $target_marget_graph = TargetMarketGraph::create($input);
        return response()->success($target_marget_graph,'Target Market Graph Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $target_marget_graph = TargetMarketGraph::find($id);
        if($target_marget_graph) {
            return response()->success($target_marget_graph,'Target Market Graph Fetched Successfully');
        }
        else{
            return response()->fail('Target Market Graph Not Found');
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
        $target_marget_graph = TargetMarketGraph::where('id', $id)->update($request->all());
        if($target_marget_graph){
            return response()->success($request->all(),'Target Market Graph Updated Successfully');
        }
        else{
            return response()->fail('Target Market Graph Not Found');
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
        $target_marget_graph = TargetMarketGraph::destroy($id);

        if($target_marget_graph){
            return response()->success([],'Target Market Graph Deleted Successfully');
        }
        else{
            return response()->fail('Target Market Graph Not Found');
        }
    }
}
