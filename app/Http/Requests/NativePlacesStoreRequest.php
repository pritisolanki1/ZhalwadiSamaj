<?php

namespace App\Http\Requests;

class NativePlacesStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'native'      => 'required|array|size:2',
            'native.en'   => 'required',
            'native.gu'   => 'required',
            'taluka'      => 'filled|array|size:2',
            'taluka.en'   => 'filled',
            'taluka.gu'   => 'filled',
            'district'    => 'filled|array|size:2',
            'district.en' => 'filled',
            'district.gu' => 'filled',
            'state'       => 'filled|array|size:2',
            'state.en'    => 'filled',
            'state.gu'    => 'filled',
            'latitude'    => 'filled',
            'longitude'   => 'filled',
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
