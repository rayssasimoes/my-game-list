/* my-game-list: scripts principais (modais, navbar, autocomplete, etc.) */

// ==== NAVBAR - MENU HAMBÚRGUER ====
document.addEventListener('DOMContentLoaded', () => {
    const hamburgerBtn = document.getElementById('hamburgerBtn');
    const mobileMenu = document.getElementById('mobileMenu');
    
    if (hamburgerBtn && mobileMenu) {
        // criar overlay e garantir ordem no DOM
        const overlay = document.createElement('div');
        overlay.className = 'mobile-menu-overlay';
        overlay.style.zIndex = '10999';
        document.body.appendChild(overlay);

        // mover menu para body e forçar z-index alto para sobrepor overlay
        if (mobileMenu.parentElement !== document.body) {
            document.body.appendChild(mobileMenu);
        } else {
            document.body.appendChild(mobileMenu);
        }
        mobileMenu.style.zIndex = '11000';
        
    // Alternar menu
        hamburgerBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            hamburgerBtn.classList.toggle('active');
            mobileMenu.classList.toggle('active');
            overlay.classList.toggle('active');
            // alterna estado do menu
            document.body.style.overflow = mobileMenu.classList.contains('active') ? 'hidden' : '';
        });
        
        // Fechar ao clicar no overlay
        overlay.addEventListener('click', () => {
            hamburgerBtn.classList.remove('active');
            mobileMenu.classList.remove('active');
            overlay.classList.remove('active');
            document.body.style.overflow = '';
        });
        
        // Fechar ao clicar em um link
        const menuItems = mobileMenu.querySelectorAll('.mobile-menu-item');
        menuItems.forEach(item => {
            item.addEventListener('click', () => {
                hamburgerBtn.classList.remove('active');
                mobileMenu.classList.remove('active');
                overlay.classList.remove('active');
                document.body.style.overflow = '';
            });
        });

        // Após abrir o menu, verificar quem está no topo do menu (elementFromPoint)
        // e, se necessário, aplicar um fallback automático que agrupa overlay+menu
        // dentro de um root com z-index extremamente alto para contornar quirks.
        function checkAndFixStacking() {
            try {
                const rect = mobileMenu.getBoundingClientRect();
                const midX = Math.round(rect.left + rect.width / 2);
                const midY = Math.round(rect.top + rect.height / 2);
                const topEl = document.elementFromPoint(midX, midY);
                if (topEl && (topEl === overlay || !mobileMenu.contains(topEl))) {
                    // Aplicar fallback: criar um root que contém overlay e menu
                    let root = document.getElementById('mobile-menu-root');
                    if (!root) {
                        root = document.createElement('div');
                        root.id = 'mobile-menu-root';
                        root.style.position = 'fixed';
                        root.style.top = '0';
                        root.style.left = '0';
                        root.style.right = '0';
                        root.style.bottom = '0';
                        // z-index alto (perto do máximo) para sobrepor qualquer contexto
                        root.style.zIndex = '2147483646';
                        document.body.appendChild(root);
                    }

                    // Ajustar overlay e menu dentro do root
                    overlay.style.position = 'fixed';
                    overlay.style.zIndex = '2147483646';
                    mobileMenu.style.zIndex = '2147483647';
                    root.appendChild(overlay);
                    root.appendChild(mobileMenu);
                }
            } catch (err) {
                // falha silenciada: não bloqueia a UX
            }
        }

        // Chamar verificação ao abrir o menu (pequeno debounce para aguardar o render)
        hamburgerBtn.addEventListener('click', () => {
            setTimeout(() => {
                if (mobileMenu.classList.contains('active')) {
                    checkAndFixStacking();
                }
            }, 60);
        });
    }
});

// ==== NAVBAR - DROPDOWN DO USUÁRIO (APENAS DESKTOP) ====
document.addEventListener('DOMContentLoaded', () => {
    const userAvatarBtn = document.getElementById('userAvatarBtn');
    const userDropdownMenu = document.getElementById('userDropdownMenu');
    
    if (userAvatarBtn && userDropdownMenu) {
        userAvatarBtn.addEventListener('click', (e) => {
            // Só funciona em desktop (> 768px)
            if (window.innerWidth > 768) {
                e.stopPropagation();
                userDropdownMenu.classList.toggle('show');
            }
        });
        
        // Mobile/Tablet: clique no avatar leva diretamente ao perfil
        // Implementado separadamente para não interferir no dropdown de desktop
        const mobileAvatarClick = (e) => {
            if (window.matchMedia('(max-width: 768px)').matches) {
                const profileLinkEl = document.querySelector('#mobileMenu .mobile-menu-item[href*="page=profile"], .user-dropdown-item[href*="page=profile"]');
                const profileHref = profileLinkEl ? profileLinkEl.getAttribute('href') : 'index.php?page=profile';
                window.location.assign(profileHref);
            }
        };
        userAvatarBtn.addEventListener('click', mobileAvatarClick);
        
        // Fechar ao clicar fora (apenas desktop)
        document.addEventListener('click', (e) => {
            if (window.innerWidth > 768) {
                if (!userDropdownMenu.contains(e.target) && e.target !== userAvatarBtn) {
                    userDropdownMenu.classList.remove('show');
                }
            }
        });
    }
});

// ==== MODAL ====
function openModal(modalId, view) {
    const modal = document.getElementById(modalId);
    if (modal) {
        // Preencher campo de redirect (se existir) com a URL atual
        try {
            // Usar apenas path + query para evitar problemas de host/origin
            const currentUrl = window.location.pathname + window.location.search;
            // Se o modal contém um input name="redirect", preencher
            const redirectInput = modal.querySelector('input[name="redirect"]');
            if (redirectInput) redirectInput.value = currentUrl;
            // Também preencher inputs globais caso estejam fora do modal (fallback)
            const globalLogin = document.getElementById('login_redirect');
            if (globalLogin && !globalLogin.value) globalLogin.value = currentUrl;
            const globalRegister = document.getElementById('register_redirect');
            if (globalRegister && !globalRegister.value) globalRegister.value = currentUrl;
        } catch (e) {
            // não bloqueia abertura do modal
        }

        document.body.classList.add('modal-open');
        modal.classList.add('show');

        // Se for o modal de autenticação, garantir que a view padrão seja 'login'
        try {
            if (modalId === 'authModal') {
                // se view fornecida, usa; senão força para 'login'
                toggleAuthView(view || 'login');
            }
        } catch (err) {
            // não bloquear abertura do modal se toggleAuthView não existir
        }
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

// Alternar entre as visualizações de login e redefinição de senha
function toggleAuthView(view) {
    const loginView = document.getElementById('login-view');
    const forgotPasswordView = document.getElementById('forgot-password-view');

    if (view === 'login') {
        loginView.style.display = 'block';
        forgotPasswordView.style.display = 'none';
    } else if (view === 'forgot') {
        loginView.style.display = 'none';
        forgotPasswordView.style.display = 'block';
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

    initSearchAutocomplete();
    initPasswordToggle();
    initPasswordValidation();
    initAvailabilityCheck();
    initLoginForm();
    // Garantia: se o botão "Esqueci a senha" estiver presente, garante que abrirá a view de redefinição
    try {
        const forgotLinks = document.querySelectorAll('.login-actions .btn-link-custom');
        forgotLinks.forEach(btn => {
            // Se já houver onclick inline, não duplicar
            if (!btn.onclick) {
                btn.addEventListener('click', (e) => {
                    e.preventDefault();
                    try {
                        toggleAuthView('forgot');
                    } catch (err) {
                        // fallback para casos onde as views são modais separados
                        try { showForgotPassword(); } catch (e) { /* ignore */ }
                    }
                });
            }
        });
    } catch (err) {
        // não bloquear inicialização se falhar
        console.error('Erro ao inicializar fallback do link Esqueci a senha:', err);
    }
});

// ==== LOGIN FORM VIA AJAX ====
function initLoginForm() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;
    
    const errorMessage = document.getElementById('login-error-message');
    const errorText = document.getElementById('login-error-text');
    const passwordField = document.querySelector('.login-password-field');
    const errorHint = document.querySelector('.login-error-hint');
    
    loginForm.addEventListener('submit', function(e) {
        e.preventDefault();
        // Garantir fallback: se o input hidden redirect estiver vazio, preencher com path+query
        try {
            const redirectInput = this.querySelector('input[name="redirect"]') || document.getElementById('login_redirect');
            if (redirectInput && !redirectInput.value) {
                redirectInput.value = window.location.pathname + window.location.search;
            }
        } catch (err) {
            // ignore
        }
        
        // Esconder mensagens de erro anteriores
        if (errorMessage) errorMessage.style.display = 'none';
        if (errorHint) errorHint.style.display = 'none';
        if (passwordField) passwordField.classList.remove('is-invalid');
        
        const formData = new FormData(this);
        
        fetch('index.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                    // Preferir redirect vindo do servidor (validado). Se não existir, usar o valor submetido
                    let redirect = data.redirect || formData.get('redirect');
                    if (redirect) {
                        // Se for um caminho absoluto (começa com /), navegar diretamente
                        if (redirect.startsWith('/')) {
                            window.location.href = redirect;
                            return;
                        }

                        // Se for um caminho relativo (ex: index.php?page=...), navegar relativo
                        if (redirect.startsWith('index.php') || redirect.indexOf('page=') !== -1) {
                            // construir URL relativa
                            const base = window.location.origin + (window.location.pathname === '/' ? '' : '');
                            window.location.href = base + '/' + redirect.replace(/^\/+/, '');
                            return;
                        }

                        // último recurso: tentar construir URL e validar origem
                        try {
                            const url = new URL(redirect, window.location.origin);
                            if (url.origin === window.location.origin) {
                                window.location.href = url.href;
                                return;
                            }
                        } catch (err) {
                            // ignora
                        }
                    }

                    window.location.href = 'index.php';
            } else {
                // mostra erro no modal sem fechar
                if (passwordField) {
                    passwordField.classList.add('is-invalid');
                }
                if (errorHint) {
                    errorHint.textContent = data.error || 'Email/usuário ou senha incorretos';
                    errorHint.style.display = 'block';
                }
            }
        })
        .catch(error => {
            if (errorHint) {
                errorHint.textContent = 'Erro ao processar login. Tente novamente.';
                errorHint.style.display = 'block';
            }
        });
    });
}

// Fallback para register: garantir que redirect esteja preenchido antes do submit (formulário sem AJAX)
document.addEventListener('DOMContentLoaded', () => {
    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;
    registerForm.addEventListener('submit', function(e) {
        try {
            const redirectInput = this.querySelector('input[name="redirect"]') || document.getElementById('register_redirect');
            if (redirectInput && !redirectInput.value) {
                redirectInput.value = window.location.pathname + window.location.search;
            }
        } catch (err) {
            // ignore
        }
        // allow normal submit
    });
});

// ==== PASSWORD TOGGLE ====
function initPasswordToggle() {
    const toggleIcons = document.querySelectorAll('.password-toggle-icon');

    toggleIcons.forEach(icon => {
        const targetId = icon.getAttribute('data-target');
        const passwordInput = document.getElementById(targetId);

        if (!passwordInput) return;

    // ativa/desativa ícone conforme input
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

    // alterna visibilidade
        icon.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // só funciona se houver texto
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
    // Validar todos os campos com classe password-validation
    const passwordInputs = document.querySelectorAll('.password-validation');
    
    passwordInputs.forEach(passwordInput => {
        if (!passwordInput) return;
        
        // Buscar o hint correspondente (pode ser password-hint ou new_password_hint)
        let passwordHint = null;
        if (passwordInput.id === 'register_password') {
            passwordHint = document.getElementById('password-hint');
        } else if (passwordInput.id === 'new_password') {
            passwordHint = document.getElementById('new_password_hint');
        }
        
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
            }, 400);
        });
    });
}

// ==== SEARCH AUTOCOMPLETE ====
function initSearchAutocomplete() {
    // Inicializar para todos os campos de busca (desktop e mobile)
    const searchInputs = document.querySelectorAll('.search-input');
    
    searchInputs.forEach(searchInput => {
        if (!searchInput) return;

        const searchContainer = searchInput.closest('.search-container');
        const searchClear = searchContainer.querySelector('.search-clear');
        const searchIcon = searchContainer.querySelector('.search-icon');
        
        // Criar dropdown de sugestões
        let dropdown = searchContainer.querySelector('.search-dropdown');
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
                // Mostrar ícone de limpar quando tiver texto
                if (searchClear) searchClear.classList.remove('visible');
                return;
            }

            // debounce 300ms
            debounceTimer = setTimeout(() => {
                fetchSearchResults(query, dropdown);
            }, 300);
            
            // Mostrar ícone de limpar quando tiver texto
            if (searchClear) {
                if (this.value.length > 0) {
                    searchClear.classList.add('visible');
                } else {
                    searchClear.classList.remove('visible');
                }
            }
        });

        // fechar dropdown ao clicar fora
        document.addEventListener('click', function(e) {
            if (!searchContainer.contains(e.target)) {
                dropdown.classList.remove('show');
                if (searchClear) searchClear.classList.remove('visible');
            }
        });

        // Fechar dropdown com ESC
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                dropdown.classList.remove('show');
                if (searchClear) searchClear.classList.remove('visible');
            }
        });

        // limpar busca
        if (searchClear) {
            searchClear.addEventListener('click', function(e) {
                e.preventDefault();
                searchInput.value = '';
                searchInput.dispatchEvent(new Event('input'));
                dropdown.classList.remove('show');
                this.classList.remove('visible');
                // opcional: focar novamente no input
                searchInput.focus();
            });
        }
    });
}

// Função de diagnóstico: identifica ancestrais que criam stacking contexts
function analyzeStackingContexts(menuEl, overlayEl) {
    // Retorna um relatório simples sobre ancestrais que criam stacking contexts
    function createsStackingContext(el) {
        if (!el || el.nodeType !== 1) return false;
        const s = window.getComputedStyle(el);
        if (s.position === 'fixed' || s.position === 'sticky') return true;
        if (s.zIndex !== 'auto' && s.position !== 'static') return true;
        if (s.opacity && parseFloat(s.opacity) < 1) return true;
        if (s.transform && s.transform !== 'none') return true;
        if (s.filter && s.filter !== 'none') return true;
        if (s.perspective && s.perspective !== 'none') return true;
        if (s.isolation && s.isolation === 'isolate') return true;
        if (s.willChange && s.willChange !== 'auto') return true;
        return false;
    }

    const report = [];
    [menuEl, overlayEl].forEach(el => {
        const info = {
            id: el.id || '(sem id)',
            tag: el.tagName,
            computedZ: window.getComputedStyle(el).zIndex || '(auto)'
        };
        report.push(`Elemento: ${info.tag}#${info.id} - z-index: ${info.computedZ}`);
        let parent = el.parentElement;
        while (parent) {
            const sc = createsStackingContext(parent);
            const ps = window.getComputedStyle(parent);
            if (sc) {
                report.push(`STACKING CONTEXT: ${parent.tagName}#${parent.id || '(sem id)'} - position:${ps.position} z-index:${ps.zIndex}`);
            }
            parent = parent.parentElement;
        }
    });
    return report;
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
                    <a href="index.php?page=game&id=${game.id}" class="search-result-item" data-game-id="${game.id}">
                        <img src="${game.cover}" alt="${game.name}" class="search-result-image">
                        <div class="search-result-info">
                            <div class="search-result-name">${game.name}</div>
                            <div class="search-result-year">${year}</div>
                        </div>
                    </a>
                `;
            });

            dropdown.innerHTML = html;
        })
        .catch(error => {
            // falha na busca - notificamos visualmente
            dropdown.innerHTML = '<div class="search-error">Erro ao buscar jogos</div>';
        });
}

// ==== AVAILABILITY CHECK (Username/Email) - APENAS MODAIS ====
function initAvailabilityCheck() {
    // Pega apenas os inputs dos modais, não do edit-profile
    const availabilityInputs = document.querySelectorAll('.modal .availability-check');

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

// ==== MY LIST PAGE - TAB SWITCHING ====
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.mylist-tab');
    const tabContents = document.querySelectorAll('.mylist-tab-content');
    
    if (tabs.length > 0 && tabContents.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');
                
                // Remove active de todas as tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Remove active de todos os conteúdos
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Adiciona active na tab clicada
                tab.classList.add('active');
                
                // Adiciona active no conteúdo correspondente
                const targetContent = document.querySelector(`.mylist-tab-content[data-tab="${targetTab}"]`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
                
                // Atualiza URL (opcional - muda o parâmetro tab sem recarregar)
                const url = new URL(window.location);
                url.searchParams.set('tab', targetTab);
                window.history.pushState({}, '', url);
            });
        });
    }
});



// ==== MY LIST PAGE - GAME ACTIONS ====
// Funções editGame() e removeGame() agora estão em my-list.php

// ==== HOME PAGE - QUICK ACTIONS ====
document.addEventListener('DOMContentLoaded', () => {
    const quickActionBtns = document.querySelectorAll('.quick-action-btn');
    
    // Suporte para touch em mobile
    const gameCards = document.querySelectorAll('.game-card');
    let activeCard = null;
    
    gameCards.forEach(card => {
        card.addEventListener('touchstart', (e) => {
            // Se já tiver um card ativo e não for o mesmo, desativar
            if (activeCard && activeCard !== card) {
                activeCard.classList.remove('touch-active');
            }
            
            // Se tocar no mesmo card ativo, desativar (toggle)
            if (activeCard === card) {
                card.classList.remove('touch-active');
                activeCard = null;
            } else {
                // Ativar novo card
                card.classList.add('touch-active');
                activeCard = card;
            }
        });
    });
    
    // Fechar overlay ao tocar fora
    document.addEventListener('touchstart', (e) => {
        if (activeCard && !e.target.closest('.game-card')) {
            activeCard.classList.remove('touch-active');
            activeCard = null;
        }
    });
    
    quickActionBtns.forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.stopPropagation(); // Prevenir outros eventos
            
            const action = btn.getAttribute('data-action');
            const gameId = btn.getAttribute('data-game-id');
            const gameName = btn.getAttribute('data-game-name');
            const gameCover = btn.getAttribute('data-game-cover');
            
            if (action === 'more') {
                // Abrir menu de mais opções (implementar depois)
                alert('Menu de opções em desenvolvimento');
                return;
            }
            
            // Adicionar jogo à lista com o status específico
            addGameToList(gameId, action, btn, gameName, gameCover);
        });
    });
});

function addGameToList(gameId, status, btnElement, gameName, gameCover) {
    // Verificar se o botão já está ativo (remover do jogo da lista)
    const isActive = btnElement.classList.contains('active');
    
    // Mostrar feedback visual
    btnElement.disabled = true;
    btnElement.style.opacity = '0.6';
    
    if (isActive) {
        // Remover jogo da lista
        
        
        fetch('includes/remove-from-list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `game_id=${gameId}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Remover classe active
                btnElement.classList.remove('active');
                
                // Feedback visual temporário
                const originalHTML = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="bi bi-check"></i>';
                
                setTimeout(() => {
                    btnElement.innerHTML = originalHTML;
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                }, 1000);
                
                showNotification('Jogo removido da lista!', 'success');
            } else {
                btnElement.style.opacity = '1';
                btnElement.disabled = false;
                showNotification(data.message || 'Erro ao remover jogo', 'error');
            }
        })
        .catch(error => {
            console.error('Erro ao remover:', error);
            btnElement.style.opacity = '1';
            btnElement.disabled = false;
            showNotification('Erro ao remover jogo', 'error');
        });
    } else {
        // Adicionar jogo à lista
        
        
        fetch('includes/add-to-list.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `game_id=${gameId}&status=${status}&game_name=${encodeURIComponent(gameName)}&game_cover=${encodeURIComponent(gameCover)}`
        })
        .then(response => response.text().then(text => {
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erro ao fazer parse do JSON:', e);
                throw new Error('Resposta inválida do servidor');
            }
        }))
        .then(data => {
            if (data.success) {
                // Remover classe active de todos os botões do mesmo card
                const card = btnElement.closest('.game-card');
                const allBtns = card.querySelectorAll('.quick-action-btn[data-action]:not([data-action="more"])');
                allBtns.forEach(btn => btn.classList.remove('active'));
                
                // Marcar apenas o botão clicado como ativo
                btnElement.classList.add('active');
                
                // Feedback visual temporário
                const originalHTML = btnElement.innerHTML;
                btnElement.innerHTML = '<i class="bi bi-check"></i>';
                
                setTimeout(() => {
                    btnElement.innerHTML = originalHTML;
                    btnElement.style.opacity = '1';
                    btnElement.disabled = false;
                }, 1000);
                
                // Mostrar notificação de sucesso
                const messages = {
                    'completed': 'Marcado como Jogado!',
                    'playing': 'Adicionado em Jogando!',
                    'want_to_play': 'Adicionado à Lista de Desejos!',
                    'dropped': 'Marcado como Abandonado!'
                };
                showNotification(messages[status] || 'Jogo adicionado à sua lista!', 'success');
            } else {
                console.error('Erro retornado pelo servidor:', data.message);
                btnElement.style.opacity = '1';
                btnElement.disabled = false;
                showNotification(data.message || 'Erro ao adicionar jogo', 'error');
            }
        })
        .catch(error => {
            console.error('Error completo:', error);
            btnElement.style.opacity = '1';
            btnElement.disabled = false;
            showNotification('Erro ao adicionar jogo: ' + error.message, 'error');
        });
    }
}

function showNotification(message, type = 'success') {
    // Criar notificação temporária
    const notification = document.createElement('div');
    notification.className = `quick-notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 80px;
        right: 20px;
        background: ${type === 'success' ? '#22c55e' : '#ef4444'};
        color: white;
        padding: 1rem 1.5rem;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
        z-index: 10000;
        animation: slideIn 0.3s ease;
        font-weight: 600;
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.animation = 'slideOut 0.3s ease';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}

// Adicionar animações CSS para notificações
const style = document.createElement('style');
style.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(400px);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(400px);
            opacity: 0;
        }
    }
`;
document.head.appendChild(style);

// ==== PROFILE PAGE - TABS ====
document.addEventListener('DOMContentLoaded', () => {
    // Abas principais do perfil (Perfil, Jogos, Atividade)
    const profileTabs = document.querySelectorAll('.profile-tab');
    const profileTabContents = document.querySelectorAll('.profile-page .tab-content');
    
    if (profileTabs.length > 0 && profileTabContents.length > 0) {
        profileTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');
                
                // Remove active de todas as tabs
                profileTabs.forEach(t => t.classList.remove('active'));
                
                // Remove active de todos os conteúdos
                profileTabContents.forEach(content => content.classList.remove('active'));
                
                // Adiciona active na tab clicada
                tab.classList.add('active');
                
                // Adiciona active no conteúdo correspondente
                const targetContent = document.querySelector(`.profile-page [data-tab-content="${targetTab}"]`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }
    
    // Abas de filtro de jogos (Jogando, Jogado, Abandonado, Favorito)
    const filterTabs = document.querySelectorAll('.filter-tab');
    const filterContents = document.querySelectorAll('.filter-content');
    
    if (filterTabs.length > 0 && filterContents.length > 0) {
        filterTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetFilter = tab.getAttribute('data-filter');
                
                // Remove active de todas as filter tabs
                filterTabs.forEach(t => t.classList.remove('active'));
                
                // Remove active de todos os conteúdos
                filterContents.forEach(content => content.classList.remove('active'));
                
                // Adiciona active na tab clicada
                tab.classList.add('active');
                
                // Adiciona active no conteúdo correspondente
                const targetContent = document.querySelector(`[data-filter-content="${targetFilter}"]`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }
});

// ==== EDIT PROFILE PAGE - TABS ====
document.addEventListener('DOMContentLoaded', () => {
    // Abas de configurações (Perfil, Autenticação, Avatar, Notificações)
    const settingsTabs = document.querySelectorAll('.settings-tab');
    const settingsTabContents = document.querySelectorAll('.edit-profile-page .tab-content');
    
    if (settingsTabs.length > 0 && settingsTabContents.length > 0) {
        settingsTabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetTab = tab.getAttribute('data-tab');
                
                // Remove active de todas as tabs
                settingsTabs.forEach(t => t.classList.remove('active'));
                
                // Remove active de todos os conteúdos
                settingsTabContents.forEach(content => content.classList.remove('active'));
                
                // Adiciona active na tab clicada
                tab.classList.add('active');
                
                // Adiciona active no conteúdo correspondente
                const targetContent = document.querySelector(`.edit-profile-page [data-tab-content="${targetTab}"]`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
            });
        });
    }
    
    // ==== VALIDAÇÃO DE SENHA NO EDIT PROFILE ====
    const passwordMatchInput = document.querySelector('.password-match-validation');
    if (passwordMatchInput) {
        const matchTarget = document.getElementById(passwordMatchInput.dataset.matchTarget);
        const matchHint = document.getElementById('confirm_password_hint');
        
        function validatePasswordMatch() {
            if (passwordMatchInput.value.length > 0) {
                if (passwordMatchInput.value !== matchTarget.value) {
                    passwordMatchInput.classList.add('password-invalid');
                    passwordMatchInput.classList.remove('password-valid');
                    if (matchHint) {
                        matchHint.style.display = 'block';
                        matchHint.classList.add('invalid');
                        matchHint.classList.remove('valid');
                    }
                    return false;
                } else {
                    passwordMatchInput.classList.remove('password-invalid');
                    passwordMatchInput.classList.add('password-valid');
                    if (matchHint) {
                        matchHint.style.display = 'none';
                        matchHint.classList.remove('invalid');
                    }
                    return true;
                }
            }
            return true;
        }
        
        passwordMatchInput.addEventListener('input', validatePasswordMatch);
        matchTarget.addEventListener('input', validatePasswordMatch);
    }
    
    // ==== VERIFICAÇÃO DE USERNAME NO EDIT PROFILE ====
    const usernameEditInput = document.querySelector('.edit-profile-page .availability-check[data-check-type="username"]');
    if (usernameEditInput) {
        const currentUsername = usernameEditInput.dataset.currentValue;
        const usernameHint = document.getElementById('username-edit-hint');
        let usernameTimeout;
        
        usernameEditInput.addEventListener('input', function() {
            clearTimeout(usernameTimeout);
            const value = this.value.trim();
            
            // Limpar todas as classes primeiro
            this.classList.remove('availability-invalid', 'availability-valid');
            
            // Se o valor for igual ao username atual, não precisa verificar
            if (value === currentUsername) {
                if (usernameHint) {
                    usernameHint.textContent = '3-20 caracteres, apenas letras, números e underscore';
                    usernameHint.classList.remove('invalid', 'valid');
                }
                return;
            }
            
            // Se vazio, não validar
            if (value.length === 0) {
                if (usernameHint) {
                    usernameHint.textContent = '3-20 caracteres, apenas letras, números e underscore';
                    usernameHint.classList.remove('invalid', 'valid');
                }
                return;
            }
            
            // Validação de formato PRIMEIRO
            const isValidFormat = value.length >= 3 && value.length <= 20 && /^[a-zA-Z0-9_]+$/.test(value);
            
            if (!isValidFormat) {
                // FORMATO INVÁLIDO → VERMELHO
                this.classList.add('availability-invalid');
                this.classList.remove('availability-valid');
                if (usernameHint) {
                    usernameHint.textContent = 'Formato inválido: use 3-20 caracteres (letras, números e underscore)';
                    usernameHint.classList.add('invalid');
                    usernameHint.classList.remove('valid');
                }
                return; // Para aqui, não verifica no servidor
            }
            
            // FORMATO VÁLIDO → Verificar disponibilidade no servidor
            usernameTimeout = setTimeout(() => {
                fetch(`includes/check-availability.php?type=username&value=${encodeURIComponent(value)}`)
                .then(response => response.json())
                .then(data => {
                    if (data.available) {
                        // USERNAME DISPONÍVEL → VERDE
                        usernameEditInput.classList.remove('availability-invalid');
                        usernameEditInput.classList.add('availability-valid');
                        if (usernameHint) {
                            usernameHint.textContent = 'Nome de usuário disponível';
                            usernameHint.classList.remove('invalid');
                            usernameHint.classList.add('valid');
                        }
                    } else {
                        // USERNAME JÁ EXISTE → VERMELHO
                        usernameEditInput.classList.add('availability-invalid');
                        usernameEditInput.classList.remove('availability-valid');
                        if (usernameHint) {
                            usernameHint.textContent = 'Este nome de usuário já está em uso';
                            usernameHint.classList.add('invalid');
                            usernameHint.classList.remove('valid');
                        }
                    }
                })
                .catch(error => {
                    console.error('Erro ao verificar username:', error);
                });
            }, 300);
        });
    }
});

// ==== FORGOT PASSWORD MODAL ====
function showForgotPassword() {
    closeModal('loginModal');
    setTimeout(() => {
        openModal('forgotPasswordModal');
    }, 300);
}

function backToLogin() {
    closeModal('forgotPasswordModal');
    setTimeout(() => {
        openModal('loginModal');
    }, 300);
}

// ==== FORGOT PASSWORD FORM HANDLER (aplica a formulários múltiplos) ====
function attachForgotFormHandler(formEl) {
    if (!formEl) return;

    const modalContainer = formEl.closest('.modal');
    // localizar elementos de mensagem dentro do mesmo modal (usa classes, IDs únicos opcionais)
    const messageDiv = modalContainer ? (modalContainer.querySelector('.forgot-password-message') || modalContainer.querySelector('#auth-recover-msg') || modalContainer.querySelector('#standalone-recover-msg')) : null;
    const messageText = modalContainer ? (modalContainer.querySelector('.forgot-password-text') || modalContainer.querySelector('#auth-recover-text') || modalContainer.querySelector('#standalone-recover-text')) : null;
    const submitBtn = formEl.querySelector('button[type="submit"]');

    formEl.addEventListener('submit', function(e) {
        e.preventDefault();

        if (messageDiv) messageDiv.style.display = 'none';

        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.dataset.origHtml = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Enviando...';
        }

        const formData = new FormData(this);

        fetch('includes/password-reset.php', {
            method: 'POST',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.origHtml || '<i class="bi bi-send"></i> Enviar Link';
            }

            if (data.success) {
                if (messageDiv) {
                    messageDiv.className = 'alert alert-success';
                    if (messageText) messageText.textContent = data.message || 'Email enviado com sucesso! Verifique sua caixa de entrada.';
                    messageDiv.style.display = 'block';
                }

                formEl.reset();

                setTimeout(() => {
                    const relatedModal = formEl.closest('.modal');
                    if (relatedModal) closeModal(relatedModal.id);
                    if (messageDiv) setTimeout(() => { messageDiv.style.display = 'none'; }, 300);
                }, 3000);
            } else {
                if (messageDiv) {
                    messageDiv.className = 'alert alert-error';
                    if (messageText) messageText.textContent = data.message || 'Erro ao processar solicitação. Tente novamente.';
                    messageDiv.style.display = 'block';
                }
            }
        })
        .catch(error => {
            console.error('Erro:', error);
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = submitBtn.dataset.origHtml || '<i class="bi bi-send"></i> Enviar Link';
            }
            if (messageDiv && messageText) {
                messageDiv.className = 'alert alert-error';
                messageText.textContent = 'Erro ao enviar email. Tente novamente.';
                messageDiv.style.display = 'block';
            }
        });
    });
}

// Anexar handlers para ambos os formulários (auth modal e standalone modal)
document.addEventListener('DOMContentLoaded', () => {
    const authForm = document.getElementById('authRecoverForm');
    const standaloneForm = document.getElementById('forgotPasswordForm');

    if (authForm) attachForgotFormHandler(authForm);
    if (standaloneForm) attachForgotFormHandler(standaloneForm);
});
