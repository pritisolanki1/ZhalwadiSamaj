<?php

namespace App\Http\Requests;

class MemberRequestUpdateRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return auth()->user() && !auth()->user()->hasRole('Member');
    }

    public function rules(): array
    {
        return [
            'status'        => 'required|string|in:Pending,In Progress,Completed,Rejected',
            'admin_remarks' => 'nullable|string|max:5000',
        ];
    }

    public function messages(): array
    {
        return [
            'status.required' => 'Please select a status.',
            'status.in'       => 'The selected status is invalid.',
        ];
    }
}
