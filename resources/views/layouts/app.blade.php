<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'MyGameList') }}</title>

    <!-- CSS Puro -->
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
    
    <!-- JavaScript -->
    <script src="{{ asset('js/app.js') }}" defer></script>
</head>
<body>
    <!-- Barra de Carregamento -->
    <div id="loading-bar" class="loading-bar"></div>
    
    <div id="app">
    <!-- Top Navbar -->
    <nav class="navbar">
            <div class="navbar-container">

                <!-- Logo -->
                <a class="navbar-brand" href="{{ url('/') }}">
                    MyGameList
                </a>

                <!-- Search Bar -->
                <div class="search-container">
                    <input class="search-input" type="search" placeholder="{{ __('Buscar jogos...') }}" aria-label="Search">
                </div>

                <!-- Right Side Of Navbar -->
                <div class="navbar-menu">
                    <!-- Link principal: Jogos -->
                    <a class="nav-link" href="#">Jogos</a>
                    
                    <!-- Authentication Links -->
                    @guest
                        @if (Route::has('login'))
                            <a class="nav-link" href="#" onclick="openModal('loginModal')">
                                {{ __('Entrar') }}
                            </a>
                        @endif

                        @if (Route::has('register'))
                            <a class="nav-link" href="#" onclick="openModal('registerModal')">
                                {{ __('Criar Conta') }}
                            </a>
                        @endif
                    @else
                        <div class="nav-item dropdown">
                            <a class="dropdown-toggle" href="#" role="button">
                                {{ Auth::user()->name }}
                            </a>

                            <div class="dropdown-menu">
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    {{ __('Perfil') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('games.my-list') }}">
                                    {{ __('Lista') }}
                                </a>
                                <a class="dropdown-item" href="{{ route('profile.edit') }}">
                                    {{ __('Configurações') }}
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" onclick="openModal('logoutConfirmModal')">
                                    {{ __('Sair') }}
                                </a>
                                <form id="logout-form" action="{{ route('logout') }}" method="POST" class="d-none">
                                    @csrf
                                </form>
                            </div>
                        </div>
                    @endguest
                </div>
            </div>
        </nav>

        <div class="container mt-4">
            @if (session('success'))
                <div class="alert alert-success">
                    {{ session('success') }}
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger">
                    {{ session('error') }}
                </div>
            @endif
        </div>

        <main>
            @yield('content')
        </main>
    </div>

    <!-- Login Modal -->
    <div class="modal" id="loginModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Entrar') }}</h5>
                    <button type="button" class="btn-close" onclick="closeModal('loginModal')">&times;</button>
                </div>
                <div class="modal-body">
                    @include('auth.partials.login-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Register Modal -->
    <div class="modal" id="registerModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Criar Conta') }}</h5>
                    <button type="button" class="btn-close" onclick="closeModal('registerModal')">&times;</button>
                </div>
                <div class="modal-body">
                    @include('auth.partials.register-form')
                </div>
            </div>
        </div>
    </div>

    <!-- Forgot Password Modal -->
    <div class="modal" id="forgotPasswordModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Esqueceu a Senha?') }}</h5>
                    <button type="button" class="btn-close" onclick="closeModal('forgotPasswordModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="text-white-50">
                        {{ __('Forgot your password? No problem. Just let us know your email address and we will email you a password reset link that will allow you to choose a new one.') }}
                    </p>
                    <x-auth-session-status class="mb-4" :status="session('status')" />
                    <form method="POST" action="{{ route('password.email') }}">
                        @csrf
                        <div class="mb-3">
                            <label for="password_email" class="form-label">{{ __('Email') }}</label>
                            <input id="password_email" class="form-control" type="email" name="email" value="{{ old('email') }}" required autofocus>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <button type="button" class="btn btn-link" onclick="closeModal('forgotPasswordModal'); openModal('loginModal')">
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

    <!-- Logout Confirmation Modal -->
    <div class="modal" id="logoutConfirmModal">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ __('Confirmar Saída') }}</h5>
                    <button type="button" class="btn-close" onclick="closeModal('logoutConfirmModal')">&times;</button>
                </div>
                <div class="modal-body">
                    <p class="mb-0">{{ __('Você tem certeza que deseja sair?') }}</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeModal('logoutConfirmModal')">
                        {{ __('Cancelar') }}
                    </button>
                    <button type="button" class="btn btn-primary" onclick="document.getElementById('logout-form').submit();">
                        {{ __('Sim') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    @include('layouts.partials._footer')
</body>
</html>