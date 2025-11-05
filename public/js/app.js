/* =========================================
   MY GAME LIST - JAVASCRIPT
   Modais, Dropdown e Alerts
   ========================================= */

// ==== MODAL ====
function openModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.add('show');
        document.body.style.overflow = 'hidden';
    }
}

function closeModal(modalId) {
    const modal = document.getElementById(modalId);
    if (modal) {
        modal.classList.remove('show');
        document.body.style.overflow = '';
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
});

// ==== SEARCH AUTOCOMPLETE ====
function initSearchAutocomplete() {
    const searchInput = document.querySelector('.search-input');
    if (!searchInput) return;

    const searchContainer = document.querySelector('.search-container');
    
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
            return;
        }

        // Debounce: espera 300ms após parar de digitar
        debounceTimer = setTimeout(() => {
            fetchSearchResults(query, dropdown);
        }, 300);
    });

    // Fechar dropdown ao clicar fora
    document.addEventListener('click', function(e) {
        if (!searchContainer.contains(e.target)) {
            dropdown.classList.remove('show');
        }
    });

    // Fechar dropdown com ESC
    searchInput.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            dropdown.classList.remove('show');
        }
    });
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
