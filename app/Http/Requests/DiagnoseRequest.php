<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rules\File;
use Illuminate\Validation\Rule;

class DiagnoseRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the post request.
     *
     * @return array
     */
    public function store()
    {
        return [
            'diagnoses' => [
                Rule::in(['normal','warning','danger']),
                'required'
            ],
            'file' => [
                'required',
                File::types(['mp3','wav'])
                    ->max(2 * 1024),
            ]
        ];
    }

    /**
     * Get the validation rules that apply to the put/patch request.
     *
     * @return array
     */
    public function update()
    {
        return [
        ];
    }

    /**
     * Get the validation rules that apply to the delete request.
     *
     * @return array
     */
    public function destroy()
    {
        return [

        ];
    }
}
