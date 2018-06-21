<?php

namespace CannaPlan\Http\Controllers;
use CannaPlan\Models\Competitor;
use CannaPlan\Http\Requests\CompetitorRequest;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class CompetitorController extends Controller
{
    public function index()
    {
        //
    }

    public function store(CompetitorRequest $request)
    {
        if(Pitch::find($request->input('pitch_id'))){
            if(Pitch::is_user_pitch($request->input('pitch_id'))!=false) {
                $input = $request->all();
                $competitor = Competitor::create($input);
                return response()->success($competitor,'Competitor Created Successfully');
            }
            else{
                return response()->fail("No Pitch In Company With This Identifier");
            }
        }
        else{
            return response()->fail("Pitch Not Found");
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

        $competitor = Competitor::find($id);

        if($competitor && $user->id==$competitor->created_by) {
            return response()->success($competitor,'Competitor Fetched Successfully');
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
    public function updateCompetitor(CompetitorRequest $request, $id)
    {
        $user=Auth::user();
        $competitor=Competitor::find($id);
        if($competitor && $competitor->id==$user->id) {
            $competitor = Competitor::where('id', $id)->update($request->all());

            return response()->success($request->all(),'Competitor Updated Successfully');

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
                $competitor=Competitor::find($ids[$i]);
                if($competitor && $competitor->id==$user->id) {

                    if($orders[$i]>count($ids) || $orders[$i]<1)
                    {
                        DB::rollback();
                        return response()->fail('Order Number Is Not Correct');
                    }
                    else{
                        Competitor::where('id', $ids[$i])->update(['order'=> $orders[$i]]);
                    }

                }
                else{
                    return response()->fail('User Not Authorized');
                }

            }
            DB::commit();
            return response()->success([],'Competitor Order Updated Successfully');
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
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        $user=Auth::user();
        $competitor=Competitor::find($id);
        if($competitor && $competitor->id==$user->id) {
            $competitor = Competitor::destroy($id);

            return response()->success([],'Competitor Deleted Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
}
