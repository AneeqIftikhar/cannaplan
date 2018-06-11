<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Http\Requests\ChapterRequest;
use CannaPlan\Models\Chapter;

class ChapterController extends Controller
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
    public function store(ChapterRequest $request)
    {
        $input = $request->all();
        $chapter = Chapter::create($input);
        return response()->success($chapter,'Chapter Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $chapter = Chapter::find($id);
        if($chapter) {
            return response()->success($chapter,'Chapter Fetched Successfully');
        }
        else{
            return response()->fail('Chapter Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateChapter(ChapterRequest $request, $id)
    {
        $chapter = Chapter::where('id', $id)->update($request->all());

        if($chapter){
            return response()->success($request->all(),'Chapter Updated Successfully');
        }
        else{
            return response()->fail('Chapter Not Found');
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
        $chapter = Chapter::destroy($id);

        if($chapter){
            return response()->success([],'Chapter Deleted Successfully');
        }
        else{
            return response()->fail('Chapter Not Found');
        }
    }
}
