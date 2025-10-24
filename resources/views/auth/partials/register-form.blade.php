<form method="POST" action="{{ route('register') }}"
    x-data="{
        formData: {
            name: '',
            email: '',
            password: '',
            password_confirmation: ''
        },
        errors: {},
        passwordVisible: false,
        submitting: false,
        resetForm() {
            this.formData = { name: '', email: '', password: '', password_confirmation: '' };
            this.errors = {};
        },
        submitForm() {
            this.submitting = true;
            this.errors = {};

            fetch('{{ route('register') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('input[name=_token]').value
                },
                body: JSON.stringify(this.formData)
            })
            .then(response => {
                if (response.ok) {
                    window.location.href = '/dashboard';
                } else if (response.status === 422) {
                    response.json().then(data => {
                        this.errors = data.errors;
                    });
                } else {
                    alert('Ocorreu um erro inesperado. Tente novamente.');
                }
            })
            .catch(error => {
                alert('Ocorreu um erro de conexão. Verifique sua internet e tente novamente.');
            })
            .finally(() => {
                this.submitting = false;
            });
        }
    }"
    x-init="
        const modal = document.getElementById('registerModal');
        if (modal) {
            modal.addEventListener('hide.bs.modal', () => {
                resetForm();
            });
        }
    "
    @submit.prevent="submitForm">

    @csrf

    <!-- Name -->
    <div class="mb-3">
        <label for="name" class="form-label">{{ __('Name') }}</label>
        <input id="name" class="form-control" :class="{ 'is-invalid': errors.name }" type="text" name="name" x-model="formData.name" required autofocus autocomplete="name">
        <div x-show="errors.name" x-text="errors.name ? errors.name[0] : ''" class="invalid-feedback"></div>
    </div>

    <!-- Email Address -->
    <div class="mb-3">
        <label for="email" class="form-label">{{ __('Email') }}</label>
        <input id="email" class="form-control" :class="{ 'is-invalid': errors.email }" type="email" name="email" x-model="formData.email" required autocomplete="username">
        <div x-show="errors.email" x-text="errors.email ? errors.email[0] : ''" class="invalid-feedback"></div>
    </div>

<!-- Password -->
<div class="mb-3 password-container">
    <label for="register_password" class="form-label">{{ __('Password') }}</label>
    <input id="register_password"
           class="form-control form-control-with-icon"
           :class="{
               'is-invalid': errors.password || (formData.password.length > 0 && formData.password.length < 8)
           }"
           :type="passwordVisible ? 'text' : 'password'"
           name="password"
           x-model="formData.password"
           required
           autocomplete="new-password"
           aria-describedby="passwordHelp">

    <!-- Ícone ver/ocultar senha: visível quando houver pelo menos 1 caractere -->
    <i class="bi password-toggle-icon"
       :class="passwordVisible ? 'bi-eye-slash' : 'bi-eye'"
       x-show="formData.password.length > 0"
       @click="passwordVisible = !passwordVisible"
       x-cloak
       role="button"
       aria-label="Alternar visibilidade da senha"></i>

    <!-- Mensagem de erro do backend -->
    <div x-show="errors.password" x-text="errors.password ? errors.password[0] : ''" class="invalid-feedback"></div>

    <!-- Texto de ajuda (aparece somente quando não há erro do backend) -->
    <div x-show="!errors.password"
         id="passwordHelp"
         :class="{
             'form-text': true,
             'text-danger': (formData.password.length > 0 && formData.password.length < 8)
         }">
        A senha deve ter no mínimo 8 caracteres.
    </div>
</div>

    <!-- Confirm Password -->
    <div class="mb-3">
        <label for="password_confirmation" class="form-label">{{ __('Confirm Password') }}</label>
        <input id="password_confirmation" class="form-control" type="password" name="password_confirmation" x-model="formData.password_confirmation" required autocomplete="new-password">
    </div>

    <div class="d-flex justify-content-end align-items-center mt-4">
        <a class="text-decoration-none me-3" href="#" data-bs-toggle="modal" data-bs-target="#loginModal">
            {{ __('Already registered?') }}
        </a>

        <button type="submit" class="btn btn-primary" :disabled="submitting">
            <span x-show="submitting" class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span>
            <span x-text="submitting ? 'Criando Conta...' : 'Criar Conta'">{{ __('Register') }}</span>
        </button>
    </div>
</form>