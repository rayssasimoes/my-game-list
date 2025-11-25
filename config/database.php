<?php
/**
 * Configuração do Banco de Dados
 */

// Configurações do banco
define('DB_HOST', 'localhost');
define('DB_NAME', 'db_mygamelist');
define('DB_USER', 'root');
define('DB_PASS', '');

// Função para conectar ao banco
function getDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
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
                "Verifique se o MySQL está rodando no XAMPP. " .
                "Erro técnico: " . $e->getMessage()
            );
        }
    }
    
    return $pdo;
}
