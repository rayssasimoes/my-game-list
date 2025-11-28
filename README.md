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

# Estrutura principal do projeto

```
my-game-list/
├── .env
├── .env.example
├── .git/
├── .gitignore
├── composer.json
├── composer.lock
├── config/
├── database.sql
├── docs/
├── includes/
├── index.php
├── node_modules/
├── pages/
├── public/
├── README.md
├── vendor/
```