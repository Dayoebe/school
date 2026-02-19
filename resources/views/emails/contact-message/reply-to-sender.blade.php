<x-mail::message>
# Response To Your Message

Thank you for contacting {{ $contactMessage->school?->name ?? config('app.name') }}.

<x-mail::panel>
**Your Subject:** {{ $contactMessage->subject }}  
**Status:** {{ str_replace('_', ' ', ucfirst($contactMessage->status)) }}
</x-mail::panel>

**Our Response**

{{ $contactMessage->response_note }}

If you need more help, reply to this email.

Regards,  
{{ config('app.name') }}
</x-mail::message>
