<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreExamRecordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'section_id' => 'required|integer|exists:sections,id',
            'subject_id' => 'required|integer|exists:subjects,id',
            'user_id' => 'required|integer|exists:users,id',
            'exam_records' => 'array',
            'exam_records.*.exam_slot_id' => 'required|integer|exists:exam_slots,id',
            'exam_records.*.student_marks' => 'required|integer|min:0',
        ];
    }
}
