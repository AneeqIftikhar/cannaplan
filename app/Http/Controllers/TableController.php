<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Models\Table;
use CannaPlan\Http\Requests\TableRequest;

class TableController extends Controller
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
    public function store(TableRequest $request)
    {
        $input = $request->all();
        $table = Table::create($input);
        return response()->success($table,'Table Created Successfully');
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        $table = Table::find($id);
        if($table) {
            return response()->success($table,'Table Fetched Successfully');
        }
        else{
            return response()->fail('Table Not Found');
        }
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTable(TableRequest $request, $id)
    {
        $table = Table::where('id', $id)->update($request->all());

        if($table){
            return response()->success($request->all(),'Table Updated Successfully');
        }
        else{
            return response()->fail('Table Not Found');
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
        $table = Table::destroy($id);

        if($table){
            return response()->success([],'Table Deleted Successfully');
        }
        else{
            return response()->fail('Table Not Found');
        }
    }
}
