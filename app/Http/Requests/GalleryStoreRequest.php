<?php

namespace App\Http\Requests;

class GalleryStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'      => 'required|array|size:2',
            'name.en'   => 'required',
            'name.gu'   => 'required',
            'latitude'  => 'filled',
            'longitude' => 'filled',
            'date'      => 'date_format:Y-m-d',
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
