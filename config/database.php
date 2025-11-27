<?php
/**
 * Configuração do Banco de Dados
 *
 * Este arquivo agora tenta carregar variáveis do arquivo `.env` (se existir)
 * e usa `getenv()` como fonte principal. Caso as variáveis não existam,
 * usa valores padrão para desenvolvimento local.
 */

// Tenta carregar um .env simples se houver (somente chave=valor, sem dependências)

// Carrega credenciais específicas de produção/hosting se existir:
// - Crie `config/credentials.php` no servidor com `putenv()` ou `define()`
// - NÃO comite esse arquivo no Git (adicione ao .gitignore)
$credsPath = __DIR__ . '/credentials.php';
if (file_exists($credsPath)) {
    require_once $credsPath;
}

$envPath = __DIR__ . '/../.env';
if (!getenv('DB_HOST') && file_exists($envPath)) {
    $lines = file($envPath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $line = trim($line);
        if ($line === '' || strpos($line, '#') === 0) continue;
        if (!strpos($line, '=')) continue;
        list($key, $val) = explode('=', $line, 2);
        $key = trim($key);
        $val = trim($val);
        $val = trim($val, "'\" ");
        putenv("$key=$val");
        $_ENV[$key] = $val;
        $_SERVER[$key] = $val;
    }
}

// Configurações do banco (prioriza variáveis de ambiente)
// Caso as variáveis de ambiente não estejam definidas, usar os valores
// fornecidos para o ambiente de produção (substitua se necessário).
define('DB_HOST', getenv('DB_HOST') ?: 'sql110.infinityfree.com');
define('DB_NAME', getenv('DB_NAME') ?: 'if0_40530168_db_mygamelist');
define('DB_USER', getenv('DB_USER') ?: 'if0_40530168');
define('DB_PASS', getenv('DB_PASS') ?: 'flacampeao25');
// Porta opcional (quando fornecida pelo provedor)
define('DB_PORT', getenv('DB_PORT') ?: null);

// Função para conectar ao banco
function getDB() {
    static $pdo = null;

    if ($pdo === null) {
        try {
            // Monta DSN incluindo porta quando fornecida
            $host = DB_HOST;
            // Se o host for 'localhost', use 127.0.0.1 para forçar conexão TCP (evita uso de socket)
            if ($host === 'localhost') {
                $host = '127.0.0.1';
            }

            $portPart = '';
            if (!empty(DB_PORT)) {
                $portPart = ';port=' . DB_PORT;
            }

            $dsn = "mysql:host={$host}{$portPart};dbname=" . DB_NAME . ";charset=utf8mb4";

            $pdo = new PDO(
                $dsn,
                DB_USER,
                DB_PASS,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false,
                    PDO::ATTR_TIMEOUT => 5 // Timeout de 5 segundos
                ]
            );
        } catch (PDOException $e) {
            // Log do erro
            error_log("Erro de conexão com MySQL: " . $e->getMessage());

            // Mensagem mais amigável
            throw new Exception(
                "Não foi possível conectar ao banco de dados. " .
                "Verifique se as credenciais estão corretas. Erro técnico: " . $e->getMessage()
            );
        }
    }

    return $pdo;
}
