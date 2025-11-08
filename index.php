<?php

// Iniciar sessão
session_start();

// Incluir arquivos necessários
require_once 'config/database.php';
require_once 'includes/auth.php';
require_once 'includes/igdb-api.php';

// Sistema de rotas simples
$page = $_GET['page'] ?? 'home';

// Rotas disponíveis
$validPages = [
    'home',
    'my-list',
    'search',
    'profile',
    'edit-profile'
];

// Verificar se a página existe
if (!in_array($page, $validPages)) {
    $page = 'home';
}

// Carregar a página solicitada
$pageFile = "pages/{$page}.php";

if (file_exists($pageFile)) {
    include $pageFile;
} else {
    // Página não encontrada - redirecionar para home
    header('Location: index.php');
    exit;
}
