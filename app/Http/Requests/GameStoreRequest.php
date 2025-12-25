<?php

namespace App\Http\Requests;

use Illuminate\Validation\Rule;

class GameStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'game_name'            => 'required|array|size:2',
            'game_name.en'         => 'required',
            'game_name.gu'         => 'required',
            'game_type'            => [
                'required',
                Rule::in([
                    'single',
                    'multiple',
                ]),
            ],
            'year'                 => 'required',
            'man_of_the_series_id' => 'filled',
        ];
    }
}
