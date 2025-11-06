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

```powershell
copy .env.example .env
```

Edite o `.env` e preencha suas credenciais:
- DB_HOST, DB_NAME, DB_USER, DB_PASS
- IGDB_CLIENT_ID e IGDB_CLIENT_SECRET (obtenha em https://dev.twitch.tv/console)

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

## Executar o projeto

Inicie Apache e MySQL no XAMPP e acesse:

```
http://localhost/my-game-list
```

## Acessar pelo celular

Execute `ipconfig` no Windows e copie o IPv4 (exemplo: 192.168.0.105)

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
