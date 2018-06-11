<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Topic;
use CannaPlan\Http\Requests\TopicRequest;

class TopicController extends Controller
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
    public function store(TopicRequest $request)
    {
        $input = $request->all();
        $topic = Topic::create($input);
        return response()->success($topic,'Topic Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $topic = Topic::find($id);
        if($topic) {
            return response()->success($topic,'Topic Fetched Successfully');
        }
        else{
            return response()->fail('Topic Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTopic(TopicRequest $request, $id)
    {
        $topic = Topic::where('id', $id)->update($request->all());

        if($topic){
            return response()->success($request->all(),'Topic Updated Successfully');
        }
        else{
            return response()->fail('Topic Not Found');
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
        $topic = Topic::destroy($id);

        if($topic){
            return response()->success([],'Topic Deleted Successfully');
        }
        else{
            return response()->fail('Topic Not Found');
        }
    }
}
