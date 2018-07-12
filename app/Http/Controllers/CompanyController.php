<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Company;
use CannaPlan\Models\Forecast;
use CannaPlan\Models\Pitch;
use CannaPlan\Models\Plan;
use Illuminate\Http\Request;
use CannaPlan\Http\Requests\CompanyRequest;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;

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
            $user=Auth::user();
            if($user) {
                $company = $user->companies()->create($input);
                if($company) {
                    //creating pitch with pitch company name as company's orignal company name
                    $company->pitches()->create(['company_name'=>$request->input('title')]);
                    $plan=$company->plans()->create([]);
                    Plan::add_entries_in_plan_module($plan);
                    //creating plan with dummy chapters,sections and topics/charts/tables
                    $forecast=$company->forecasts()->create(['name'=>'Original Forecast','burden_rate'=>'20']);
                    $company->selected_forecast=$forecast->id;
                    $company->save();



                    DB::commit();
                    return response()->success($company,'Company Created Successfully');
                }
                else {
                    DB::rollback();
                    return response()->fail('Company Could Not be Created');
                }
            }
            else {
                return response()->fail("User Not Authorized");
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
        if(Company::is_user_company($id)!==false) {
            $company = Company::where('id' , '=' , $id)->with('currency')->first();
            if ($company) {
                return response()->success($company, 'Company Fetched Successfully');
            } else {
                return response()->fail("No Company In User Profile With This Identifier");
            }
        }
        else{
            return response()->fail("User Not Authorized");
        }

    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateCompany(CompanyRequest $request, $id)
    {
        if(Company::is_user_company($id)!==false)
        {
            $company = Company::find($id);
            if($company) {
                $company->update(Input::all());
                return response()->success($company,'Company Updated Successfully');
            }
            else {
                return response()->fail("Company Update Failed");
            }
        }
        else
        {
            return response()->fail("User Not Authorized");
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
        if(Company::is_user_company($id)!=false)
        {
            $company=Company::destroy($id);
            if($company){
                return response()->success([],'Company Deleted Successfully');
            }
            else {
                return response()->fail("Company Deletion Failed");
            }
        }
        else
        {
            return response()->fail("User Not Authorized");
        }


    }
}
