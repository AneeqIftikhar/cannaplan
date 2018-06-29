<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Forecast;
use Illuminate\Http\Request;
use CannaPlan\Http\Requests\ExpenseRequest;
use CannaPlan\Models\Expense;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

class ExpenseController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        //
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(ExpenseRequest $request)
    {
        $forecast=Forecast::find($request->input('forecast_id'));
        if($forecast && $forecast->created_by==Auth::user()->id){
            $input = $request->all();
            $expense=$forecast->expenses()->create($input);

            if($expense) {
                return response()->success($expense,'Expense Created Successfully');
            }
            else{
                return response()->fail('Expense Could Not Be Added');
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

        $expense = Expense::find($id);

        if($expense && $user->id==$expense->created_by) {
            return response()->success($expense,'Expense Fetched Successfully');
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
    public function updateExpense(ExpenseRequest $request, $id)
    {
        $user=Auth::user();
        $expense=Expense::find($id);
        if($expense && $expense->created_by==$user->id) {

            $expense->update(Input::all());

            return response()->success($expense,'Expense Updated Successfully');

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
        $expense=Expense::find($id);
        if($expense && $expense->created_by==$user->id) {
            $expense = Expense::destroy($id);

            return response()->success([],'Expense Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
