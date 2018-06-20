<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Helpers\Helper;
use CannaPlan\Http\Requests\TeamRoleRequest;
use CannaPlan\Models\TeamRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class TeamRoleController extends Controller
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
    public function store(TeamRoleRequest $request)
    {
        $input_array=$request->all();
        if ($request->hasFile('image')) {
            $input_array['image']=Helper::uploadImage($request->image);
        }
        $team_role=TeamRole::create($input_array);
        if($team_role) {
            return response()->success($team_role,'Team Role Added Successfully');
        }
        else{
            return response()->fail('Team Could Not Be Added');
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
        $team_role = TeamRole::find($id);
        if($team_role) {
            return response()->success($team_role,'Team Role Fetched Successfully');
        }
        else {
            return response()->fail('Team Role Could Not be Fetched');
        }
    }



    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateTeamRole(TeamRoleRequest $request, $id)
    {
        $input_array=$request->all();
        if ($request->hasFile('image')) {
            $input_array['image']=Helper::uploadImage($request->image);
        }
        $team_role=TeamRole::where('id', $id)->update($input_array);
        if($team_role) {
            return response()->success([],'Team Role Updated Successfully');
        }
        else{
            return response()->fail('Team Role Update Failed');
        }
    }

    public function updateOrder(Request $request)
    {
        DB::beginTransaction();
        $ids = $request->id;
        $orders=$request->order;
        for($i=0 ; $i<count($ids) ; $i++)
        {
            if(TeamRole::where('id', $ids[$i])->first())
            {
                if($orders[$i]>count($ids) || $orders[$i]<1)
                {
                    DB::rollback();
                    return response()->fail('Order Number Is Not Correct');
                }
                else{
                    $team_roles[] = TeamRole::where('id', $ids[$i])->update(['order'=> $orders[$i]]);
                }
            }
            else{
                DB::rollback();
                return response()->fail('Could Not Find A Team Role');
            }

        }
        DB::commit();
        return response()->success([],'Team Roles Order Updated Successfully');

    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $team_role = TeamRole::destroy($id);
        if($team_role){
            return response()->success([],'Pitch Deleted Successfully');
        }
        else{
            return response()->fail ('Team Role Deletion Failed');
        }

    }
}
