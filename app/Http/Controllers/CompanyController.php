<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Company;
use CannaPlan\Models\Pitch;
use Illuminate\Http\Request;
use CannaPlan\Http\Requests\CompanyRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
class CompanyController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $user = Auth::user();
        if($user) {
            return response()->success($user->companies,"Companies Fetched Successfull");
        }
        else{
            return response()->fail("User Not Authenticated");
        }
    }

    /**
     * Store a Company From API
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(CompanyRequest $request)
    {
        try
        {
            DB::beginTransaction();
            $input = $request->all();
            $company = Company::create($input);
            if($company)
            {
                //creating pitch with pitch company name as comapnys orignal company name
                $company->pitches()->create(['company_name'=>$request->input('title')]);
                //creating plan with dummy chapters,sections and topics/charts/tables

                DB::commit();
                return response()->success($company,'Company Created Successfully');
            }
            else
            {
                DB::rollback();
                return response()->fail('Company Could Not be Created');
            }
        }
        catch (\PDOException $ex) {
            DB::rollback();
            return response()->fail($ex->getMessage());
        }
        catch (\Exception $ex) {
            DB::rollback();
            return response()->fail($ex->getMessage());

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
        //Authorization to be added
        $company = Company::find($id);
        if($company){
            return response()->success($company,'Company Fetched Successfully');
        }
        else {
            return response()->fail("Company Not Found");
        }

    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(CompanyRequest $request, $id)
    {
        $company=Company::where('id', $id)->update($request->all());
        if($company) {
            return response()->success($request->all(),'Company Updated Successfully');
        }
        else {
            return response()->fail("Company Update Failed");
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
        $company=Company::destroy($id);
        if($company){
            return response()->success([],'Company Deleted Successfully');
        }
        else {
            return response()->fail("Company Deletion Failed");
        }

    }
}
