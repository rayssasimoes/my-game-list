<?php
// config/credentials.example.php
// Copie este arquivo para `config/credentials.php` e preencha os valores.
// NÃO faça commit do arquivo `config/credentials.php` com credenciais reais.

// Exemplo usando putenv() para compatibilidade com config/database.php
$creds = [
    'DB_HOST' => 'seu_host_mysql_aqui',      // ex: sqlXXX.epizy.com
    'DB_NAME' => 'seu_nome_do_banco',        // ex: epiz_12345678_dbname
    'DB_USER' => 'seu_usuario_mysql',        // ex: epiz_12345678
    'DB_PASS' => 'sua_senha_mysql',          // ex: senhaSeguraAqui
];

foreach ($creds as $k => $v) {
    putenv("$k=$v");
    $_ENV[$k] = $v;
    $_SERVER[$k] = $v;
}
