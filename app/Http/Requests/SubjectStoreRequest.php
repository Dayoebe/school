<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectStoreRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name' => [
                'required',
                'string',
                Rule::unique('subjects')->where(function ($query) {
                    return $query->where('my_class_id', $this->input('my_class_id'));
                }),
            ],
            'short_name'  => 'required|string|max:255',
            'my_class_id' => 'required|exists:my_classes,id',
            'teachers.*'  => 'nullable|exists:users,id',
        ];
    }

    public function messages(): array
    {
        return [
            'name.unique' => 'This subject already exists for the selected class.',
        ];
    }
}
