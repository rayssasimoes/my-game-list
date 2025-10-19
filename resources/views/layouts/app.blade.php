<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyGameList') }}</title>

    <!-- Scripts -->
    @vite(['resources/scss/app.scss', 'resources/js/app.js'])
</head>
<body class="bg-light">
    <div id="app">
        <!-- Top Navbar -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark shadow-sm fixed-top">
            <div class="container-fluid">
                <!-- Always-visible Hamburger Button -->
                <button class="btn btn-dark" type="button" data-bs-toggle="offcanvas" data-bs-target="#mainOffcanvas" aria-controls="mainOffcanvas">
                    <span class="navbar-toggler-icon"></span>
                </button>

                <!-- Logo -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    MyGameList
                </a>

                <!-- Search Bar -->
                <div class="mx-auto">
                    <form class="d-flex" role="search">
                        <input class="form-control me-2" type="search" placeholder="{{ __('Buscar jogos...') }}" aria-label="Search">
                        <button class="btn btn-outline-success" type="submit">{{ __('Buscar') }}</button>
                    </form>
                </div>

                <!-- Right Side Of Navbar -->
                <div class="d-flex">
                    <ul class="navbar-nav ms-auto">
                        <!-- Authentication Links -->
                        @guest
                            @if (Route::has('login'))
                                <li class="nav-item">
                                    <button type="button" class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#loginModal">
                                        {{ __('Entrar') }}
                                    </button>
                                </li>
                            @endif

                            @if (Route::has('register'))
                                <li class="nav-item">
                                    <button type="button" class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#registerModal">
                                        {{ __('Criar Conta') }}
                                    </button>
                                </li>
                            @endif
                        @else
                            <li class="nav-item dropdown">
                                <a id="navbarDropdown" class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" v-pre>
                                    {{ Auth::user()->name }}
                                </a>

                                <div class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                                    <a class="dropdown-item" href="{{ route('logout') }}"
                                       onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                                        {{ __('Logout') }}
                                    </a>
                                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                        @csrf
                                    </form>
                                </div>
                            </li>
                        @endguest
                    </ul>
                </div>
            </div>
        </nav>

        <!-- Offcanvas Menu -->
        <div class="offcanvas offcanvas-start bg-dark text-white" tabindex="-1" id="mainOffcanvas" aria-labelledby="mainOffcanvasLabel">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="mainOffcanvasLabel">Menu</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
            </div>
            <div class="offcanvas-body">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">{{ __('Início') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/') }}">{{ __('Explorar Jogos') }}</a>
                    </li>
                    @auth
                    <li class="nav-item">
                        <a class="nav-link" href="{{ url('/minha-lista') }}">{{ __('Minha Lista') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('profile.edit') }}">{{ __('Meu Perfil') }}</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('logout') }}"
                           onclick="event.preventDefault();
                                         document.getElementById('logout-form-offcanvas').submit();">
                            {{ __('Logout') }}
                        </a>
                        <form id="logout-form-offcanvas" action="{{ route('logout') }}" method="POST" class="d-none">
                            @csrf
                        </form>
                    </li>
                    @endauth
                </ul>
            </div>
        </div>

        <div class="container mt-4">
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="{{ __('Close') }}"></button>
                </div>
            @endif
        </div>

        <main class="py-4">
            @yield('content')
        </main>
    </div>

    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="loginModalLabel">{{ __('Entrar') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @include('auth.partials.login-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal fade" id="registerModal" tabindex="-1" aria-labelledby="registerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="registerModalLabel">{{ __('Criar Conta') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    @include('auth.partials.register-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal fade" id="forgotPasswordModal" tabindex="-1" aria-labelledby="forgotPasswordModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="forgotPasswordModalLabel">{{ __('Esqueceu a Senha?') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </p>
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="password_email" class="form-label">{{ __('Email') }}</label>
                            <input id="password_email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus>
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-link" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">
                                {{ __('Voltar para o login') }}
                            </button>
                            <button type="submit" class="btn btn-primary">
                                {{ __('Email Password Reset Link') }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials._footer')
</body>
</html>