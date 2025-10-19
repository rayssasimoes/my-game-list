<form method="POST" action="{{ route('register') }}">
    @csrf

    <!-- Name -->
    <div class="mb-3">
        <label for="register_name" class="form-label">{{ __('Name') }}</label>
        <input id="register_name" class="form-control @error('name') is-invalid @enderror" type="text" name="name" value="{{ old('name') }}" required autofocus autocomplete="name">
        @error('name')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Email Address -->
    <div class="mb-3">
        <label for="register_email" class="form-label">{{ __('Email') }}</label>
        <input id="register_email" class="form-control @error('email') is-invalid @enderror" type="email" name="email" value="{{ old('email') }}" required autocomplete="username">
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3">
        <label for="register_password" class="form-label">{{ __('Password') }}</label>
        <input id="register_password" class="form-control @error('password') is-invalid @enderror" type="password" name="password" required autocomplete="new-password">
        @error('password')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" required autocomplete="new-password">
    </div>

<div class="flex items-center justify-end mt-4">
        <button type="button" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal">
            {{ __('Already registered?') }}
        </button>

        <x-primary-button class="ms-4">
            {{ __('Register') }}
        </x-primary-button>
    </div>
</form>
