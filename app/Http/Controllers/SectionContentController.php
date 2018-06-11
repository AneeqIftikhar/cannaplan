<?php

namespace CannaPlan\Http\Controllers;
use CannaPlan\Models\SectionContent;
use CannaPlan\Http\Requests\SectionContentRequest;

use Illuminate\Http\Request;

class SectionContentController extends Controller
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
    public function store(SectionContentRequest $request)
    {
        $input = $request->all();
        $section_content = SectionContent::create($input);
        return response()->success($section_content,'Section Content Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $section_content = SectionContent::find($id);
        if($section_content) {
            return response()->success($section_content,'Section Content Fetched Successfully');
        }
        else{
            return response()->fail('Section Content Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateSectionContent(SectionContentRequest $request, $id)
    {
        $section_content = SectionContent::where('id', $id)->update($request->all());

        if($section_content){
            return response()->success($request->all(),'Section Content Updated Successfully');
        }
        else{
            return response()->fail('Section Content Not Found');
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
        $section_content = SectionContent::destroy($id);

        if($section_content){
            return response()->success([],'Section Content Deleted Successfully');
        }
        else{
            return response()->fail('Section Content Not Found');
        }
    }
}
