@php
    $pwaThemeColor = $pwaThemeColor ?? '#dc2626';
    $pwaTitle = $pwaTitle ?? config('app.name', 'School Portal');
    $pwaIcon = $pwaIcon ?? asset(config('app.logo', 'logo.png'));
@endphp

<meta name="application-name" content="{{ $pwaTitle }}">
<meta name="mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-capable" content="yes">
<meta name="apple-mobile-web-app-status-bar-style" content="default">
<meta name="apple-mobile-web-app-title" content="{{ $pwaTitle }}">
<meta name="theme-color" content="{{ $pwaThemeColor }}">

<link rel="manifest" href="{{ route('pwa.manifest') }}">
<link rel="apple-touch-icon" href="{{ $pwaIcon }}">
