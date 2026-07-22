{{-- resources/views/app.blade.php --}}
{{-- Root view every Inertia page renders into. The manifest link and
     theme-color meta tag are what make the PWA installable (SETUP.md step 7a).
     @routes requires the Ziggy package: composer require tightenco/ziggy
     — gives JS pages a route('name') helper matching your named routes.
     If you'd rather skip it for now, delete the @routes line below. --}}
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#1F3864">
    <link rel="manifest" href="/manifest.json">

    <title inertia>{{ config('app.name', 'Q.E.S') }}</title>

    @routes
    @viteReactRefresh
    @vite(['resources/css/app.css', 'resources/js/app.jsx'])
    @inertiaHead
</head>
<body class="antialiased">
    @inertia
</body>
</html>
