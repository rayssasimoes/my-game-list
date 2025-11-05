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
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Erro na conexão com o banco: " . $e->getMessage());
        }
    }
    
    return $pdo;
}
