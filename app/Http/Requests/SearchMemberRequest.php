<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SearchMemberRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'length'                => ['filled'],
            'search_key'            => [
                'filled',
                'in:name,unique_number,native,profession',
            ],
            'search_value'          => ['filled'],
            'filter_by_blood_group' => ['filled'],
            'filter_by_zone'        => ['filled'],
        ];
    }
}
