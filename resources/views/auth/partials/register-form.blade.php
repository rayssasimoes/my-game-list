<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input id="name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="password" class="form-label">{{ __('Password') }}</label>
        <input id="password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>

    <div class="d-flex justify-content-end align-items-center mt-4">
        <a class="text-decoration-none me-3" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
            {{ __('Already registered?') }}
        </a>

        <button type="submit" class="btn btn-primary">
            {{ __('Register') }}
        </button>
    </div>
</form>