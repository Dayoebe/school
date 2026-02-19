<x-mail::message>
# New Contact Message Received

A new contact message was submitted from the public contact page.

<x-mail::panel>
**School:** {{ $contactMessage->school?->name ?? 'N/A' }}  
**Sender:** {{ $contactMessage->full_name }}  
**Email:** {{ $contactMessage->email }}  
**Phone:** {{ $contactMessage->phone ?: 'N/A' }}  
**Subject:** {{ $contactMessage->subject }}
</x-mail::panel>

**Message**

{{ $contactMessage->message }}

<x-mail::button :url="route('contacts.messages.index')">
Open Contact Messages
</x-mail::button>

Regards,  
{{ config('app.name') }}
</x-mail::message>
