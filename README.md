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

**Hospedagem pública (deploy):**
- **URL:** `https://mygamelist.infinityfreeapp.com`  
   (clique ou cole no navegador para abrir o site publicado)

**Resumo do que o projeto faz:**
- Cadastro / login de usuários
- Busca e import de informações da IGDB
- Lista pessoal de jogos com status e nota
- Favoritos, busca com autocomplete e páginas públicas de jogos

**Instalação / Configuração (resumo rápido)**

- Clone o repositório:

```bash
git clone https://github.com/rayssasimoes/my-game-list.git
cd my-game-list
```

- Crie o arquivo de configurações (local ou produção):

```powershell
copy .env.example .env    # Windows
```
```bash
cp .env.example .env      # Linux / macOS
```

- Edite o ` .env` (ou `config/credentials.php`) com as credenciais reais do seu ambiente:
   - **DB_HOST**, **DB_NAME**, **DB_USER**, **DB_PASS**
   - **IGDB_CLIENT_ID**, **IGDB_CLIENT_SECRET**
   - **SMTP_*** (se pretende enviar emails)

- Importe o schema do banco (use `database.sql` ou `database-infinityfree.sql` conforme seu host):
   - Via phpMyAdmin: abra a aba SQL e cole o conteúdo de `database-infinityfree.sql` ou `database.sql` e execute.
   - Via terminal MySQL: `mysql -u USER -p DB_NAME < database.sql`

**Observação importante sobre produção / hospedagem gratuita**
- Muitos provedores gratuitos (ex.: InfinityFree) bloqueiam SMTP de saída. Para envio de emails use um serviço SMTP autorizado ou API de terceiros (SendGrid, Mailgun, Postmark) e ajuste `.env`.
- Nunca comite `config/credentials.php` nem seu `.env` com credenciais reais.

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

**Principais arquivos e onde olhar ao migrar**
- `config/database.php` : conecta ao MySQL (usa `getenv()` / `config/credentials.php`).
- `config/credentials.example.php` : modelo para criar `config/credentials.php` no servidor.
- `includes/auth.php` : lógica de autenticação (login, register, getUser).
- `pages/edit-profile.php` : edição do perfil do usuário (cuidado com colunas do DB).
- `includes/password-reset.php` : fluxo de recuperação de senha (PHPMailer / SMTP).

**Comandos úteis**
- Rodar composer (instalar dependências):
   ```bash
   composer install
   ```
- Verificar versão PHP no servidor:
   ```bash
   php -v
   ```

**Dicas após deploy**
- Ajuste `config/credentials.php` no servidor com `putenv()` ou preencha variáveis de ambiente no painel da hospedagem.
- Verifique permissões de `uploads/` (diretório `public/uploads/avatars/`) — normalmente `755` para pastas e `644` para arquivos.
- Remova temporariamente `ini_set('display_errors', 1)` após depuração (não deixar em produção).

---

Se quiser, eu posso:
- Adicionar um arquivo `docs/DEPLOY.md` com passos detalhados para InfinityFree (incluindo criação do banco, import SQL, criação de `config/credentials.php` e ajustes SMTP).
- Adicionar entrada no `.gitignore` para `config/credentials.php` e `.env` (recomendado).

Diga se quer que eu crie o `docs/DEPLOY.md` e/ou adicione o `.gitignore` para `config/credentials.php` e `.env` e eu aplico os patches.