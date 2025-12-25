<?php

namespace App\Http\Requests;

class TeamStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'    => 'filled|exists:members,id',
            'team_type'    => 'required|array|size:1',
            'team_type.en' => 'required',
            'admin_type'   => 'filled',
            // "admin_type" => ["required", Rule::in(['Admin 1', 'Admin 2', 'Admin 3'])],
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
