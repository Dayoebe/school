@component('mail::message')
# {{ $broadcastMessage->title }}

Hello {{ $recipient->name }},

{{ $broadcastMessage->body }}

@component('mail::panel')
Sent by: {{ $broadcastMessage->createdBy?->name ?? 'School Admin' }}

Sent at: {{ $broadcastMessage->sent_at?->toDayDateTimeString() ?? now()->toDayDateTimeString() }}
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
