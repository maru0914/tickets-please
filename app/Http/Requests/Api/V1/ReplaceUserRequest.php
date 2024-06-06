<?php

namespace App\Http\Requests\Api\V1;

class ReplaceUserRequest extends BaseUserRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'data.attributes.name' => 'required|string',
            'data.attributes.email' => 'required|email',
            'data.attributes.isManager' => 'required|boolean',
            'data.attributes.password' => 'required|string'
        ];
    }
}
