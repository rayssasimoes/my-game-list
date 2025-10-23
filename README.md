# MyGameList 🎮

📄 **Sobre o Projeto**
Uma plataforma para gamers catalogarem os jogos que já jogaram, estão jogando, ou desejam comprar, com informações (capa, gênero, plataformas) buscadas automaticamente. Este projeto está sendo desenvolvido para a disciplina de Programação Web.

🤖 **Sobre o Desenvolvimento**
Este projeto foi desenvolvido com o auxílio da IA Gemini, com engenharia de prompt e arquitetura de projeto guiadas por mim.

🚀 **Tecnologias e Frameworks**
Este projeto foi construído com o seguinte stack:

- **Backend**: Laravel (PHP)
- **Frontend**: Bootstrap 5, Sass (SCSS), Alpine.js
- **Build Tool**: Vite
- **Banco de Dados**: MySQL
- **API Externa**: RAWG Video Games Database API

⚙️ **Instalação e Configuração**
Siga os passos abaixo para rodar o projeto em seu ambiente de desenvolvimento local.

**1. Clone o repositório:**
```bash
git clone [URL_DO_SEU_REPOSITÓRIO_AQUI]
cd my-game-list
```

**2. Instale as dependências:**
```bash
# Instalar dependências do PHP (backend)
composer install

# Instalar dependências do Node.js (frontend)
npm install
```

**3. Configure o Ambiente:**
```bash
# Crie seu arquivo de ambiente a partir do exemplo
cp .env.example .env

# Gere a chave da aplicação
php artisan key:generate
```

**4. Crie o Banco de Dados:**
- Abra seu cliente MySQL (como o phpMyAdmin do XAMPP).
- Crie um novo banco de dados com o nome que preferir (ex: `mygamelist_db`).

**5. Configure a Conexão com o Banco de Dados:**
- Abra o arquivo `.env` que você criou no passo 3.
- Modifique as seguintes linhas com as informações do seu banco de dados:
```
DB_DATABASE=mygamelist_db
DB_USERNAME=root
DB_PASSWORD=
```

**6. Execute as Migrations:**
Este comando criará todas as tabelas necessárias no seu banco.
```bash
php artisan migrate
```

▶️ **Como Rodar o Projeto**
Para rodar a aplicação, você precisará de dois terminais abertos na pasta do projeto.

- No **Terminal 1**, inicie o servidor do Laravel:
```bash
php artisan serve
```

- No **Terminal 2**, inicie o servidor do Vite para compilar os assets (CSS e JS):
```bash
npm run dev
```

Agora, abra seu navegador e acesse a URL fornecida pelo `php artisan serve` (geralmente http://127.0.0.1:8000).