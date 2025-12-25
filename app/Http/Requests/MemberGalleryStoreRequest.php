<?php

namespace App\Http\Requests;

class MemberGalleryStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        if (auth()->user()->hasRole('Member')) {
            return [
                'member_id' => 'sometimes|exists:members,id',
            ];
        } else {
            return [
                'member_id' => 'required|exists:members,id',
            ];
        }
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
