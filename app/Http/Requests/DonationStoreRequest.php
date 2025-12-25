<?php

namespace App\Http\Requests;

use App\Models\Donation;
use Illuminate\Validation\Rule;

class DonationStoreRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'member_id'         => 'required|exists:members,id',
            'donations_type'    => [
                'required',
                Rule::in(Donation::DONATION_TYPES),
            ],
            'amount'            => 'required|min:1|max:100000',
            'date'              => 'date_format:Y-m-d',
            'transition_id'     => 'filled|string',
            'transition'        => 'required|array',
            'transition_status' => [
                'required',
                Rule::in(Donation::TRANSITION_STATUSES),
            ],
            'status'            => [
                'required',
                Rule::in(Donation::STATUSES),
            ],
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
