<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Models\Plan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{

    //Plan is created inside company creation
//    public function store(Request $request)
//    {
//        //
//    }


    public function show($id)
    {
        $plan=Plan::find($id);
        $user=Auth::user();
        if($plan && $plan->created_by==$user->id)
        {

            $plan = Plan::where('id',$id)->with(['chapters','chapters.sections','chapters.sections.sectionContents','chapters.sections.sectionContents.content'])->first();

            return response()->success($plan,'Plan Fetched Successfully');
        }
        else
        {
            return response()->fail('User Not Authorized');
        }
    }


   //Nothing To update In Plan
//    public function update(Request $request, $id)
//    {
//        //
//    }

    //Plan Can not be deleted
//    public function destroy($id)
//    {
//        //
//    }
}
