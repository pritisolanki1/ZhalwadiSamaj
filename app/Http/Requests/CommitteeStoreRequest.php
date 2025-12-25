<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class CommitteeStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'          => 'filled|exists:members,id',
            'name'               => 'required|array|size:2',
            'name.en'            => 'required',
            'name.gu'            => 'required',
            'authority_types'    => 'required|array|size:1',
            'authority_types.en' => [
                'required',
                Rule::in([
                    'Main Committee',
                    'Yuva Committee',
                    'Lady Committee',
                ]),
            ],
            //"authority_types.gu" => "required" ,
            'birth_date'         => 'date_format:Y-m-d',
            'designation'        => 'required|array|size:1',
            'designation.en'     => 'required',
            //"designation.gu"            => "required" ,
            'phone'              => 'filled',
            'zone_id'            => 'filled|exists:zones,id',
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
