@props([
    'class' => '',
    'width' => 'w-full max-w-lg',
    'height' => '',
])

<div class="mx-auto my-12 px-4 {{ $class }} {{ $width }} {{ $height }}">
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="px-6 pt-8 pb-6 border-b border-gray-100 text-center">
            <img
                src="{{ asset(config('app.logo')) }}"
                alt="{{ config('app.name') }}"
                class="rounded-full w-20 h-20 border border-gray-200 shadow-sm mx-auto mb-4"
            >

            @isset($header)
                {{ $header }}
            @else
                <h1 class="text-2xl font-bold text-gray-900">{{ config('app.name') }}</h1>
            @endisset
        </div>

        <div class="px-6 py-6">
            {{ $slot }}
        </div>
    </div>

    @isset($footer)
        <div class="mt-4 text-center text-sm text-gray-600">
            {{ $footer }}
        </div>
    @endisset
</div>
