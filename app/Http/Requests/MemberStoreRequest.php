<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class MemberStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'                => 'required|array|size:2',
            'name.en'             => 'required',
            'name.gu'             => 'required',
            'gender'              => 'required|in:Male,Female',
            'birth_date'          => 'date_format:d-m-Y',
            'unique_number' => 'sometimes|nullable|unique:members,unique_number,' . request()->id,
            'phone' => 'filled|regex:/^[5-9]{1}[0-9]{9}/|unique:members,phone,' . request()->id,
            'email' => 'sometimes|nullable|unique:members,email,' . request()->id,
            'blood_group'         => [
                Rule::in([
                    'A+',
                    'A-',
                    'B+',
                    'B-',
                    'AB+',
                    'AB-',
                    'O+',
                    'O-',
                    null,
                ]),
            ],
            'relationShip_status' => [
                'required',
                Rule::in([
                    'Single',
                    'Engaged',
                    'Married',
                    'Divorced',
                    'Widow',
                    'Widower',
                ]),
            ],
            'profession'          => 'array',
            'profession.en'       => 'filled',
            'profession.gu'       => 'filled',
            'profession_type'     => 'array',
            'profession_type.en'  => 'filled',
            'profession_type.gu'  => 'filled',
            'address'             => 'array',
            'address.en'          => 'filled',
            'address.gu'          => 'filled',
            'work_address'        => 'array',
            'work_address.en'     => 'filled',
            'work_address.gu'     => 'filled',
            'qualification'       => 'array',
            'qualification.en'    => 'filled',
            'qualification.gu'    => 'filled',
            'mosal'               => 'array',
            'mosal.en'            => 'filled',
            'mosal.gu'            => 'filled',

            'slider'          => 'array',
            'mother_name'     => 'array',
            'mother_name.en'  => 'filled',
            'mother_name.gu'  => 'filled',
            'father_name'     => 'array',
            'father_name.en'  => 'filled',
            'father_name.gu'  => 'filled',
            'education'       => 'filled',
            'kuldevi_id'      => 'filled|exists:kuldevis,id',
            'zone_id'         => 'filled|exists:zones,id',
            'native_place_id' => 'filled|exists:native_places,id',
            'is_private'      => 'boolean',
            'status'          => [
                Rule::in([
                    '0',
                    '1',
                    '2',
                ]),
            ],

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
