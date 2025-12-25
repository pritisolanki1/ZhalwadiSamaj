<?php

namespace App\Http\Requests;

class ReportStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id'     => 'required',
            'type'   => 'required|in:gallery_image,member_gallery',
            'value'  => 'sometimes',
            'notes'  => 'sometimes',
            'status' => 'sometimes|in:open,pending,working,solve,reject',
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
