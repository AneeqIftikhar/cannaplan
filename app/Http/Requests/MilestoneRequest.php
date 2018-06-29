<?php

namespace CannaPlan\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class MilestoneRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        return [
            'pitch_id' => 'required',
            'title'=>'required',
            'due_date' => 'required',
            'responsible' => 'required|max:100',
            'details' => 'required|max:255'
        ];
    }
}
