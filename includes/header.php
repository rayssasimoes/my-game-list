<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'MyGameList'; ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <!-- CSS Global (sempre carregado) -->
    <link rel="stylesheet" href="public/css/global.css">
    
    <!-- CSS Components (sempre carregado) -->
    <link rel="stylesheet" href="public/css/components/navbar.css">
    <link rel="stylesheet" href="public/css/components/modals.css">
    <link rel="stylesheet" href="public/css/components/buttons.css">
    <link rel="stylesheet" href="public/css/components/forms.css">
    <link rel="stylesheet" href="public/css/components/common.css">
    
    <!-- CSS específico da página -->
    <?php
    $currentPage = $_GET['page'] ?? 'home';
    
    // Carregar CSS específico da página
    if ($currentPage === 'profile'):
    ?>
        <link rel="stylesheet" href="public/css/pages/profile.css">
    <?php elseif ($currentPage === 'edit-profile'): ?>
        <link rel="stylesheet" href="public/css/pages/edit-profile.css">
    <?php elseif ($currentPage === 'search'): ?>
        <link rel="stylesheet" href="public/css/pages/search.css">
    <?php elseif ($currentPage === 'my-list' || $currentPage === 'games'): ?>
        <link rel="stylesheet" href="public/css/pages/games.css">
    <?php else: ?>
        <!-- Home e outras páginas carregam games.css por padrão -->
        <link rel="stylesheet" href="public/css/pages/games.css">
    <?php endif; ?>
    
    <!-- CSS Responsivo (sempre por último) -->
    <link rel="stylesheet" href="public/css/responsive.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-main">
            <div class="navbar-container">
                <!-- Logo (Extrema Esquerda) -->
                <a class="navbar-brand" href="index.php">
                    MyGameList
                </a>

                <!-- Busca (Desktop) -->
                <div class="search-container search-desktop">
                    <form action="index.php" method="GET">
                        <input type="hidden" name="page" value="search">
                        <i class="bi bi-search search-icon" id="searchIcon"></i>
                        <input class="search-input" type="search" name="q" placeholder="Buscar jogos..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        <i class="bi bi-x search-clear" id="searchClear" role="button" title="Limpar busca"></i>
                    </form>
                </div>

                <!-- Right Side -->
                <div class="navbar-right">
                    <?php if (!isLoggedIn()): ?>
                        <!-- Visitante: Botões de Autenticação -->
                        <button class="btn-login" onclick="openModal('loginModal')">Entrar</button>
                        <button class="btn-register" onclick="openModal('registerModal')">Cadastrar</button>
                        <!-- Menu Hambúrguer Mobile -->
                        <button class="btn-hamburger" id="hamburgerBtn">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    <?php else: ?>
                        <!-- Logado: Notificações + Avatar -->
                        <?php $user = getUser(); ?>
                        
                        <!-- Ícone de Notificações -->
                        <button class="btn-notifications" title="Notificações">
                            <i class="bi bi-bell"></i>
                            <span class="notification-badge">3</span>
                        </button>
                        
                        <!-- Avatar + Nome do Usuário -->
                        <div class="user-profile-wrapper">
                            <button class="btn-user-profile" id="userAvatarBtn">
                                <div class="user-profile-avatar">
                                    <?php if (!empty($user['avatar_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar">
                                    <?php else: ?>
                                        <svg class="default-avatar-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <span class="user-profile-name"><?php echo htmlspecialchars($user['username']); ?></span>
                                <i class="bi bi-chevron-down user-profile-arrow"></i>
                            </button>
                            
                            <!-- Dropdown Menu do Usuário -->
                            <div class="user-dropdown-menu" id="userDropdownMenu">
                            <div class="user-dropdown-header">
                                <div class="user-dropdown-avatar">
                                    <?php if (!empty($user['avatar_path'])): ?>
                                        <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar">
                                    <?php else: ?>
                                        <svg class="default-avatar-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                                        </svg>
                                    <?php endif; ?>
                                </div>
                                <div class="user-dropdown-info">
                                    <div class="user-dropdown-name"><?php echo htmlspecialchars($user['first_name'] ?? $user['name']); ?></div>
                                    <div class="user-dropdown-username">@<?php echo htmlspecialchars($user['username']); ?></div>
                                </div>
                            </div>
                            <div class="user-dropdown-divider"></div>
                            <a class="user-dropdown-item" href="index.php?page=profile">
                                <i class="bi bi-person"></i> Ver Perfil
                            </a>
                            <a class="user-dropdown-item" href="index.php?page=my-list">
                                <i class="bi bi-list-ul"></i> Minha Lista
                            </a>
                            <a class="user-dropdown-item" href="index.php?page=edit-profile">
                                <i class="bi bi-gear"></i> Configurações
                            </a>
                            <div class="user-dropdown-divider"></div>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="user-dropdown-item user-dropdown-button">
                                    <i class="bi bi-box-arrow-right"></i> Sair
                                </button>
                            </form>
                            </div>
                        </div>
                        
                        <!-- Menu Hambúrguer Mobile -->
                        <button class="btn-hamburger" id="hamburgerBtn">
                            <span></span>
                            <span></span>
                            <span></span>
                        </button>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        
        <!-- Barra de Busca (Mobile - Nova Linha) -->
        <div class="navbar-search">
            <div class="navbar-container">
                <div class="search-container">
                    <form action="index.php" method="GET">
                        <input type="hidden" name="page" value="search">
                        <i class="bi bi-search search-icon"></i>
                        <input class="search-input" type="search" name="q" placeholder="Buscar jogos..." value="<?php echo htmlspecialchars($_GET['q'] ?? ''); ?>">
                        <i class="bi bi-x search-clear search-clear-mobile" role="button" title="Limpar busca"></i>
                    </form>
                </div>
            </div>
        </div>
        
        <!-- Menu Mobile (Hambúrguer) -->
        <div class="mobile-menu" id="mobileMenu">
            <div class="mobile-menu-content">
                <a class="mobile-menu-item" href="index.php">
                    <i class="bi bi-house"></i> Início
                </a>
                <a class="mobile-menu-item" href="index.php?page=search">
                    <i class="bi bi-search"></i> Buscar
                </a>
                
                <?php if (isLoggedIn()): ?>
                    <!-- Menu para usuários logados -->
                    <a class="mobile-menu-item" href="index.php?page=my-list">
                        <i class="bi bi-list-ul"></i> Minha Lista
                    </a>
                    <a class="mobile-menu-item" href="index.php?page=profile">
                        <i class="bi bi-person"></i> Perfil
                    </a>
                    <a class="mobile-menu-item" href="index.php?page=edit-profile">
                        <i class="bi bi-gear"></i> Configurações
                    </a>
                    <div class="mobile-menu-divider"></div>
                    <form method="POST" style="margin: 0;">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="mobile-menu-item mobile-menu-button">
                            <i class="bi bi-box-arrow-right"></i> Sair
                        </button>
                    </form>
                <?php else: ?>
                    <!-- Menu para visitantes -->
                    <div class="mobile-menu-divider"></div>
                    <a class="mobile-menu-item" href="#" onclick="openModal('loginModal'); return false;">
                        <i class="bi bi-box-arrow-in-right"></i> Entrar
                    </a>
                    <a class="mobile-menu-item mobile-menu-highlight" href="#" onclick="openModal('registerModal'); return false;">
                        <i class="bi bi-person-plus"></i> Criar Conta
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Alertas -->
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            </div>
        <?php endif; ?>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            </div>
        <?php endif; ?>
    </div>

    <main>
