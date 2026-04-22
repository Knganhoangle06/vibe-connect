<!DOCTYPE html>
<html lang="vi">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Mini Social')</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/homepage.css') }}">
    @yield('styles')
</head>
<body>

    @include('layouts.header')

    <div class="main-container">

        <main>
            @yield('content')
        </main>

    </div>

    @yield('script')
    <script src="{{ asset('js/script.js') }}"></script>
</body>
</html>
