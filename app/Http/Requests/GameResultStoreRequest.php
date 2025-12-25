<?php

namespace App\Http\Requests;

class GameResultStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_id'      => 'required|exists:games,id',
            'team_name'    => 'filled|nullable|array|size:2',
            'team_name.en' => 'filled',
            'team_name.gu' => 'filled',
            'caption'      => 'filled|nullable',
            'wise_caption' => 'filled|nullable',
            'man_of_match' => 'filled|nullable',
            'rank'         => 'required',
            'image'        => 'filled|nullable',
            'member_id'    => 'required|array',

        ];
    }
}
