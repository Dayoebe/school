@component('mail::message')
# {{ $notice->title }}

Hello {{ $recipient->name }},

{{ $messageBody }}

@component('mail::panel')
Active from {{ $notice->start_date?->format('M d, Y') }} to {{ $notice->stop_date?->format('M d, Y') }}.
@endcomponent

@if ($notice->attachment)
An attachment was added to this notice. Please sign in to the dashboard to open it.
@endif

@component('mail::button', ['url' => route('dashboard')])
Open Dashboard
@endcomponent

Thanks,
{{ config('app.name') }}
@endcomponent
