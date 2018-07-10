<?php

namespace CannaPlan\Http\Controllers;

use CannaPlan\Http\Requests\PitchRequest;
use CannaPlan\Models\Company;
use CannaPlan\Models\Pitch;
use Illuminate\Http\Request;
use CannaPlan\Helpers\Helper;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Input;
class PitchController extends Controller
{

    public function getPitchByCompany($id)
    {
        $user=Auth::user();
        $company=Company::find($id);
        if($company && $user->id == $company->created_by) {
            $pitch = $company->pitches[0];
            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            return response()->success($pitch,'Pitch Fetched Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }

    }
    public function updatePitch(PitchRequest $request, $id)
    {
        $user=Auth::user();

        $pitch = Pitch::find($id);
        if($pitch && $user->id == $pitch->created_by) {
            $input_array=$request->all();
            if ($request->hasFile('logo')) {
                $input_array['logo']=str_replace('\\', '/', Helper::uploadImage($request->logo));
            }
            //$pitch=Pitch::where('id', $id)->update($input_array);

            $pitch->update($input_array);
            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            return response()->success($pitch,'Pitch Updated Successfully');
        }
        else{
            return response()->fail('User Not Authorized');
        }

    }

    public function deleteLogo($id)
    {
        $user=Auth::user();

        $pitch = Pitch::find($id);
        if($pitch && $user->id == $pitch->created_by) {
             if($pitch->logo && Helper::deleteImage($pitch->logo)) {
                 $pitch->logo=null;
                 $pitch->save();
                 $pitch->competitors;
                 $pitch->milestones;
                 $pitch->targetMarketGraphs;
                 $pitch->teamRoles;
                return response()->success($pitch,'Logo Deleted Successfully');
            }
            else{
                return response()->fail('Image Not Found');
            }
        }
        else{
            return response()->fail('User Not Authorized');
        }
    }

    public function getPitchByPublishKey($publish_key)
    {
        $pitch = Pitch::where('publish_key',$publish_key)->where('is_published',true)->first();
        if($pitch) {
            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            $pitch->company->currency;
            return response()->success($pitch,'Pitch Fetched Successfully');

        }
        else{
            return response()->fail('No Such Key is Published');
        }

    }

    public function publishPitchByCompany($id)
    {
        $user=Auth::user();
        $company=Company::find($id);
        if($company && $user->id == $company->created_by) {
            $time=time();
            $pitch = $company->pitches[0];
            if($pitch->is_published!=true)
            {
                $pitch->is_published=true;
                $pitch->publish_key=$time;
                $pitch->save();
                $pitch->competitors;
                $pitch->milestones;
                $pitch->targetMarketGraphs;
                $pitch->teamRoles;
                return response()->success($pitch,'Pitch Fetched Successfully');
            }
            else
            {
                return response()->fail('Already Published');
            }


        }
        else{
            return response()->fail('User Not Authorized');
        }
    }
    public function unpublishPitchByCompany($id)
    {
        $user=Auth::user();
        $company=Company::find($id);
        if($company && $user->id == $company->created_by) {
            $pitch = $company->pitches[0];
            if($pitch->is_published==true)
            {
                $pitch->is_published=false;
                $pitch->save();
                $pitch->competitors;
                $pitch->milestones;
                $pitch->targetMarketGraphs;
                $pitch->teamRoles;
                return response()->success($pitch,'Your Pitch is no Longer Published');
            }
            else
            {
                return response()->fail('Pitch Not Published');
            }


        }
        else{
            return response()->fail('User Not Authorized');
        }
    }

    public function getPitchByCompanyForPDF($id)
    {
        $user=Auth::user();
        $company=Company::find($id);
        if($company && $user->id == $company->created_by) {
            $pitch = $company->pitches[0];
            if($pitch->logo!=null)
            {
                $pitch['image_base64']=base64_encode(file_get_contents($pitch->logo));
            }

            $pitch->competitors;
            $pitch->milestones;
            $pitch->targetMarketGraphs;
            $pitch->teamRoles;
            foreach ($pitch->teamRoles as $teamRole)
            {
                if($teamRole->image!=null)
                {
                    $teamRole['image_base64']=base64_encode(file_get_contents($teamRole->image));
                }
            }

            return response()->success($pitch,'Pitch Fetched Successfully');

        }
        else{
            return response()->fail('User Not Authorized');
        }
    }

}
