import './bootstrap';
import 'bootstrap';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Barra de Carregamento
document.addEventListener('DOMContentLoaded', function() {
    const loadingBar = document.getElementById('loading-bar');
    
    // Função para iniciar o carregamento
    function startLoading() {
        loadingBar.classList.remove('complete');
        loadingBar.classList.add('loading');
    }
    
    // Função para completar o carregamento
    function completeLoading() {
        loadingBar.classList.remove('loading');
        loadingBar.classList.add('complete');
        
        // Remove a classe complete após a animação
        setTimeout(() => {
            loadingBar.classList.remove('complete');
        }, 1000);
    }
    
    // Intercepta cliques em links (exceto âncoras #)
    document.addEventListener('click', function(e) {
        const link = e.target.closest('a');
        
        if (link && link.href && !link.href.includes('#') && link.target !== '_blank') {
            // Verifica se é uma navegação interna (mesmo domínio)
            const url = new URL(link.href);
            if (url.origin === window.location.origin) {
                startLoading();
            }
        }
    });
    
    // Intercepta submissão de formulários
    document.addEventListener('submit', function(e) {
        const form = e.target;
        
        // Ignora formulários com target="_blank" ou method="POST" sem confirmação
        if (form.target !== '_blank') {
            startLoading();
        }
    });
    
    // Completa o carregamento quando a página terminar de carregar
    window.addEventListener('load', completeLoading);
    
    // Completa o carregamento se o usuário voltar usando botão de voltar
    window.addEventListener('pageshow', function(event) {
        if (event.persisted) {
            completeLoading();
        }
    });
});
