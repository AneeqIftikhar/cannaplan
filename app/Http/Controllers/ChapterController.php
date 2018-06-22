<?php

namespace CannaPlan\Http\Controllers;

use Illuminate\Http\Request;
use CannaPlan\Http\Requests\ChapterRequest;
use CannaPlan\Models\Chapter;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ChapterController extends Controller
{

//Commenting store function as there will be prefilled chapters which are only there to be edited
//user will not be able to add new chapter for initial version of the app
//    public function store(ChapterRequest $request)
//    {
//        $input = $request->all();
//        $chapter = Chapter::create($input);
//        return response()->success($chapter,'Chapter Created Successfully');
//    }

   //We would not be needing show function for chapter
//    public function show($id)
//    {
//        $chapter = Chapter::find($id);
//        if($chapter) {
//            return response()->success($chapter,'Chapter Fetched Successfully');
//        }
//        else{
//            return response()->fail('Chapter Not Found');
//        }
//    }



    public function updateChapter(Request $request, $id)
    {
        $chapter = Chapter::find($id);
        $user=Auth::user();
        if($chapter && $user->id==$chapter->created_by)
        {
            $chapter = Chapter::find($id);
            $chapter->update($request->all());
            if($chapter){
                return response()->success($request->all(),'Chapter Updated Successfully');
            }
            else{
                return response()->fail('Chapter Not Found');
            }
        }
        else
        {
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
            $user=Auth::user();
            for($i=0 ; $i<count($ids) ; $i++)
            {
                $chapter=Chapter::find($ids[$i]);
                if($chapter && $user->id==$chapter->created_by)
                {
                    if($orders[$i]>count($ids) || $orders[$i]<1)
                    {
                        DB::rollback();
                        return response()->fail('Order Number Is Not Correct');
                    }
                    else{
                        Chapter::where('id', $ids[$i])->update(['order'=> $orders[$i]]);
                    }
                }
                else{
                    DB::rollback();
                    return response()->fail('User Not Authorized');
                }

            }
            DB::commit();
            return response()->success([],'Chapter Order Updated Successfully');
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

//    Delition of chapter is not included in this phase
//    public function destroy($id)
//    {
//        $chapter = Chapter::destroy($id);
//
//        if($chapter){
//            return response()->success([],'Chapter Deleted Successfully');
//        }
//        else{
//            return response()->fail('Chapter Not Found');
//        }
//    }
}
