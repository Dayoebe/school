<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateExamStatusRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if ($this->status == 'active' || $this->status == 1 || $this->status == 'on') {
            $this->merge(['status' => true]);
        } elseif ($this->status == 'inactive' || $this->status == 0 || $this->status == 'off' || $this->status == null) {
            $this->merge(['status' => false]);
        }
    }

    public function rules(): array
    {
        return [
            'status' => 'boolean',
        ];
    }
}
