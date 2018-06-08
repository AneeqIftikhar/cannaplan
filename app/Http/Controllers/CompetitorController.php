<?php

namespace CannaPlan\Http\Controllers;
use CannaPlan\Models\Competitor;
use CannaPlan\Http\Requests\CompetitorRequest;

use Illuminate\Http\Request;

class CompetitorController extends Controller
{
    public function index()
    {
        //
    }

    public function store(CompetitorRequest $request)
    {
        $input = $request->all();
        $competitor = Competitor::create($input);
        return response()->success($competitor,'Competitor Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $competitor = Competitor::find($id);
        if($competitor) {
            return response()->success($competitor,'Competitor Fetched Successfully');
        }
        else{
            return response()->fail('Competitor Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CompetitorRequest $request, $id)
    {
        $competitor = Competitor::where('id', $id)->update($request->all());
        if($competitor){
            return response()->success($request->all(),'Competitor Updated Successfully');
        }
        else{
            return response()->fail('Competitor Not Found');
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
        $competitor = Competitor::destroy($id);

        if($competitor){
            return response()->success([],'Competitor Deleted Successfully');
        }
        else{
            return response()->fail('Competitor Not Found');
        }
    }
}
