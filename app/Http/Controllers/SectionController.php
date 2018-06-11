<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Section;
use CannaPlan\Http\Requests\SectionRequest;

class SectionController extends Controller
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
    public function store(SectionRequest $request)
    {
        $input = $request->all();
        $section = Section::create($input);
        return response()->success($section,'Section Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $section = Section::find($id);
        if($section) {
            return response()->success($section,'Section Fetched Successfully');
        }
        else{
            return response()->fail('Section Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateSection(SectionRequest $request, $id)
    {
        $section = Section::where('id', $id)->update($request->all());

        if($section){
            return response()->success($request->all(),'Section Updated Successfully');
        }
        else{
            return response()->fail('Section Not Found');
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
        $section = Section::destroy($id);

        if($section){
            return response()->success([],'Section Deleted Successfully');
        }
        else{
            return response()->fail('Section Not Found');
        }
    }
}
