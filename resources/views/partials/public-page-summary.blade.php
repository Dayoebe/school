@php
    $page = $page ?? [];
    $label = (string) ($page['label'] ?? '');
    $summary = (string) ($page['summary'] ?? $page['description'] ?? '');
@endphp

@if ($summary !== '')
    <section class="border-b border-slate-200 bg-white py-4" aria-label="Page summary">
        <div class="mx-auto max-w-6xl px-4 sm:px-6 lg:px-8">
            @if ($label !== '' && $label !== 'Home')
                <nav class="mb-2 text-xs font-semibold text-slate-500" aria-label="Breadcrumb">
                    <ol class="flex flex-wrap items-center gap-2">
                        <li>
                            <a href="{{ route('home') }}" class="text-red-700 hover:underline">Home</a>
                        </li>
                        <li aria-hidden="true">/</li>
                        <li class="text-slate-700" aria-current="page">{{ $label }}</li>
                    </ol>
                </nav>
            @endif

            <p class="max-w-4xl text-sm leading-relaxed text-slate-700 sm:text-base">{{ $summary }}</p>
        </div>
    </section>
@endif
