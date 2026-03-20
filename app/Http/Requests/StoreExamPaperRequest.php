<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamPaperRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'my_class_id' => 'required|integer|exists:my_classes,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'title' => 'required|string|max:255',
            'instructions' => 'nullable|string|max:5000',
            'typed_content' => 'nullable|string|max:200000',
            'attachment' => 'nullable|file|mimes:jpeg,jpg,png,webp,pdf|max:15360',
        ];
    }
}
