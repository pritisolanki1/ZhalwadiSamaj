<?php

namespace App\Http\Requests;

class JobStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'business_id'     => 'filled|exists:businesses,id',
            'title'           => 'required',
            'job_description' => 'required|array',
            'city'            => 'required',
        ];
    }

    public function messages(): array
    {
        return [];
    }

    public function filters(): array
    {
        return [
            'title' => 'trim|capitalize|escape',
        ];
    }
}
