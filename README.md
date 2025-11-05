<div align="center">

# 🎮 MyGameList

### *Sua biblioteca pessoal de jogos na web*

[![PHP](https://img.shields.io/badge/PHP-8.0+-777BB4?style=for-the-badge&logo=php&logoColor=white)](https://www.php.net/)
[![MySQL](https://img.shields.io/badge/MySQL-5.7+-4479A1?style=for-the-badge&logo=mysql&logoColor=white)](https://www.mysql.com/)
[![IGDB](https://img.shields.io/badge/IGDB-API-9147FF?style=for-the-badge&logo=twitch&logoColor=white)](https://api-docs.igdb.com/)
[![CSS3](https://img.shields.io/badge/CSS3-Pure-1572B6?style=for-the-badge&logo=css3&logoColor=white)](https://developer.mozilla.org/en-US/docs/Web/CSS)

Uma plataforma web para **gamers catalogarem e gerenciarem** sua coleção de jogos com informações em tempo real da **IGDB API**.

[Demo](#-como-executar) • [Instalação](#️-instalação-e-configuração) • [Funcionalidades](#-funcionalidades)

</div>

---

## � Sobre o Projeto

**MyGameList** é uma aplicação web que permite aos usuários:

<table>
<tr>
<td width="50%">

### 🎯 Principais Recursos
- 🔐 **Autenticação segura** com criptografia
- � **Busca em tempo real** via IGDB API
- 📋 **Catálogo pessoal** de jogos
- 🏆 **Informações detalhadas** dos jogos
- 📱 **Design responsivo** para todos dispositivos

</td>
<td width="50%">

### 💡 Objetivo
Criar uma forma simples e elegante de organizar sua biblioteca de jogos, descobrir novos títulos e acompanhar o que você já jogou ou deseja jogar.

> *Desenvolvido para a disciplina de Programação Web*

</td>
</tr>
</table>

---

## �️ Tecnologias Utilizadas

<table>
<tr>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/php/php-original.svg" width="48" height="48" alt="PHP"/>
<br><strong>PHP 8+</strong>
<br><sub>Backend & Lógica</sub>
</td>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/mysql/mysql-original.svg" width="48" height="48" alt="MySQL"/>
<br><strong>MySQL</strong>
<br><sub>Banco de Dados</sub>
</td>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/css3/css3-original.svg" width="48" height="48" alt="CSS3"/>
<br><strong>CSS3 Puro</strong>
<br><sub>Estilização</sub>
</td>
<td align="center" width="25%">
<img src="https://cdn.jsdelivr.net/gh/devicons/devicon/icons/javascript/javascript-original.svg" width="48" height="48" alt="JavaScript"/>
<br><strong>JavaScript</strong>
<br><sub>Interatividade</sub>
</td>
</tr>
</table>

### 🔧 Detalhes Técnicos

```
Backend:      PHP 8+ com PDO (Prepared Statements)
Database:     MySQL 5.7+ com Foreign Keys
API:          IGDB (Internet Game Database) + OAuth 2.0
Frontend:     HTML5 + CSS3 Puro + JavaScript Vanilla
Segurança:    password_hash() + password_verify()
Servidor:     Apache (XAMPP)
```

---

## 🤖 Desenvolvimento

Este projeto foi desenvolvido com o auxílio da **IA GitHub Copilot**, com:
- 💭 Engenharia de prompt
- 🏗️ Arquitetura de software
- 🎨 Decisões de design

Todas guiadas por **[@rayssasimoes](https://github.com/rayssasimoes)**

---

## 📁 Estrutura do Projeto

```
📦 my-game-list
┣ 📂 config
┃ ┗ 📄 database.php          # Configuração PDO
┣ 📂 includes
┃ ┣ 📄 auth.php              # Sistema de autenticação
┃ ┣ 📄 igdb-api.php          # Integração com IGDB
┃ ┣ 📄 header.php            # Navbar e modais
┃ ┗ 📄 footer.php            # Footer e scripts
┣ 📂 pages
┃ ┣ 📄 home.php              # Página inicial
┃ ┗ 📄 my-list.php           # Lista pessoal
┣ 📂 public
┃ ┣ 📂 css
┃ ┃ ┗ 📄 style.css           # Estilos customizados
┃ ┗ 📂 js
┃   ┗ 📄 app.js              # JavaScript (modais)
┣ 📄 index.php               # Router principal
┣ 📄 database.sql            # Script SQL
┗ 📄 README.md               # Este arquivo
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

## ✨ Funcionalidades

<table>
<tr>
<td width="50%" valign="top">

### 🔐 **Autenticação**
- ✅ Cadastro de novos usuários
- ✅ Login seguro com senha criptografada
- ✅ Sistema de sessões
- ✅ Logout
- ✅ Proteção de rotas privadas

### 🎮 **Catálogo de Jogos**
- ✅ Listagem de jogos populares (top rated)
- ✅ Busca em tempo real por nome
- ✅ Informações detalhadas
  - 🖼️ Capa do jogo
  - 🎯 Gêneros
  - 🎮 Plataformas
  - ⭐ Rating da comunidade
- ✅ Cache inteligente (6 horas)

</td>
<td width="50%" valign="top">

### 📋 **Lista Pessoal**
- ✅ Adicionar jogos ao catálogo pessoal
- ✅ Visualizar todos os jogos salvos
- ✅ Status dos jogos:
  - 🎮 **Jogando**
  - ✅ **Completado**
  - ⭐ **Quero Jogar**
  - ❌ **Desisti**
- ✅ Contador de jogos

### 🎨 **Interface**
- ✅ Design dark mode elegante
- ✅ Navbar responsiva
- ✅ Dropdown com fundo unificado
- ✅ Modais para login/cadastro
- ✅ Alerts com auto-dismiss
- ✅ Grid responsivo de jogos

</td>
</tr>
</table>

---

## 🎨 Design & Paleta de Cores

<div align="center">

| Elemento | Cor | Preview |
|----------|-----|---------|
| Background Principal | `#1a1a1a` | ![#1a1a1a](https://via.placeholder.com/100x30/1a1a1a/1a1a1a.png) |
| Navbar | `#212529` | ![#212529](https://via.placeholder.com/100x30/212529/212529.png) |
| Dropdown Hover | `#4A5B87` | ![#4A5B87](https://via.placeholder.com/100x30/4A5B87/4A5B87.png) |
| Accent (Botões) | `#E93D82` | ![#E93D82](https://via.placeholder.com/100x30/E93D82/E93D82.png) |
| Texto Principal | `#ffffff` | ![#ffffff](https://via.placeholder.com/100x30/ffffff/ffffff.png) |

</div>

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

## 🤝 Contribuindo

Contribuições são **muito bem-vindas**! 💜

Se você deseja melhorar este projeto:

1. 🍴 **Fork** o repositório
2. 🌿 Crie uma **branch** para sua feature
   ```bash
   git checkout -b feature/MinhaFeature
   ```
3. 💾 **Commit** suas mudanças
   ```bash
   git commit -m 'feat: Adiciona MinhaFeature'
   ```
4. 📤 Faça **push** para a branch
   ```bash
   git push origin feature/MinhaFeature
   ```
5. 🔃 Abra um **Pull Request**

### 💡 Ideias para Contribuir

- 🌐 Adicionar internacionalização (PT/EN)
- 📊 Dashboard com estatísticas
- 👥 Sistema de amigos
- 🏆 Sistema de conquistas
- 📱 Progressive Web App (PWA)
- 🔔 Notificações
- 🎨 Temas customizáveis

---

## 📝 Licença

Este projeto foi desenvolvido para fins **educacionais** como parte da disciplina de Programação Web.

---

## 👩‍💻 Autora

<div align="center">

<img src="https://github.com/rayssasimoes.png" width="100" height="100" style="border-radius: 50%;" alt="Rayssa Simões"/>

### **Rayssa Simões**

[![GitHub](https://img.shields.io/badge/GitHub-rayssasimoes-181717?style=for-the-badge&logo=github)](https://github.com/rayssasimoes)
[![LinkedIn](https://img.shields.io/badge/LinkedIn-Connect-0A66C2?style=for-the-badge&logo=linkedin)](https://www.linkedin.com/in/rayssasimoes)

</div>

---

## 🙏 Agradecimentos

<div align="center">

Agradecimentos especiais a:

**GitHub Copilot** • **IGDB** • **XAMPP** • **Comunidade Open Source**

<br>

---

<br>

### ⭐ Se este projeto foi útil para você, considere dar uma estrela!

<br>

**Desenvolvido com 💜 por [Rayssa Simões](https://github.com/rayssasimoes)**

*Novembro 2025*

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

## 👥 Contribuindo

Contribuições são bem-vindas! Se você deseja melhorar este projeto:

1. Fork o repositório
2. Crie uma branch para sua feature (`git checkout -b feature/MinhaFeature`)
3. Commit suas mudanças (`git commit -m 'Adiciona MinhaFeature'`)
4. Push para a branch (`git push origin feature/MinhaFeature`)
5. Abra um Pull Request

---

## � Licença

Este projeto foi desenvolvido para fins educacionais como parte da disciplina de Programação Web.

---

## 👩‍💻 Autor

**Rayssa Simões**
- GitHub: [@rayssasimoes](https://github.com/rayssasimoes)
- Projeto: [my-game-list](https://github.com/rayssasimoes/my-game-list)

---

## 🙏 Agradecimentos

- **GitHub Copilot** - Assistência no desenvolvimento
- **IGDB** - API de dados de jogos
- **XAMPP** - Ambiente de desenvolvimento
- **Comunidade Open Source** - Inspiração e recursos

---

<div align="center">

**⭐ Se este projeto foi útil para você, considere dar uma estrela no GitHub! ⭐**

Desenvolvido com 💜 por **Rayssa Simões**

</div>