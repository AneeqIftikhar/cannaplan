<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;

class ChartController extends Controller
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
    public function store(ChartRequest $request)
    {
        $input = $request->all();
        $chart = Chart::create($input);
        return response()->success($chart,'Chart Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chart = Chart::find($id);
        if($chart) {
            return response()->success($chart,'Chart Fetched Successfully');
        }
        else{
            return response()->fail('Chart Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateChart(ChartRequest $request, $id)
    {
        $chart = Chart::where('id', $id)->update($request->all());

        if($chart){
            return response()->success($request->all(),'Chart Updated Successfully');
        }
        else{
            return response()->fail('Chart Not Found');
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
        $chart = Chart::destroy($id);

        if($chart){
            return response()->success([],'Chart Deleted Successfully');
        }
        else{
            return response()->fail('Chart Not Found');
        }
    }
}
