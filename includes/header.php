<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'MyGameList'; ?></title>
    <link rel="stylesheet" href="public/css/style.css">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar">
        <div class="navbar-container">
            <!-- Logo -->
            <a class="navbar-brand" href="index.php">
                MyGameList
            </a>

            <!-- Search Bar -->
            <div class="search-container">
                <form action="index.php" method="GET">
                    <input class="search-input" type="search" name="search" placeholder="Buscar jogos..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
                </form>
            </div>

            <!-- Right Side Of Navbar -->
            <div class="navbar-menu">
                <!-- Link principal: Jogos -->
                <a class="nav-link" href="index.php">Jogos</a>
                
                <!-- Authentication Links -->
                <?php if (!isLoggedIn()): ?>
                    <a class="nav-link" href="#" onclick="openModal('loginModal')">Entrar</a>
                    <a class="nav-link" href="#" onclick="openModal('registerModal')">Criar Conta</a>
                <?php else: ?>
                    <?php $user = getUser(); ?>
                    <div class="nav-item dropdown">
                        <a class="dropdown-toggle" href="#" role="button">
                            <?php echo htmlspecialchars($user['name']); ?>
                        </a>

                        <div class="dropdown-menu">
                            <a class="dropdown-item" href="index.php?page=my-list">
                                Minha Lista
                            </a>
                            <div class="dropdown-divider"></div>
                            <form method="POST" style="margin: 0;">
                                <input type="hidden" name="action" value="logout">
                                <button type="submit" class="dropdown-item" style="width: 100%; text-align: left; cursor: pointer;">
                                    Sair
                                </button>
                            </form>
                        </div>
                    </div>
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
