<?php
/**
 * Sistema de Autenticação - PHP Puro
 * Mantendo compatibilidade com banco do Laravel
 */

// Inicia sessão se ainda não iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Verifica se usuário está logado
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

// Pega dados do usuário logado
function getUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Faz login
function login($email, $password) {
    $db = getDB();
    $stmt = $db->prepare("SELECT id, name, email, password FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    
    return false;
}

// Faz cadastro
function register($name, $email, $password) {
    $db = getDB();
    
    // Verifica se email já existe
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return false; // Email já cadastrado
    }
    
    // Insere novo usuário
    $stmt = $db->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (?, ?, ?, NOW(), NOW())");
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    return $stmt->execute([$name, $email, $hashedPassword]);
}

// Faz logout
function logout() {
    session_destroy();
    header('Location: index.php');
    exit;
}

// Redireciona se não estiver logado
function requireLogin() {
    if (!isLoggedIn()) {
        $_SESSION['error'] = 'Você precisa estar logado para acessar esta página.';
        header('Location: index.php');
        exit;
    }
}

// Processa login via POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    if ($_POST['action'] === 'login') {
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (login($email, $password)) {
            $_SESSION['success'] = 'Login realizado com sucesso!';
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Email ou senha incorretos.';
        }
    }
    
    if ($_POST['action'] === 'register') {
        $name = $_POST['name'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A senha deve ter no mínimo 6 caracteres.';
        } elseif (register($name, $email, $password)) {
            $_SESSION['success'] = 'Conta criada com sucesso! Faça login.';
            header('Location: index.php');
            exit;
        } else {
            $_SESSION['error'] = 'Email já cadastrado ou erro ao criar conta.';
        }
    }
    
    if ($_POST['action'] === 'logout') {
        logout();
    }
}
