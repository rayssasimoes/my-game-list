# MyGameList

Aplicação web para organizar sua biblioteca de jogos usando a API do IGDB.

## Tecnologias

- PHP 8+
- MySQL
- JavaScript
- CSS
- IGDB API

## Instalação

Clone o repositório:

```bash
git clone https://github.com/rayssasimoes/my-game-list.git
cd my-game-list
```

Configure o arquivo `.env`:

**Windows:**
```powershell
copy .env.example .env
```

**Linux/Mac:**
```bash
cp .env.example .env
```

Edite o `.env` e preencha suas credenciais:
- DB_HOST, DB_NAME, DB_USER, DB_PASS
- IGDB_CLIENT_ID e IGDB_CLIENT_SECRET

## Obter credenciais da IGDB

1. Acesse https://dev.twitch.tv/console
2. Faça login com sua conta Twitch (ou crie uma)
3. Clique em "Register Your Application"
4. Preencha:
   - Name: MyGameList (ou qualquer nome)
   - OAuth Redirect URLs: http://localhost
   - Category: Website Integration
5. Clique em "Create"
6. Copie o "Client ID"
7. Clique em "New Secret" e copie o "Client Secret"
8. Cole ambos no arquivo `.env`

## Criar banco de dados

**Via phpMyAdmin:**

1. Abra http://localhost/phpmyadmin
2. Clique em "Novo" no menu lateral esquerdo
3. Digite o nome do banco: `db_mygamelist`
4. Clique em "Criar"
5. Selecione o banco criado
6. Clique na aba "SQL"
7. Copie todo o conteúdo do arquivo `database.sql`
8. Cole no campo SQL e clique em "Executar"

**Via terminal (Windows):**

```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -p < C:\xampp\htdocs\my-game-list\database.sql
```

**Via terminal (Linux/Mac):**

```bash
mysql -u root -p < /caminho/para/my-game-list/database.sql
```

## Executar o projeto

Inicie Apache e MySQL no XAMPP e acesse:

```
http://localhost/my-game-list
```

## Site publicado (link)

- Link: `mygamelist.infinityfreeapp.com`

## Acessar pelo celular

**Windows:**

Execute `ipconfig` e copie o IPv4 (exemplo: 192.168.0.105)

**Linux/Mac:**

Execute `ifconfig` ou `ip addr` e copie o IP local

No celular (mesma rede Wi-Fi), acesse:

```
http://192.168.0.105/my-game-list
```

## Estrutura

```
my-game-list/
├── config/
├── includes/
├── pages/
├── public/
│   ├── css/
│   └── js/
├── database.sql
├── .env.example
└── README.md
```

## Problemas comuns

- MySQL não conecta: Verifique se está rodando no XAMPP
- Jogos não aparecem: Confira as credenciais IGDB no `.env`
- Erro 404: Confirme que a pasta está em C:\xampp\htdocs\my-game-list

## Segurança

Nunca commite o arquivo `.env` - ele já está no .gitignore
