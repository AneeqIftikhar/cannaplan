<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Helpers\Helper;
use CannaPlan\Http\Requests\TeamRoleRequest;
use CannaPlan\Models\Pitch;
use CannaPlan\Models\TeamRole;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Input;

class TeamRoleController extends Controller
{



    public function store(TeamRoleRequest $request)
    {
        $pitch=Pitch::find($request->input('pitch_id'));
        if($pitch && $pitch->created_by==Auth::user()->id){
            $input_array=$request->all();
            if ($request->hasFile('image')) {
                $input_array['image']=Helper::uploadImage($request->image);
            }
            $team_role=$pitch->teamRoles()->create($input_array);
            if($team_role) {
                return response()->success($team_role,'Team Role Added Successfully');
            }
            else{
                return response()->fail('Team Could Not Be Added');
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

        $team_role = TeamRole::find($id);

        if($team_role && $user->id==$team_role->created_by) {
            return response()->success($team_role,'Team Role Fetched Successfully');
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
    public function updateTeamRole(TeamRoleRequest $request, $id)
    {
        $user=Auth::user();
        $team_role=TeamRole::find($id);
        if($team_role && $team_role->created_by==$user->id) {
            $input_array=$request->all();
            if ($request->hasFile('image')) {
                if($team_role->image!=null)
                {
                    Helper::deleteImage($team_role->image);
                }

                $input_array['image']=Helper::uploadImage($request->image);
            }
            else if($team_role->image!=null)
            {
                Helper::deleteImage($team_role->image);
                $team_role->image=null;
            }

            $team_role->update($input_array);

            return response()->success($team_role,'Team Role Updated Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }

    public function updateOrder(Request $request)
    {
        try
        {
            DB::beginTransaction();
            $ids = $request->id;
            $orders=$request->order;
            for($i=0 ; $i<count($ids) ; $i++)
            {
                $user=Auth::user();
                $team_role=TeamRole::find($ids[$i]);
                if($team_role && $team_role->created_by==$user->id) {
                    if($orders[$i]>count($ids) || $orders[$i]<1)
                    {
                        DB::rollback();
                        return response()->fail('Order Number Is Not Correct');
                    }
                    else{
                        TeamRole::where('id', $ids[$i])->update(['order'=> $orders[$i]]);
                    }
                }
                else{
                    return response()->fail('User Not Authorized');
                }


            }
            DB::commit();
            return response()->success([],'Team Roles Order Updated Successfully');
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

    public function deleteImage($id)
    {
        $user=Auth::user();

        $team_role=TeamRole::find($id);
        if($team_role && $user->id == $team_role->created_by) {
            if($team_role->image) {
                Helper::deleteImage($team_role->image);
                $team_role->image=null;
                $team_role->save();
                return response()->success($team_role,'Image Deleted Successfully');
            }
            else{
                return response()->fail('Image Not Found');
            }
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
        $team_role=TeamRole::find($id);
        $pitch = $team_role->pitch;
        if($team_role && $team_role->created_by==$user->id) {
            $deleted_order=$team_role->order;
            Helper::deleteImage($team_role->image);
            $team_role = TeamRole::destroy($id);
            $all_team_roles=$pitch->teamRoles;
            foreach ($all_team_roles as $team_role)
            {
                if($team_role->order>$deleted_order)
                {
                    $team_role->order=$team_role->order-1;
                    $team_role->save();
                }
            }

            return response()->success([],'Team Role Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
