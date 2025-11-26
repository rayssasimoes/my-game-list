/**
 * Script da página de redefinição de senha
 * Gerencia validação de senha e toggle de visibilidade
 */

document.addEventListener('DOMContentLoaded', function() {
    
    // ==== PASSWORD TOGGLE ====
    const toggleIcons = document.querySelectorAll('.password-toggle-icon');
    
    toggleIcons.forEach(icon => {
        const targetId = icon.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);

        if (!passwordInput) return;

        // Ativa/desativa ícone conforme input
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                icon.classList.remove('password-icon-inactive');
                icon.classList.add('password-icon-active');
            } else {
                icon.classList.remove('password-icon-active');
                icon.classList.add('password-icon-inactive');
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                passwordInput.type = 'password';
            }
        });

        // Alterna visibilidade
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            if (!this.classList.contains('password-icon-active')) return;

            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            }
        });
    });

    // ==== PASSWORD VALIDATION ====
    const newPasswordInput = document.getElementById('new_password');
    const newPasswordHint = document.getElementById('new_password_hint');
    
    if (newPasswordInput) {
        let validationTimeout;

        newPasswordInput.addEventListener('input', function() {
            const value = this.value;
            
            clearTimeout(validationTimeout);

            if (value.length === 0) {
                this.classList.remove('password-invalid');
                if (newPasswordHint) newPasswordHint.classList.remove('invalid');
                return;
            }

            validationTimeout = setTimeout(() => {
                if (value.length < 6) {
                    this.classList.add('password-invalid');
                    if (newPasswordHint) newPasswordHint.classList.add('invalid');
                } else {
                    this.classList.remove('password-invalid');
                    this.classList.add('password-valid');
                    if (newPasswordHint) newPasswordHint.classList.remove('invalid');
                }
            }, 400);
        });
    }

    // ==== PASSWORD MATCH VALIDATION ====
    const confirmPasswordInput = document.querySelector('.password-match-validation');
    if (confirmPasswordInput) {
        const matchTarget = document.getElementById(confirmPasswordInput.dataset.matchTarget);
        const matchHint = document.getElementById('confirm_password_hint');
        
        function validatePasswordMatch() {
            if (confirmPasswordInput.value.length > 0) {
                if (confirmPasswordInput.value !== matchTarget.value) {
                    confirmPasswordInput.classList.add('password-invalid');
                    confirmPasswordInput.classList.remove('password-valid');
                    if (matchHint) {
                        matchHint.style.display = 'block';
                        matchHint.classList.add('invalid');
                    }
                    return false;
                } else {
                    confirmPasswordInput.classList.remove('password-invalid');
                    confirmPasswordInput.classList.add('password-valid');
                    if (matchHint) {
                        matchHint.style.display = 'none';
                    }
                    return true;
                }
            }
            return true;
        }
        
        confirmPasswordInput.addEventListener('input', validatePasswordMatch);
        matchTarget.addEventListener('input', validatePasswordMatch);
        
        // Validação no submit
        const resetForm = document.getElementById('resetPasswordForm');
        if (resetForm) {
            resetForm.addEventListener('submit', function(e) {
                if (!validatePasswordMatch()) {
                    e.preventDefault();
                    alert('As senhas não coincidem. Por favor, verifique.');
                }
            });
        }
    }
});
