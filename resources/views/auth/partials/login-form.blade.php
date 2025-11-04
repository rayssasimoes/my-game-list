<!-- Session Status -->
@if (session('status'))
    <div class="alert alert-success mb-4" role="alert">
        {{ session('status') }}
    </div>
@endif

<form method="POST" action="{{ route('login') }}" 
    x-data="{ 
        email: '', 
        password: '', 
        passwordVisible: false,
        resetForm() {
            this.email = '';
            this.password = '';
            this.passwordVisible = false;
        }
    }"
    x-init="
        const modal = document.getElementById('loginModal');
        if (modal) {
            modal.addEventListener('hide.bs.modal', () => {
                resetForm();
            });
        }
    ">
    @csrf

    <!-- Email Address -->
    <div class="mb-3">
        <label for="login_email" class="form-label">{{ __('Email or Username') }}</label>
        <div class="input-group">
            <span class="input-group-addon">
                <i class="bi bi-envelope"></i>
            </span>
            <input id="login_email" 
                   class="form-control @error('email') is-invalid @enderror" 
                   type="text" 
                   name="email" 
                   x-model="email"
                   value="{{ old('email') }}" 
                   placeholder="email@exemplo.com ou username"
                   required 
                   autofocus 
                   autocomplete="username">
        </div>
        @error('email')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <!-- Password -->
    <div class="mb-3 password-container">
        <label for="login_password" class="form-label">{{ __('Password') }}</label>
        <input id="login_password" 
               class="form-control form-control-with-icon @error('password') is-invalid @enderror" 
               :type="passwordVisible ? 'text' : 'password'" 
               name="password" 
               x-model="password"
               required 
               autocomplete="current-password">
        
        <!-- Ícone ver/ocultar senha com estados ativo/inativo -->
        <i class="bi password-toggle-icon"
           :class="{
               'bi-eye-slash': passwordVisible,
               'bi-eye': !passwordVisible,
               'password-icon-inactive': password.length === 0,
               'password-icon-active': password.length > 0
           }"
           @click="password.length > 0 ? passwordVisible = !passwordVisible : null"
           :style="password.length === 0 ? 'cursor: default;' : 'cursor: pointer;'"
           role="button"
           :aria-label="password.length > 0 ? 'Alternar visibilidade da senha' : ''"></i>
        
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

        <button type="submit" class="btn btn-primary-custom ms-3">
            {{ __('Log in') }}
        </button>
    </div>
</form>