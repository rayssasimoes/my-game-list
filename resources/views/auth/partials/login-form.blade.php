<!-- Session Status -->
@if (session('status'))
    <div class="alert alert-success mb-4" role="alert">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}">
    @csrf

    <!-- Email Address -->
    <div class="mb-3">
        <label for="login_email" class="form-label">{{ __('Email') }}</label>
        <input id="login_email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autofocus autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="login_password" class="form-label">{{ __('Password') }}</label>
        <input id="login_password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="current-password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Remember Me -->
    <div class="mb-3 form-check">
        <input id="remember_me" type="checkbox" class="form-check-input" name="remember">
        <label for="remember_me" class="form-check-label">{{ __('Remember me') }}</label>
    </div>

    <div class="d-flex justify-content-end align-items-center">
        @if (Route::has('password.request'))
            <a class="btn btn-link text-decoration-none" href="{{ route('password.request') }}">
                {{ __('Forgot your password?') }}
            </a>
        @endif

        <button type="submit" class="btn btn-primary ms-3">
            {{ __('Log in') }}
        </button>
    </div>
</form>