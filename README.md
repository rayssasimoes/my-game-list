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

📱 **Testando no Celular**
Para testar o projeto no seu celular na mesma rede Wi-Fi:

**1. Descubra seu IP local:**
```bash
# Windows
ipconfig

# Mac/Linux
ifconfig
```

**2. Configure a variável de ambiente:**
Edite o arquivo `.env` e adicione:
```
VITE_HMR_HOST=SEU_IP_LOCAL
```
Exemplo: `VITE_HMR_HOST=192.168.0.100`

**3. Compile os assets:**
```bash
npm run build
```

**4. Inicie o servidor Laravel:**
```bash
php artisan serve --host=0.0.0.0 --port=8000
```

**5. Acesse no celular:**
```
http://SEU_IP_LOCAL:8000
```
Exemplo: `http://192.168.0.100:8000`

> **⚠️ Importante**: Para testes no celular, use `npm run build` ao invés de `npm run dev`. O build compila os assets de forma estática e funciona melhor em dispositivos da rede local. Lembre-se de rodar `npm run build` novamente sempre que fizer mudanças no CSS/JS.

> **💡 Dica**: Para desenvolvimento no PC, continue usando `npm run dev` normalmente para ter hot reload automático.

> **Nota**: O arquivo `.env` não é versionado no Git por segurança. Cada desenvolvedor deve configurar seu próprio IP local.