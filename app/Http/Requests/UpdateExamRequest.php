<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:10000',
            'semester_id' => 'required|integer|exists:semesters,id',
            'start_date' => 'required|date',
            'stop_date' => 'required|date|after_or_equal:start_date',
        ];
    }
}
