<?php

namespace App\Http\Requests;

class BusinessStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'       => 'required|array|size:2',
            'name.en'    => 'required',
            'name.gu'    => 'required',
            'address'    => 'array',
            'address.en' => 'required',
            'address.gu' => 'required',
            'latitude'   => 'filled',
            'longitude'  => 'filled',
            'phone'      => 'array',
            'email'      => 'array',
            'email.*'    => 'email',
            'website'    => 'array',
            'about'      => 'array',
            'about.en'   => 'required',
            'about.gu'   => 'required',
            'partner_id' => 'array',
            'logo'       => 'array',
            'slider'     => 'array',
            'gallery'    => 'array',
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
