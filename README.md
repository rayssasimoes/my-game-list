# MyGameList<div align="center">



Uma aplicação web para organizar sua biblioteca de jogos localmente. Usa a API do IGDB para obter informações de jogos.# 🎮 MyGameList



## Requisitos### *Sua biblioteca pessoal de jogos na web*



- PHP 8+[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)

- MySQL 5.7+[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)

- XAMPP (recomendado para Windows)[![IGDB](https://img.shields.io/badge/IGDB-API-9147FF?style=for-the-badge&logo=twitch&logoColor=white)](https://api-docs.igdb.com/)

[![CSS3](https://img.shields.io/badge/CSS3-Pure-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)

## Instalação

Uma plataforma web para **gamers catalogarem e gerenciarem** sua coleção de jogos com informações em tempo real da **IGDB API**.

### 1. Clone o repositório

[Demo](#-como-executar) • [Instalação](#️-instalação-e-configuração) • [Funcionalidades](#-funcionalidades)

```bash

git clone https://github.com/rayssasimoes/my-game-list.git</div>

cd my-game-list

```---



### 2. Configure as variáveis de ambiente## � Sobre o Projeto



Crie um arquivo `.env` a partir do `.env.example`:**MyGameList** é uma aplicação web que permite aos usuários:



**Windows (PowerShell):**<table>

```powershell<tr>

copy .env.example .env<td width="50%">

```

### 🎯 Principais Recursos

**Linux/Mac:**- 🔐 **Autenticação segura** com criptografia

```bash- � **Busca em tempo real** via IGDB API

cp .env.example .env- 📋 **Catálogo pessoal** de jogos

```- 🏆 **Informações detalhadas** dos jogos

- 📱 **Design responsivo** para todos dispositivos

Edite o `.env` e preencha com suas credenciais:

- `DB_*` - Configurações do banco de dados</td>

- `IGDB_CLIENT_ID` e `IGDB_CLIENT_SECRET` - Obtenha em [Twitch Developers Console](https://dev.twitch.tv/console)<td width="50%">



### 3. Crie o banco de dados### 💡 Objetivo

Criar uma forma simples e elegante de organizar sua biblioteca de jogos, descobrir novos títulos e acompanhar o que você já jogou ou deseja jogar.

**Opção A: Via phpMyAdmin**

1. Acesse http://localhost/phpmyadmin> *Desenvolvido para a disciplina de Programação Web*

2. Clique em "Novo" e crie o banco: `db_mygamelist`

3. Selecione o banco criado</td>

4. Na aba "SQL", cole o conteúdo do arquivo `database.sql` e execute</tr>

</table>

**Opção B: Via linha de comando (Windows)**

```powershell---

cd C:\xampp\mysql\bin

.\mysql.exe -u root -p < C:\xampp\htdocs\my-game-list\database.sql## �️ Tecnologias Utilizadas

```

<table>

### 4. Inicie os servidores<tr>

<td align="center" width="25%">

1. Abra o XAMPP Control Panel<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP"/>

2. Inicie Apache e MySQL<br><strong>PHP 8+</strong>

3. Acesse: http://localhost/my-game-list<br><sub>Backend & Lógica</sub>

</td>

## Acessar pelo celular<td align="center" width="25%">

<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="48" height="48" alt="MySQL"/>

Para testar no celular (mesma rede Wi-Fi):<br><strong>MySQL</strong>

<br><sub>Banco de Dados</sub>

1. No Windows, abra o PowerShell e execute:</td>

```powershell<td align="center" width="25%">

ipconfig<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="48" height="48" alt="CSS3"/>

```<br><strong>CSS3 Puro</strong>

<br><sub>Estilização</sub>

2. Procure o `Endereço IPv4` (exemplo: `192.168.0.105`)</td>

<td align="center" width="25%">

3. No celular, acesse:<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="48" height="48" alt="JavaScript"/>

```<br><strong>JavaScript</strong>

http://SEU_IP/my-game-list<br><sub>Interatividade</sub>

```</td>

</tr>

Exemplo: `http://192.168.0.105/my-game-list`</table>



## Estrutura do projeto### 🔧 Detalhes Técnicos



``````

my-game-list/Backend:      PHP 8+ com PDO (Prepared Statements)

├── config/              # Configurações (database.php)Database:     MySQL 5.7+ com Foreign Keys

├── includes/            # Funções (auth.php, igdb-api.php)API:          IGDB (Internet Game Database) + OAuth 2.0

├── pages/               # Páginas (home.php, my-list.php)Frontend:     HTML5 + CSS3 Puro + JavaScript Vanilla

├── public/Segurança:    password_hash() + password_verify()

│   ├── css/Servidor:     Apache (XAMPP)

│   └── js/```

├── database.sql         # Script de criação do banco

├── .env.example         # Template de configuração---

└── README.md

```## 🤖 Desenvolvimento



## SegurançaEste projeto foi desenvolvido com o auxílio da **IA GitHub Copilot**, com:

- 💭 Engenharia de prompt

- **NUNCA** commite o arquivo `.env` (ele já está no `.gitignore`)- 🏗️ Arquitetura de software

- Se alguma credencial foi exposta, gere novas credenciais na [Twitch Developers Console](https://dev.twitch.tv/console)- 🎨 Decisões de design



## TroubleshootingTodas guiadas por **[@rayssasimoes](https://github.com/rayssasimoes)**



**Erro de conexão MySQL:**---

- Verifique se o MySQL está rodando no XAMPP Control Panel

## 📁 Estrutura do Projeto

**Erro "Access denied":**

- Confirme a senha no `config/database.php` (padrão XAMPP: vazio)```

📦 my-game-list

**Página 404:**┣ 📂 config

- Confirme que a pasta está em `C:\xampp\htdocs\my-game-list`┃ ┗ 📄 database.php          # Configuração PDO

- Acesse exatamente: `http://localhost/my-game-list`┣ 📂 includes

┃ ┣ 📄 auth.php              # Sistema de autenticação

**Jogos não aparecem:**┃ ┣ 📄 igdb-api.php          # Integração com IGDB

- Verifique sua conexão com a internet┃ ┣ 📄 header.php            # Navbar e modais

- Confirme as credenciais IGDB no `.env`┃ ┗ 📄 footer.php            # Footer e scripts

- Abra o Console do navegador (F12) para ver erros┣ 📂 pages

┃ ┣ 📄 home.php              # Página inicial

## Recursos┃ ┗ 📄 my-list.php           # Lista pessoal

┣ 📂 public

- [IGDB API Documentation](https://api-docs.igdb.com/)┃ ┣ 📂 css

- [PHP PDO Manual](https://www.php.net/manual/pt_BR/book.pdo.php)┃ ┃ ┗ 📄 style.css           # Estilos customizados

- [MySQL Documentation](https://dev.mysql.com/doc/)┃ ┗ 📂 js

┃   ┗ 📄 app.js              # JavaScript (modais)

---┣ 📄 index.php               # Router principal

┣ 📄 database.sql            # Script SQL

*Projeto desenvolvido para fins educacionais*┗ 📄 README.md               # Este arquivo

```

---

## ⚙️ Pré-requisitos

Antes de começar, você precisará ter instalado:

<table>
<tr>
<td width="50%">

### 📦 XAMPP (ou similar)
- ✅ PHP 8.0 ou superior
- ✅ MySQL 5.7 ou superior  
- ✅ Apache Server

[Download XAMPP](https://www.apachefriends.org/download.html)

</td>
<td width="50%">

### 🛠️ Ferramentas Adicionais
- ✅ Git (para clonar)
- ✅ Navegador moderno
- ✅ Editor de código (opcional)

[Download Git](https://git-scm.com/downloads)

</td>
</tr>
</table>

---

## � Instalação e Configuração

### **Passo 1️⃣: Clone o Repositório**

```bash
git clone https://github.com/rayssasimoes/my-game-list.git
cd my-game-list
```

<br>

### **Passo 2️⃣: Configure o Banco de Dados**

<details>
<summary><b>📌 Opção A: Via phpMyAdmin (Recomendado para iniciantes)</b></summary>

<br>

1. Abra o **phpMyAdmin**: [`http://localhost/phpmyadmin`](http://localhost/phpmyadmin)

2. Clique em **"Novo"** no menu lateral esquerdo

3. Digite o nome: `db_mygamelist`

4. Clique em **"Criar"**

5. Selecione o banco recém-criado

6. Vá na aba **"SQL"**

7. Abra o arquivo `database.sql` do projeto no Bloco de Notas

8. **Copie todo o conteúdo** do arquivo

9. **Cole** no campo SQL do phpMyAdmin

10. Clique em **"Executar"**

✅ **Pronto!** As tabelas foram criadas com sucesso!

</details>

<details>
<summary><b>📌 Opção B: Via Linha de Comando (Para usuários avançados)</b></summary>

<br>

**Windows (PowerShell):**
```powershell
cd C:\xampp\mysql\bin
.\mysql.exe -u root -p < C:\xampp\htdocs\my-game-list\database.sql
```

**Mac/Linux (Terminal):**
```bash
mysql -u root -p < /caminho/para/my-game-list/database.sql
```

✅ **Pronto!** As tabelas foram criadas!

</details>

<br>

### **Passo 3️⃣: Verifique a Conexão**

Abra o arquivo `config/database.php` e confirme as configurações:

```php
$host = 'localhost';      // ✅ Host do MySQL
$dbname = 'db_mygamelist'; // ✅ Nome do banco
$username = 'root';        // ✅ Usuário (padrão XAMPP)
$password = '';            // ✅ Senha (vazia no XAMPP)
```

> 💡 **Dica:** No XAMPP padrão, o usuário é `root` e a senha é **vazia**.

<br>

### **Passo 4️⃣: Inicie os Servidores**

1. Abra o **XAMPP Control Panel**
2. Clique em **"Start"** no **Apache** ✅
3. Clique em **"Start"** no **MySQL** ✅
4. Ambos devem ficar com **fundo verde**

<div align="center">

| Serviço | Status | Porta |
|---------|--------|-------|
| Apache  | 🟢 Running | 80 |
| MySQL   | 🟢 Running | 3306 |

</div>

---

## ▶️ Como Executar

### 💻 **Acesso Local (Computador)**

1. Certifique-se de que **Apache** e **MySQL** estão rodando

2. Abra seu navegador favorito

3. Acesse:

```
🔗 http://localhost/my-game-list
```

4. ✅ **Pronto!** O site deve carregar com os jogos populares!

<br>

### 📱 **Testando no Celular (Mesma Rede Wi-Fi)**

Quer testar a responsividade em dispositivos móveis? É fácil!

<details>
<summary><b>📱 Clique aqui para ver o passo a passo</b></summary>

<br>

#### **1. Descubra seu IP Local**

**Windows (PowerShell):**
```powershell
ipconfig
```
Procure por **`Endereço IPv4`** na seção `Adaptador de Rede Sem Fio Wi-Fi`

**Mac/Linux (Terminal):**
```bash
ifconfig | grep "inet "
```

📝 **Exemplo de resultado:** `192.168.0.105`

<br>

#### **2. Conecte o Celular na Mesma Rede**

Certifique-se de que seu celular está conectado à **mesma rede Wi-Fi** que seu computador.

<br>

#### **3. Acesse no Navegador do Celular**

Digite no navegador:

```
🔗 http://SEU_IP_LOCAL/my-game-list
```

**Exemplo real:**
```
🔗 http://192.168.0.105/my-game-list
```

<br>

#### **4. Teste a Responsividade! 🎉**

Experimente:
- ✅ Rotacionar a tela (modo retrato/paisagem)
- ✅ Fazer login
- ✅ Buscar jogos
- ✅ Adicionar jogos à sua lista
- ✅ Abrir o menu dropdown

</details>

---

## 🔑 Credenciais da IGDB API

As credenciais da **IGDB API** já estão configuradas no projeto:

```php
// Arquivo: includes/igdb-api.php
Client ID:     8moen985l6yy84pd61d7d4net3k26g
Client Secret: bwwru0snjnk13e5ko1aoyi2clbucu3
```

> ⚠️ **Nota de Segurança:** Em produção, mova essas credenciais para variáveis de ambiente ou arquivo `.env`

> 💡 **Obter suas próprias credenciais:** [Twitch Developers Console](https://dev.twitch.tv/console)

---

## 🐛 Resolução de Problemas

<details>
<summary><b>❌ Erro: "Connection refused" ou "Can't connect to MySQL"</b></summary>

<br>

**Causa:** MySQL não está rodando

**Solução:**
1. Abra o **XAMPP Control Panel**
2. Verifique se o MySQL está com status **verde**
3. Se não, clique em **"Start"**
4. Teste novamente

</details>

<details>
<summary><b>❌ Erro: "Access denied for user 'root'@'localhost'"</b></summary>

<br>

**Causa:** Senha do MySQL incorreta

**Solução:**
1. Abra `config/database.php`
2. Verifique a linha `$password`
3. No XAMPP padrão, deve estar **vazio**: `$password = '';`
4. Salve e teste novamente

</details>

<details>
<summary><b>❌ Página em branco ou erro 404</b></summary>

<br>

**Possíveis causas e soluções:**

1. **Apache não está rodando**
   - Abra XAMPP Control Panel
   - Inicie o Apache

2. **Caminho incorreto**
   - Verifique se a pasta está em: `C:\xampp\htdocs\my-game-list`
   - Acesse: `http://localhost/my-game-list` (exatamente assim)

3. **Porta 80 ocupada**
   - Feche Skype ou outros programas que usam porta 80
   - Ou configure o Apache para usar outra porta

</details>

<details>
<summary><b>❌ Não consigo acessar pelo celular</b></summary>

<br>

**Checklist:**

- [ ] Computador e celular estão na **mesma rede Wi-Fi**?
- [ ] O IP está correto? (Use `ipconfig` para confirmar)
- [ ] O Firewall está bloqueando? (Teste desativando temporariamente)
- [ ] Apache está rodando?
- [ ] Você está acessando `http://` e não `https://`?

**Teste rápido:**
- Tente acessar apenas `http://SEU_IP` no celular
- Se aparecer a página do XAMPP, o problema é no caminho do projeto

</details>

<details>
<summary><b>❌ Jogos não aparecem na página inicial</b></summary>

<br>

**Possíveis causas:**

1. **Sem conexão com a internet**
   - A API IGDB precisa de internet para funcionar
   - Verifique sua conexão

2. **Credenciais da API expiraram**
   - As credenciais podem ter um limite de requisições
   - Obtenha novas em: [Twitch Developers](https://dev.twitch.tv/)

3. **Erro JavaScript**
   - Abra o Console (F12)
   - Verifique se há erros em vermelho
   - Compartilhe o erro para obter ajuda

</details>

---

## 📚 Recursos Adicionais

<div align="center">

| Recurso | Link |
|---------|------|
| 📖 IGDB API Docs | [api-docs.igdb.com](https://api-docs.igdb.com/) |
| 🐘 PHP PDO Manual | [php.net/pdo](https://www.php.net/manual/pt_BR/book.pdo.php) |
| 🗄️ MySQL Docs | [dev.mysql.com/doc](https://dev.mysql.com/doc/) |
| 🎮 phpMyAdmin | [localhost/phpmyadmin](http://localhost/phpmyadmin) |
| ❓ XAMPP FAQ | [apachefriends.org/faq](https://www.apachefriends.org/faq.html) |

</div>

---

## 🐛 Troubleshooting (Resolução de Problemas)

### ❌ Erro: "Connection refused" ou "Can't connect to MySQL"
**Solução**: Verifique se o MySQL está rodando no XAMPP Control Panel.

### ❌ Erro: "Access denied for user 'root'@'localhost'"
**Solução**: Verifique a senha no arquivo `config/database.php`. No XAMPP padrão, deixe vazio (`''`).

### ❌ Página em branco ou erro 404
**Solução**: 
- Verifique se o Apache está rodando
- Confirme que a pasta está em `C:\xampp\htdocs\my-game-list`
- Acesse exatamente: `http://localhost/my-game-list`

### ❌ Não consigo acessar pelo celular
**Solução**:
- Certifique-se de estar na mesma rede Wi-Fi
- Verifique se o firewall não está bloqueando (desative temporariamente para testar)
- Confirme o IP com `ipconfig` novamente
- Teste acessar `http://SEU_IP` (sem o projeto) para ver se o Apache responde

### ❌ Jogos não aparecem na página inicial
**Solução**:
- Verifique sua conexão com a internet (a API precisa de internet)
- Abra o Console do Navegador (F12) e veja se há erros
- As credenciais da API podem ter expirado (solicite novas no [Twitch Developers](https://dev.twitch.tv/))

---

## 📚 Recursos Adicionais

### APIs e Documentações
- [IGDB API Documentation](https://api-docs.igdb.com/)
- [PHP PDO Manual](https://www.php.net/manual/pt_BR/book.pdo.php)
- [MySQL Documentation](https://dev.mysql.com/doc/)

### Ferramentas Úteis
- [phpMyAdmin](http://localhost/phpmyadmin) - Gerenciamento visual do banco
- [XAMPP FAQ](https://www.apachefriends.org/faq.html) - Perguntas frequentes sobre XAMPP

---

## � Licença

Este projeto foi desenvolvido para fins educacionais como parte da disciplina de Programação Web.