/* =========================================
   MY GAME LIST - JAVASCRIPT
   Modais, Dropdown e Alerts
   ========================================= */

// ==== MODAL ====
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        document.body.classList.add('modal-open');
        modal.classList.add('show');
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.classList.remove('modal-open');
        
        // Limpar formulário ao fechar - problema 2
        const form = modal.querySelector('form');
        if (form) {
            form.reset();
            
            // Resetar ícones de senha
            const passwordIcons = modal.querySelectorAll('.password-toggle-icon');
            passwordIcons.forEach(icon => {
                icon.classList.remove('password-icon-active', 'bi-eye-slash');
                icon.classList.add('password-icon-inactive', 'bi-eye');
                const targetId = icon.getAttribute('data-target');
                const input = document.getElementById(targetId);
                if (input) input.type = 'password';
            });
            
            // Remover classes de validação
            const passwordInputs = modal.querySelectorAll('.password-validation');
            passwordInputs.forEach(input => {
                input.classList.remove('password-invalid');
            });
            const passwordHints = modal.querySelectorAll('.password-hint');
            passwordHints.forEach(hint => {
                hint.classList.remove('invalid');
            });

            // Remover classes de validação de disponibilidade
            const availabilityInputs = modal.querySelectorAll('.availability-check');
            availabilityInputs.forEach(input => {
                input.classList.remove('availability-invalid', 'availability-valid');
            });
            const availabilityHints = modal.querySelectorAll('.availability-hint');
            availabilityHints.forEach(hint => {
                hint.classList.remove('invalid', 'valid');
                
                // Resetar hint de username para texto padrão
                if (hint.id === 'username-hint') {
                    hint.textContent = '3-20 caracteres, apenas letras, números e underscore';
                    hint.style.display = 'block';
                    hint.style.color = ''; // Remove cor de validação
                }
                
                // Resetar visibilidade do hint de email
                if (hint.id === 'email-hint') {
                    hint.style.display = 'none';
                    hint.textContent = 'Este email já está em uso'; // Texto padrão
                }
            });
        }
    }
}

// Fechar modal ao clicar fora dele
document.addEventListener('click', (e) => {
    if (e.target.classList.contains('modal')) {
        closeModal(e.target.id);
    }
});

// Fechar modal com ESC
document.addEventListener('keydown', (e) => {
    if (e.key === 'Escape') {
        const modals = document.querySelectorAll('.modal.show');
        modals.forEach(modal => {
            closeModal(modal.id);
        });
    }
});

// ==== ALERTS AUTO-DISMISS ====
document.addEventListener('DOMContentLoaded', () => {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transition = 'opacity 0.5s ease';
            setTimeout(() => {
                alert.remove();
            }, 500);
        }, 5000);
    });

    // Inicializar autocomplete de busca
    initSearchAutocomplete();

    // Inicializar toggle de senha
    initPasswordToggle();

    // Inicializar validação de senha em tempo real
    initPasswordValidation();

    // Inicializar validação de disponibilidade (username/email)
    initAvailabilityCheck();
});

// ==== PASSWORD TOGGLE ====
function initPasswordToggle() {
    const toggleIcons = document.querySelectorAll('.password-toggle-icon');

    toggleIcons.forEach(icon => {
        const targetId = icon.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);

        if (!passwordInput) return;

        // Monitora mudanças no input para ativar/desativar ícone
        passwordInput.addEventListener('input', function() {
            if (this.value.length > 0) {
                // Ativa o ícone
                icon.classList.remove('password-icon-inactive');
                icon.classList.add('password-icon-active');
            } else {
                // Desativa o ícone
                icon.classList.remove('password-icon-active');
                icon.classList.add('password-icon-inactive');
                // Reseta para olho fechado
                icon.classList.remove('bi-eye-slash');
                icon.classList.add('bi-eye');
                passwordInput.type = 'password';
            }
        });

        // Clique no ícone para alternar visibilidade - CORRIGIDO problema 3
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Só funciona se tiver texto
            if (!this.classList.contains('password-icon-active')) return;

            if (passwordInput.type === 'password') {
                // Mostra senha
                passwordInput.type = 'text';
                this.classList.remove('bi-eye');
                this.classList.add('bi-eye-slash');
            } else {
                // Oculta senha
                passwordInput.type = 'password';
                this.classList.remove('bi-eye-slash');
                this.classList.add('bi-eye');
            }
        });
    });
}

// ==== PASSWORD VALIDATION (Real-time) - Problema 4 ====
function initPasswordValidation() {
    const passwordInput = document.getElementById('register_password');
    if (!passwordInput) return;

    const passwordHint = document.getElementById('password-hint');
    let validationTimeout;

    passwordInput.addEventListener('input', function() {
        const value = this.value;
        
        // Limpa timeout anterior
        clearTimeout(validationTimeout);

        // Se está vazio, remove validação
        if (value.length === 0) {
            this.classList.remove('password-invalid');
            if (passwordHint) passwordHint.classList.remove('invalid');
            return;
        }

        // Aguarda 400ms após parar de digitar (validação rápida)
        validationTimeout = setTimeout(() => {
            if (value.length < 6) {
                // Senha muito curta
                this.classList.add('password-invalid');
                if (passwordHint) passwordHint.classList.add('invalid');
            } else {
                // Senha válida
                this.classList.remove('password-invalid');
                if (passwordHint) passwordHint.classList.remove('invalid');
            }
        }, 400); // Reduzido de 800ms para 400ms - consistência
    });
}

// ==== SEARCH AUTOCOMPLETE ====
function initSearchAutocomplete() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    const searchContainer = document.querySelector('.search-container');
    const searchClear = document.getElementById('searchClear');
    const searchIcon = document.getElementById('searchIcon');
    
    // Criar dropdown de sugestões
    let dropdown = document.querySelector('.search-dropdown');
    if (!dropdown) {
        dropdown = document.createElement('div');
        dropdown.className = 'search-dropdown';
        searchContainer.appendChild(dropdown);
    }

    let debounceTimer;

    // Evento de digitação
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();

        // Limpa o timer anterior
        clearTimeout(debounceTimer);

        // Se query vazia ou muito curta, esconde dropdown
        if (query.length < 2) {
            dropdown.classList.remove('show');
            // esconder ícone de limpar quando vazio e mostrar lupa
            if (searchClear) searchClear.classList.remove('visible');
            if (searchIcon) searchIcon.classList.remove('hidden');
            return;
        }

        // Debounce: espera 300ms após parar de digitar
        debounceTimer = setTimeout(() => {
            fetchSearchResults(query, dropdown);
        }, 300);
        // mostrar ícone de limpar quando tiver texto e esconder lupa
        if (searchClear && searchIcon) {
            if (this.value.length > 0) {
                searchClear.classList.add('visible');
                searchIcon.classList.add('hidden');
            } else {
                searchClear.classList.remove('visible');
                searchIcon.classList.remove('hidden');
            }
        } else if (searchClear) {
            if (this.value.length > 0) searchClear.classList.add('visible');
            else searchClear.classList.remove('visible');
        }
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            dropdown.classList.remove('show');
            if (searchClear) searchClear.classList.remove('visible');
            if (searchIcon) searchIcon.classList.remove('hidden');
        }
    });

    // Fechar dropdown com ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
            if (searchClear) searchClear.classList.remove('visible');
            if (searchIcon) searchIcon.classList.remove('hidden');
        }
    });

    // Clique no ícone X para limpar a busca
    if (searchClear) {
        searchClear.addEventListener('click', function(e) {
            e.preventDefault();
            searchInput.value = '';
            searchInput.dispatchEvent(new Event('input'));
            dropdown.classList.remove('show');
            this.classList.remove('visible');
            if (searchIcon) searchIcon.classList.remove('hidden');
            // opcional: focar novamente no input
            searchInput.focus();
        });
    }
}

function fetchSearchResults(query, dropdown) {
    // Mostra loading
    dropdown.innerHTML = '<div class="search-loading">Buscando...</div>';
    dropdown.classList.add('show');

    // Faz requisição AJAX
    fetch(`includes/search-autocomplete.php?query=${encodeURIComponent(query)}`)
        .then(response => response.json())
        .then(games => {
            if (games.length === 0) {
                dropdown.innerHTML = '<div class="search-no-results">Nenhum jogo encontrado</div>';
                return;
            }

            // Renderiza resultados
            let html = '';
            games.forEach(game => {
                const year = game.year ? `${game.year}` : 'Ano desconhecido';
                html += `
                    <div class="search-result-item" data-game-id="${game.id}">
                        <img src="${game.cover}" alt="${game.name}" class="search-result-image">
                        <div class="search-result-info">
                            <div class="search-result-name">${game.name}</div>
                            <div class="search-result-year">${year}</div>
                        </div>
                    </div>
                `;
            });

            dropdown.innerHTML = html;
        })
        .catch(error => {
            console.error('Erro na busca:', error);
            dropdown.innerHTML = '<div class="search-error">Erro ao buscar jogos</div>';
        });
}

// ==== AVAILABILITY CHECK (Username/Email) ====
function initAvailabilityCheck() {
    const availabilityInputs = document.querySelectorAll('.availability-check');

    availabilityInputs.forEach(input => {
        const checkType = input.getAttribute('data-check-type'); // 'username' ou 'email'
        const hintId = checkType + '-hint';
        const hint = document.getElementById(hintId);
        let checkTimeout;

        input.addEventListener('input', function() {
            const value = this.value.trim();

            // Limpa timeout anterior
            clearTimeout(checkTimeout);

            // Remove classes anteriores
            this.classList.remove('availability-invalid', 'availability-valid');
            if (hint) {
                hint.classList.remove('invalid', 'valid');
            }

            // Se vazio, não valida e reseta hint
            if (value.length === 0) {
                if (hint) {
                    if (checkType === 'username') {
                        // Mostra texto padrão para username
                        hint.textContent = '3-20 caracteres, apenas letras, números e underscore';
                        hint.style.display = 'block';
                        hint.style.color = ''; // Remove cores de validação
                    } else if (checkType === 'email') {
                        // Esconde hint de email quando vazio
                        hint.style.display = 'none';
                    }
                }
                return;
            }

            // Validação mínima para username (3-20 caracteres)
            if (checkType === 'username') {
                if (value.length < 3) {
                    // Mostra texto padrão enquanto digita (menos de 3 caracteres)
                    if (hint) {
                        hint.textContent = '3-20 caracteres, apenas letras, números e underscore';
                        hint.style.display = 'block';
                        hint.classList.remove('invalid', 'valid');
                    }
                    return; // Não verifica ainda
                }
            }

            // Validação básica de email
            if (checkType === 'email') {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test(value)) {
                    return; // Email inválido, não verifica
                }
            }

            // Debounce: aguarda 800ms após parar de digitar
            checkTimeout = setTimeout(() => {
                checkAvailability(value, checkType, this, hint);
            }, 400); // Reduzido de 800ms para 400ms - mais rápido
        });
    });
}

function checkAvailability(value, type, input, hint) {
    // Faz requisição AJAX
    fetch(`includes/check-availability.php?type=${type}&value=${encodeURIComponent(value)}`)
        .then(response => response.json())
        .then(data => {
            if (data.available) {
                // Disponível - borda verde
                input.classList.remove('availability-invalid');
                input.classList.add('availability-valid');
                
                if (hint) {
                    hint.classList.remove('invalid');
                    hint.classList.add('valid');
                    
                    if (type === 'username') {
                        hint.textContent = '✓ Nome de usuário disponível';
                    } else if (type === 'email') {
                        hint.style.display = 'block';
                        hint.textContent = '✓ Email disponível';
                    }
                }
            } else {
                // Não disponível - borda vermelha
                input.classList.remove('availability-valid');
                input.classList.add('availability-invalid');
                
                if (hint) {
                    hint.classList.remove('valid');
                    hint.classList.add('invalid');
                    
                    if (type === 'username') {
                        hint.textContent = '✗ Este nome de usuário já está em uso';
                    } else if (type === 'email') {
                        hint.style.display = 'block';
                        hint.textContent = '✗ Este email já está em uso';
                    }
                }
            }
        })
        .catch(error => {
            console.error('Erro ao verificar disponibilidade:', error);
            // Em caso de erro, não bloqueia (remove classes)
            input.classList.remove('availability-invalid', 'availability-valid');
            if (hint) {
                hint.classList.remove('invalid', 'valid');
            }
        });
}
