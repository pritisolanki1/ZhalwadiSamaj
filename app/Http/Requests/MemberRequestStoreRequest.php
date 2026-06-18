<?php

namespace App\Http\Requests;

class MemberRequestStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'subject'     => 'required|string|in:Add New Family Member,Update Family Member Information,Delete Family Member,Other Request',
            'description' => 'required|string|min:10|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'subject.required'     => 'Please select a subject.',
            'subject.in'           => 'The selected subject is invalid.',
            'description.required' => 'Please provide a description.',
            'description.min'      => 'Description must be at least 10 characters.',
        ];
    }
}
