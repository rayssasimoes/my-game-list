# MyGameList

Aplicação web para organizar sua biblioteca de jogos usando a API do IGDB.

## Tecnologias

## **My Game List**

Aplicação web simples para organizar sua biblioteca de jogos pessoais e acompanhar status (jogando, finalizado, quero jogar etc.). Usa a API do IGDB para obter informações dos jogos e oferece recursos básicos como cadastro, login, lista pessoal, favoritos e perfil de usuário.

**Tecnologias:**
- **PHP** (padrão compatível com PHP 7.4+ / 8.x)
- **MySQL**
- **JavaScript** (front-end mínimo)
- **CSS** (estilos em `public/css`)
- Integração com **IGDB API**

**Resumo do que o projeto faz:**
- Cadastro / login de usuários
- Busca e import de informações da IGDB
- Lista pessoal de jogos com status e nota
- Favoritos, busca com autocomplete e páginas públicas de jogos

**Hospedagem pública (deploy):**
- **URL:** https://mygamelist.infinityfreeapp.com

**Estrutura principal do projeto**

```
my-game-list/
├── config/                 # configurações: database.php, credentials.example.php
├── includes/               # endpoints e helpers (auth, add-to-list, search, modals, etc.)
├── pages/                  # views PHP (home.php, game.php, edit-profile.php, etc.)
├── public/                 # assets públicos
│   ├── css/
│   └── js/
├── uploads/                # uploads (avatars)
├── vendor/                 # dependências (composer)
├── database.sql            # esquema do banco (local)
├── database-infinityfree.sql # esquema adaptado para InfinityFree
├── .env.example            # template de variáveis de ambiente
└── README.md               # você está aqui
```