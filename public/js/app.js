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

// ==== MY LIST PAGE - TAB SWITCHING ====
document.addEventListener('DOMContentLoaded', () => {
    const tabs = document.querySelectorAll('.list-tab');
    const tabContents = document.querySelectorAll('.tab-content');
    
    if (tabs.length > 0 && tabContents.length > 0) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                const targetStatus = tab.getAttribute('data-status');
                
                // Remove active de todas as tabs
                tabs.forEach(t => t.classList.remove('active'));
                
                // Remove active de todos os conteúdos
                tabContents.forEach(content => content.classList.remove('active'));
                
                // Adiciona active na tab clicada
                tab.classList.add('active');
                
                // Adiciona active no conteúdo correspondente
                const targetContent = document.getElementById(`tab-${targetStatus}`);
                if (targetContent) {
                    targetContent.classList.add('active');
                }
                
                // Atualiza URL hash
                window.location.hash = targetStatus;
            });
        });
        
        // Verifica se há hash na URL ao carregar
        const hash = window.location.hash.substring(1);
        if (hash) {
            const targetTab = document.querySelector(`.list-tab[data-status="${hash}"]`);
            if (targetTab) {
                targetTab.click();
            }
        }
    }
});

// ==== MY LIST PAGE - FILTER TOGGLE ====
document.addEventListener('DOMContentLoaded', () => {
    const filterToggle = document.getElementById('filterToggle');
    const filterTabs = document.getElementById('filterTabs');
    
    if (filterToggle && filterTabs) {
        // Inicialmente esconder as tabs
        filterTabs.style.display = 'none';
        
        filterToggle.addEventListener('click', () => {
            const isVisible = filterTabs.style.display !== 'none';
            
            if (isVisible) {
                filterTabs.style.display = 'none';
                filterToggle.classList.remove('active');
            } else {
                filterTabs.style.display = 'flex';
                filterToggle.classList.add('active');
            }
        });
    }
});

// ==== MY LIST PAGE - GAME ACTIONS ====
function editGame(gameId) {
    // TODO: Abrir modal de edição do jogo
    console.log('Edit game:', gameId);
    alert('Funcionalidade de edição em desenvolvimento');
}

function removeGame(gameId) {
    // Confirmar remoção
    if (confirm('Tem certeza que deseja remover este jogo da sua lista?')) {
        // TODO: Fazer requisição para remover o jogo
        console.log('Remove game:', gameId);
        alert('Funcionalidade de remoção em desenvolvimento');
    }
}

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
                console.log('More options for game:', gameId);
                alert('Menu de opções em desenvolvimento');
                return;
            }
            
            // Adicionar jogo à lista com o status específico
            addGameToList(gameId, action, btn, gameName, gameCover);
        });
    });
});

function addGameToList(gameId, status, btnElement, gameName, gameCover) {
    // Mostrar feedback visual
    btnElement.disabled = true;
    btnElement.style.opacity = '0.6';
    
    console.log('Tentando adicionar jogo:', gameId, 'com status:', status);
    console.log('Nome:', gameName, 'Cover:', gameCover);
    
    // Caminho relativo à raiz do projeto
    const url = 'includes/add-to-list.php';
    
    console.log('URL da requisição:', url);
    
    // Fazer requisição AJAX para adicionar o jogo
    fetch(url, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `game_id=${gameId}&status=${status}&game_name=${encodeURIComponent(gameName)}&game_cover=${encodeURIComponent(gameCover)}`
    })
    .then(response => {
        console.log('Response status:', response.status);
        return response.text().then(text => {
            console.log('Response text:', text);
            try {
                return JSON.parse(text);
            } catch (e) {
                console.error('Erro ao fazer parse do JSON:', e);
                console.error('Resposta recebida:', text);
                throw new Error('Resposta inválida do servidor');
            }
        });
    })
    .then(data => {
        console.log('Data recebida:', data);
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
            // Erro ao adicionar
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
    // Profile main tabs (Perfil, Jogos, Atividade)
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
    
    // Games filter tabs (Jogando, Jogado, Abandonado, Favorito)
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
    // Settings tabs (Perfil, Autenticação, Avatar, Notificações)
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
});
