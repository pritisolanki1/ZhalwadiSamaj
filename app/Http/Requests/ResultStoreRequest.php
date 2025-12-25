<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class ResultStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'  => 'required|exists:members,id',
            'year'       => 'required|date_format:Y',
            'class'      => 'required',
            'class_type' => 'filled',
            //"grade"     => "filled" ,
            //"school"    => "filled" ,
            'type'       => [
                'required',
                Rule::in([
                    'Study',
                    'Graduation',
                ]),
            ],
            'medium'     => [
                'required',
                Rule::in([
                    'English',
                    'Gujarati',
                ]),
            ],
            'rank'       => 'required|integer',
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [
            'name.en' => 'trim|capitalize|escape',
        ];
    }
}
