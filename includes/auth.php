<?php
/**
 * Sistema de Autenticação
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
    $stmt = $db->prepare("SELECT id, name, username, email, first_name, last_name, bio, pronouns, avatar_path FROM users WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    return $stmt->fetch();
}

// Faz login
function login($identifier, $password) {
    $db = getDB();
    
    // Busca por email OU username
    $stmt = $db->prepare("SELECT id, name, username, email, password FROM users WHERE email = ? OR username = ?");
    $stmt->execute([$identifier, $identifier]);
    $user = $stmt->fetch();
    
    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        return true;
    }
    
    return false;
}

// Faz cadastro
function register($name, $username, $email, $password) {
    $db = getDB();
    
    // Verifica se email já existe
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Email já cadastrado'];
    }
    
    // Verifica se username já existe
    $stmt = $db->prepare("SELECT id FROM users WHERE username = ?");
    $stmt->execute([$username]);
    if ($stmt->fetch()) {
        return ['success' => false, 'error' => 'Nome de usuário já está em uso'];
    }
    
    // Insere novo usuário
    $stmt = $db->prepare("INSERT INTO users (name, username, email, password, created_at, updated_at) VALUES (?, ?, ?, ?, NOW(), NOW())");
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
    $result = $stmt->execute([$name, $username, $email, $hashedPassword]);
    
    return ['success' => $result, 'error' => null];
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
        $identifier = $_POST['identifier'] ?? ''; // email OU username
        $password = $_POST['password'] ?? '';
        
        if (login($identifier, $password)) {
            // Se for requisição AJAX, retorna JSON
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => true]);
                exit;
            }
            $_SESSION['success'] = 'Login realizado com sucesso!';
            header('Location: index.php');
            exit;
        } else {
            // Se for requisição AJAX, retorna JSON com erro
            if (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'error' => 'Email/usuário ou senha incorretos']);
                exit;
            }
            $_SESSION['error'] = 'Email/usuário ou senha incorretos.';
        }
    }
    
    if ($_POST['action'] === 'register') {
        $name = $_POST['name'] ?? '';
        $username = $_POST['username'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        
        if (strlen($password) < 6) {
            $_SESSION['error'] = 'A senha deve ter no mínimo 6 caracteres.';
        } else {
            $result = register($name, $username, $email, $password);
            
            if ($result['success']) {
                $_SESSION['success'] = 'Conta criada com sucesso! Faça login.';
                header('Location: index.php');
                exit;
            } else {
                $_SESSION['error'] = $result['error'];
            }
        }
    }
    
    if ($_POST['action'] === 'logout') {
        logout();
    }
}
