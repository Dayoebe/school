<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreNoticeRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title'                 => 'required|string|max:255',
            'content'               => 'required|string',
            'attachment'            => 'nullable|file|mimes:jpeg,png,jpg,gif,pdf,doc,docx|max:10000',
            'start_date'            => 'required|date',
            'stop_date'             => 'required|date|after_or_equal:start_date',
            'send_email'            => 'nullable|boolean',
            'email_subject'         => 'nullable|string|max:255',
            'email_body'            => 'nullable|string|max:10000',
            'email_recipient_roles' => 'nullable|array',
            'email_recipient_roles.*' => 'string|in:student,teacher,parent,admin,principal,super-admin,super_admin',
        ];
    }
}
