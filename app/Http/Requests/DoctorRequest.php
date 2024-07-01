<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DoctorRequest extends BaseFormRequest
{
    /**
     * Get the validation rules that apply to the post request.
     *
     * @return array
     */
    public function store()
    {
        return [
            'fullname' => 'required',
            'address' => 'required',
            'phone' => 'required',
            'emergency_phone' => 'required',
            'gender' => 'required',
            'age' => 'required',

            // user
            'email' => 'required|email',
            'password' => 'required',
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
            'role' => [
                Rule::in(['admin','patient','doctor'])
            ],
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
