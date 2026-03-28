<?php

namespace App\Http\Requests;

class MemberGalleryUploadRequest extends BaseFormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'image.*' => 'image|mimes:jpeg,png,jpg,gif,svg',
            'video.*' => 'mimetypes:video/avi,video/mpeg,video/quicktime',
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
