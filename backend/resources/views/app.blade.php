<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover, maximum-scale=5">
    <meta name="theme-color" content="#4f46e5">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="description" content="{{ \App\Models\Setting::getValue('portal_meta_description', '') }}">
    <meta name="keywords" content="{{ \App\Models\Setting::getValue('portal_meta_keywords', '') }}">

    <title inertia>{{ \App\Models\Setting::getValue('organization_name', config('app.name', 'Laravel')) }}</title>

    @if(\App\Models\Setting::getRawValue('organization_favicon'))
        <link rel="icon" href="{{ \App\Models\Setting::getValue('organization_favicon') }}" type="image/x-icon">
    @endif

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @routes
    @vite(['resources/js/app.js'])
    @inertiaHead
</head>

<body class="font-sans antialiased">
    @inertia
</body>

</html>