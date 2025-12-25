<?php

namespace App\Http\Requests;

use App\Traits\ApiResponser;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

abstract class BaseFormRequest extends FormRequest
{
    use ApiResponser;

    public function validateResolved()
    {
        parent::validateResolved();
    }

    abstract public function rules(): array;

    abstract public function authorize(): bool;

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException($this->errorResponse(
            $validator->errors()->first(),
            $validator->errors()->toArray()
        ));
    }
}
