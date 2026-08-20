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
    <meta name="theme-color" content="#1B3A34">
    <link rel="manifest" href="/manifest.json">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fraunces:ital,opsz,wght@0,9..144,400;0,9..144,600;0,9..144,700;1,9..144,500&family=IBM+Plex+Sans:wght@400;500;600;700&family=IBM+Plex+Mono:wght@500;600&display=swap" rel="stylesheet">

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
