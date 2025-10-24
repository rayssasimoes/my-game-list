<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Laravel') }}</title>

        <!-- Scripts -->
        @vite(['resources/scss/app.scss', 'resources/js/app.js'])
    </head>
    <body>
        <div class="container">
            <div class="row justify-content-center align-items-center min-vh-100">
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm">
                        <div class="card-body p-4">
                            <div class="text-center mb-4">
                                <a href="/">
                                    {{-- Assumindo que você terá um logo em public/images/logo.png --}}
                                    <img src="{{ asset('images/logo.png') }}" alt="Logo" width="150">
                                </a>
                            </div>

                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </body>
</html>