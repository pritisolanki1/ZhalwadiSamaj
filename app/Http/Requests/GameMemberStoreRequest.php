<?php

namespace App\Http\Requests;

class GameMemberStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_result_id' => 'required|exists:game_results,id',
            'member_id'      => 'filled|exists:members,id',
        ];
    }
}
