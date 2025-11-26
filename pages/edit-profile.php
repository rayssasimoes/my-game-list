<?php
// Verificar se o usuário está autenticado
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}

// Obter conexão com banco de dados
$db = getDB();

// Pegar informações do usuário
$userId = $_SESSION['user_id'];

// Buscar usuário
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch();

// As datas agora vêm automaticamente do getUser() via header.php

// Processar formulário da aba Perfil
$profileSuccess = false;
$profileError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Captura segura da seção que está sendo atualizada para evitar warnings
    $updateSection = $_POST['update_section'] ?? '';

    // Processar apenas a seção adequada
    if ($updateSection === 'info') {
        $username = trim($_POST['username'] ?? '');
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $pronouns = isset($_POST['pronouns']) ? $_POST['pronouns'] : 'male';

    // Campos de conexões sociais (se disponíveis)
    $social_steam = trim($_POST['social_steam'] ?? '');
    $social_psn = trim($_POST['social_psn'] ?? '');
    $social_xbox = trim($_POST['social_xbox'] ?? '');
    $social_discord = trim($_POST['social_discord'] ?? '');
    $social_twitter = trim($_POST['social_twitter'] ?? '');
    $social_instagram = trim($_POST['social_instagram'] ?? '');

    // Normalização básica dos campos sociais
    // Instagram -> https://instagram.com/username
    if (!empty($social_instagram)) {
        $s = trim($social_instagram);
        $s = preg_replace('/^@+/', '', $s); // remove leading @
        // se já for URL, garantir https
        if (preg_match('#^(https?://)#i', $s)) {
            // garantir domínio instagram
            if (stripos($s, 'instagram.com') === false) {
                // manter, mas prefixar https://
                $s = preg_replace('#^https?://#i', 'https://', $s);
            } else {
                $s = preg_replace('#^http://#i', 'https://', $s);
            }
        } else {
            // tratar como username
            $s = 'https://instagram.com/' . ltrim($s, '/');
        }
        $social_instagram = rtrim($s, '/');
    }

    // Twitter / X -> https://x.com/username
    if (!empty($social_twitter)) {
        $t = trim($social_twitter);
        $t = preg_replace('/^@+/', '', $t);
        if (preg_match('#^(https?://)#i', $t)) {
            if (stripos($t, 'x.com') !== false || stripos($t, 'twitter.com') !== false) {
                $t = preg_replace('#^http://#i', 'https://', $t);
            } else {
                $t = 'https://x.com/' . ltrim($t, '/');
            }
        } else {
            $t = 'https://x.com/' . ltrim($t, '/');
        }
        $social_twitter = rtrim($t, '/');
    }

    // Steam -> garantir https e tentar formar profile URL
    if (!empty($social_steam)) {
        $st = trim($social_steam);
        // se já for URL, garantir https
        if (preg_match('#^(https?://)#i', $st)) {
            $st = preg_replace('#^http://#i', 'https://', $st);
        } else {
            // se for apenas número -> profiles/<id>
            if (preg_match('/^\d+$/', $st)) {
                $st = 'https://steamcommunity.com/profiles/' . $st;
            } else {
                // nome de usuário/vanity
                $st = 'https://steamcommunity.com/id/' . ltrim($st, '/');
            }
        }
        $social_steam = rtrim($st, '/');
    }
    
    // Validações
    if (empty($username) || empty($firstName) || empty($email)) {
        $profileError = 'Nome de usuário, Nome e E-mail são obrigatórios.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $profileError = 'E-mail inválido.';
    } elseif (!preg_match('/^[a-zA-Z0-9_]{3,20}$/', $username)) {
        $profileError = 'Nome de usuário deve ter 3-20 caracteres (apenas letras, números e underscore).';
    } else {
        // Verificar se username já existe (exceto o do próprio usuário)
        $stmt = $db->prepare("SELECT id FROM users WHERE username = ? AND id != ?");
            $stmt->execute([$username, $userId]);
            if ($stmt->fetch()) {
                $profileError = 'Este nome de usuário já está em uso.';
            } else {
                // Verificar se email já existe (exceto o do próprio usuário)
                $stmt = $db->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
                $stmt->execute([$email, $userId]);
                if ($stmt->fetch()) {
                    $profileError = 'Este e-mail já está em uso.';
                } else {
                    // Atualizar dados (inclui campos sociais; as colunas devem existir no banco)
                    $stmt = $db->prepare("
                        UPDATE users
                        SET username = ?, first_name = ?, last_name = ?, email = ?, bio = ?, pronouns = ?,
                            social_steam = ?, social_psn = ?, social_xbox = ?, social_discord = ?, social_twitter = ?, social_instagram = ?
                        WHERE id = ?
                    ");
                
                    if ($stmt->execute([$username, $firstName, $lastName, $email, $bio, $pronouns, $social_steam, $social_psn, $social_xbox, $social_discord, $social_twitter, $social_instagram, $userId])) {
                    $profileSuccess = true;
                    // Recarregar dados do usuário
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    // Buscar datas em query separada
                    $stmtDates = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at, DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as updated_at FROM users WHERE id = ?");
                    $stmtDates->execute([$userId]);
                    $dates = $stmtDates->fetch();
                    if ($dates && isset($dates['created_at'])) {
                        $user['created_at'] = $dates['created_at'];
                        $user['updated_at'] = $dates['updated_at'];
                    }
                    
                    // Atualizar nome na sessão
                    $_SESSION['user_name'] = $user['first_name'];
                } else {
                    $profileError = 'Erro ao atualizar perfil.';
                }
            }
        }
        }
    } elseif ($updateSection === 'social') {
        // Processar atualização apenas das conexões sociais
        $social_steam = trim($_POST['social_steam'] ?? '');
        $social_psn = trim($_POST['social_psn'] ?? '');
        $social_xbox = trim($_POST['social_xbox'] ?? '');
        $social_discord = trim($_POST['social_discord'] ?? '');
        $social_twitter = trim($_POST['social_twitter'] ?? '');
        $social_instagram = trim($_POST['social_instagram'] ?? '');

        // Normalização básica dos campos sociais
        if (!empty($social_instagram)) {
            $s = trim($social_instagram);
            $s = preg_replace('/^@+/', '', $s);
            if (preg_match('#^(https?://)#i', $s)) {
                if (stripos($s, 'instagram.com') === false) {
                    $s = preg_replace('#^https?://#i', 'https://', $s);
                } else {
                    $s = preg_replace('#^http://#i', 'https://', $s);
                }
            } else {
                $s = 'https://instagram.com/' . ltrim($s, '/');
            }
            $social_instagram = rtrim($s, '/');
        }

        if (!empty($social_twitter)) {
            $t = trim($social_twitter);
            $t = preg_replace('/^@+/', '', $t);
            if (preg_match('#^(https?://)#i', $t)) {
                if (stripos($t, 'x.com') !== false || stripos($t, 'twitter.com') !== false) {
                    $t = preg_replace('#^http://#i', 'https://', $t);
                } else {
                    $t = 'https://x.com/' . ltrim($t, '/');
                }
            } else {
                $t = 'https://x.com/' . ltrim($t, '/');
            }
            $social_twitter = rtrim($t, '/');
        }

        if (!empty($social_steam)) {
            $st = trim($social_steam);
            if (preg_match('#^(https?://)#i', $st)) {
                $st = preg_replace('#^http://#i', 'https://', $st);
            } else {
                if (preg_match('/^\d+$/', $st)) {
                    $st = 'https://steamcommunity.com/profiles/' . $st;
                } else {
                    $st = 'https://steamcommunity.com/id/' . ltrim($st, '/');
                }
            }
            $social_steam = rtrim($st, '/');
        }

        // Atualizar somente colunas sociais
        $stmt = $db->prepare("UPDATE users SET social_steam = ?, social_psn = ?, social_xbox = ?, social_discord = ?, social_twitter = ?, social_instagram = ? WHERE id = ?");
        if ($stmt->execute([$social_steam, $social_psn, $social_xbox, $social_discord, $social_twitter, $social_instagram, $userId])) {
            $profileSuccess = true;
            $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
            $stmt->execute([$userId]);
            $user = $stmt->fetch();
            // Garantir que $user tenha os valores normalizados imediatamente
            $user['social_steam'] = $social_steam;
            $user['social_psn'] = $social_psn;
            $user['social_xbox'] = $social_xbox;
            $user['social_discord'] = $social_discord;
            $user['social_twitter'] = $social_twitter;
            $user['social_instagram'] = $social_instagram;
            // Sincronizar dados na sessão caso exista cache do usuário
            if (isset($_SESSION['user']) && is_array($_SESSION['user'])) {
                $_SESSION['user']['social_steam'] = $user['social_steam'] ?? '';
                $_SESSION['user']['social_psn'] = $user['social_psn'] ?? '';
                $_SESSION['user']['social_xbox'] = $user['social_xbox'] ?? '';
                $_SESSION['user']['social_discord'] = $user['social_discord'] ?? '';
                $_SESSION['user']['social_twitter'] = $user['social_twitter'] ?? '';
                $_SESSION['user']['social_instagram'] = $user['social_instagram'] ?? '';
            }
        } else {
            $profileError = 'Erro ao atualizar conexões.';
        }
    }

}

// Processar formulário da aba Autenticação (Senha)
$passwordSuccess = false;
$passwordError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_password'])) {
    $currentPassword = $_POST['current_password'];
    $newPassword = $_POST['new_password'];
    $confirmPassword = $_POST['confirm_password'];
    
    // Validações
    if (empty($currentPassword) || empty($newPassword) || empty($confirmPassword)) {
        $passwordError = 'Todos os campos são obrigatórios.';
    } elseif ($newPassword !== $confirmPassword) {
        $passwordError = 'A nova senha e a confirmação não coincidem.';
    } elseif (strlen($newPassword) < 6) {
        $passwordError = 'A nova senha deve ter pelo menos 6 caracteres.';
    } else {
        // Verificar senha atual
        if (password_verify($currentPassword, $user['password'])) {
            // Hash da nova senha
            $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
            
            // Atualizar senha
            $stmt = $db->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($stmt->execute([$hashedPassword, $userId])) {
                $passwordSuccess = true;
            } else {
                $passwordError = 'Erro ao atualizar senha.';
            }
        } else {
            $passwordError = 'Senha atual incorreta.';
        }
    }
}

// Processar formulário da aba Avatar
$avatarSuccess = false;
$avatarError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_avatar'])) {
    // Verificar se um arquivo foi enviado
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            $avatarError = 'Formato de arquivo inválido. Use JPG, PNG, GIF ou WEBP.';
        } elseif ($file['size'] > 5 * 1024 * 1024) { // 5MB max
            $avatarError = 'Arquivo muito grande. Tamanho máximo: 5MB.';
        } else {
            // Criar pasta de uploads se não existir
            $uploadDir = __DIR__ . '/../public/uploads/avatars/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Gerar nome único para o arquivo
            $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
            $newFileName = 'avatar_' . $userId . '_' . time() . '.' . $extension;
            $uploadPath = $uploadDir . $newFileName;
            
            // Mover arquivo
            if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
                // Deletar avatar antigo se existir
                if (!empty($user['avatar_path'])) {
                    $oldAvatarPath = __DIR__ . '/../' . $user['avatar_path'];
                    if (file_exists($oldAvatarPath)) {
                        unlink($oldAvatarPath);
                    }
                }
                
                // Salvar caminho no banco (relativo à raiz do projeto)
                $avatarPath = 'public/uploads/avatars/' . $newFileName;
                $stmt = $db->prepare("UPDATE users SET avatar_path = ? WHERE id = ?");
                
                if ($stmt->execute([$avatarPath, $userId])) {
                    $avatarSuccess = true;
                    // Recarregar dados do usuário
                    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
                    $stmt->execute([$userId]);
                    $user = $stmt->fetch();
                    
                    // Buscar datas em query separada
                    $stmtDates = $db->prepare("SELECT DATE_FORMAT(created_at, '%Y-%m-%d %H:%i:%s') as created_at, DATE_FORMAT(updated_at, '%Y-%m-%d %H:%i:%s') as updated_at FROM users WHERE id = ?");
                    $stmtDates->execute([$userId]);
                    $dates = $stmtDates->fetch();
                    if ($dates && isset($dates['created_at'])) {
                        $user['created_at'] = $dates['created_at'];
                        $user['updated_at'] = $dates['updated_at'];
                    }
                } else {
                    $avatarError = 'Erro ao salvar avatar no banco de dados.';
                }
            } else {
                $avatarError = 'Erro ao fazer upload do arquivo.';
            }
        }
    } else {
        $avatarError = 'Nenhum arquivo foi selecionado.';
    }
}

// Incluir header
include 'includes/header.php';
?>

<div class="edit-profile-page">
    <div class="container">
        <!-- Título Principal -->
        <h1 class="page-title">Configurações da Conta</h1>
        
        <!-- Abas de Navegação -->
        <div class="settings-tabs">
            <button class="settings-tab active" data-tab="profile">Perfil</button>
            <button class="settings-tab" data-tab="connections">Minhas Conexões</button>
            <button class="settings-tab" data-tab="auth">Autenticação</button>
            <button class="settings-tab" data-tab="avatar">Avatar</button>
        </div>
        
        <!-- Conteúdo da Aba Perfil -->
        <div class="tab-content active" data-tab-content="profile">
            <h2 class="section-subtitle">Perfil</h2>
            
            <?php if ($profileSuccess): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Perfil atualizado com sucesso!
                </div>
            <?php endif; ?>
            
            <?php if ($profileError): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($profileError); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="settings-form">
                <!-- Indica que esta submissão é da seção 'info' -->
                <input type="hidden" name="update_section" value="info">
                <!-- Hidden: social fields will be saved together with profile (legacy) -->
                <input type="hidden" name="social_present" value="1">

                <div class="form-group">
                    <label for="username">Nome de usuário</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input availability-check" 
                        value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                        required
                        pattern="[a-zA-Z0-9_]{3,20}"
                        title="3-20 caracteres, apenas letras, números e underscore"
                        data-check-type="username"
                        data-current-value="<?php echo htmlspecialchars($user['username'] ?? ''); ?>"
                    >
                    <small class="form-hint availability-hint" id="username-edit-hint">3-20 caracteres, apenas letras, números e underscore</small>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="first_name">Nome</label>
                        <input 
                            type="text" 
                            id="first_name" 
                            name="first_name" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($user['first_name'] ?? ''); ?>"
                            required
                        >
                    </div>
                    
                    <div class="form-group">
                        <label for="last_name">Sobrenome</label>
                        <input 
                            type="text" 
                            id="last_name" 
                            name="last_name" 
                            class="form-input" 
                            value="<?php echo htmlspecialchars($user['last_name'] ?? ''); ?>"
                        >
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="email">Endereço de e-mail</label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        class="form-input" 
                        value="<?php echo htmlspecialchars($user['email']); ?>"
                        required
                    >
                </div>
                
                <div class="form-group">
                    <label for="bio">Biografia</label>
                    <textarea 
                        id="bio" 
                        name="bio" 
                        class="form-textarea" 
                        rows="4"
                        maxlength="500"
                        placeholder="Conte um pouco sobre você e seus gostos em jogos..."
                    ><?php echo htmlspecialchars($user['bio'] ?? ''); ?></textarea>
                    <small class="form-hint">Máximo 500 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="pronouns">Pronomes</label>
                    <select id="pronouns" name="pronouns" class="form-select">
                        <option value="male" <?php echo ($user['pronouns'] ?? '') === 'male' ? 'selected' : ''; ?>>Ele/Dele</option>
                        <option value="female" <?php echo ($user['pronouns'] ?? '') === 'female' ? 'selected' : ''; ?>>Ela/Dela</option>
                        <option value="neutral" <?php echo ($user['pronouns'] ?? '') === 'neutral' ? 'selected' : ''; ?>>Prefiro Não Informar</option>
                    </select>
                </div>
                
                <button type="submit" name="save_profile" class="btn btn-primary">
                    <i class="bi bi-check-circle"></i> Salvar Alterações
                </button>
                
                <!-- Link de Excluir Conta removido daqui para posição menos proeminente -->
            </form>
            
            <!-- Informações da Conta -->
            <div class="account-info-section">
                <h3 class="section-subtitle-small">Informações da Conta</h3>
                <div class="account-info-grid">
                    <div class="account-info-item">
                        <span class="account-info-label">
                            <i class="bi bi-calendar-plus"></i> Membro desde
                        </span>
                        <span class="account-info-value">
                            <?php 
                            if (isset($user['created_at']) && $user['created_at']) {
                                try {
                                    $createdDate = new DateTime($user['created_at']);
                                    echo $createdDate->format('d/m/Y \à\s H:i');
                                } catch (Exception $e) {
                                    echo 'Não disponível';
                                }
                            } else {
                                echo 'Não disponível';
                            }
                            ?>
                        </span>
                    </div>
                    <div class="account-info-item">
                        <span class="account-info-label">
                            <i class="bi bi-clock-history"></i> Última atualização
                        </span>
                        <span class="account-info-value">
                            <?php 
                            if (isset($user['updated_at']) && $user['updated_at']) {
                                try {
                                    $updatedDate = new DateTime($user['updated_at']);
                                    echo $updatedDate->format('d/m/Y \à\s H:i');
                                } catch (Exception $e) {
                                    echo 'Não disponível';
                                }
                            } else {
                                echo 'Não disponível';
                            }
                            ?>
                        </span>
                    </div>
                </div>
            </div>

            <!-- Seção de perigo (link de exclusão posicionado de forma discreta) -->
            <div class="danger-zone" style="margin-top:1.5rem; border-top:1px solid rgba(0,0,0,0.05); padding-top:1rem;">
                <h3 class="section-subtitle-small">Perigo</h3>
                <p class="text-muted" style="margin:0 0 0.5rem 0; color: #ffffff;">Ações nesta seção são irreversíveis. Este botão é propositalmente discreto para evitar exclusões acidentais.</p>
                <div style="display:flex; gap:0.75rem; align-items:center;">
                    <button type="button" class="btn-link" style="color:#ffffff; text-decoration:underline; font-weight:500; font-size:0.95rem; background:none; border:none; padding:0; cursor:pointer;" onclick="openDeleteAccountModal()">Excluir minha conta</button>
                </div>
            </div>
        </div>
        
        <!-- Conteúdo da Aba Minhas Conexões -->
        <div class="tab-content" data-tab-content="connections">
            <h2 class="section-subtitle">Minhas Conexões</h2>

            <form method="POST" class="settings-form">
                <!-- Indica que esta submissão é da seção 'social' -->
                <input type="hidden" name="update_section" value="social">
                <div class="form-group">
                    <label for="social_steam"><i class="bi bi-steam"></i> Steam</label>
                    <input type="text" id="social_steam" name="social_steam" class="form-input" placeholder="Link do perfil completo" value="<?php echo htmlspecialchars($user['social_steam'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="social_discord"><i class="bi bi-discord"></i> Discord</label>
                    <input type="text" id="social_discord" name="social_discord" class="form-input" placeholder="SeuUser#1234" value="<?php echo htmlspecialchars($user['social_discord'] ?? ''); ?>">
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label for="social_psn"><i class="bi bi-psn"></i> PSN</label>
                        <input type="text" id="social_psn" name="social_psn" class="form-input" placeholder="Gamertag / PSN ID" value="<?php echo htmlspecialchars($user['social_psn'] ?? ''); ?>">
                    </div>
                    <div class="form-group">
                        <label for="social_xbox"><i class="bi bi-xbox"></i> Xbox</label>
                        <input type="text" id="social_xbox" name="social_xbox" class="form-input" placeholder="Gamertag / Xbox ID" value="<?php echo htmlspecialchars($user['social_xbox'] ?? ''); ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label for="social_twitter"><i class="bi bi-twitter"></i> Twitter</label>
                    <input type="text" id="social_twitter" name="social_twitter" class="form-input" placeholder="Link do perfil ou @handle" value="<?php echo htmlspecialchars($user['social_twitter'] ?? ''); ?>">
                </div>

                <div class="form-group">
                    <label for="social_instagram"><i class="bi bi-instagram"></i> Instagram</label>
                    <input type="text" id="social_instagram" name="social_instagram" class="form-input" placeholder="Link do perfil ou @handle" value="<?php echo htmlspecialchars($user['social_instagram'] ?? ''); ?>">
                </div>

                <div style="margin-top:0.75rem;">
                    <button type="submit" name="save_profile" class="btn btn-primary">
                        <i class="bi bi-link-45deg"></i> Salvar Conexões
                    </button>
                </div>
            </form>
        </div>

        <!-- Conteúdo da Aba Autenticação -->
        <div class="tab-content" data-tab-content="auth">
            <h2 class="section-subtitle">Autenticação</h2>
            
            <?php if ($passwordSuccess): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Senha alterada com sucesso!
                </div>
            <?php endif; ?>
            
            <?php if ($passwordError): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($passwordError); ?>
                </div>
            <?php endif; ?>
            
            <form method="POST" class="settings-form" id="password-change-form">
                <div class="form-group">
                    <label for="current_password">Senha Atual</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="current_password" 
                            name="current_password" 
                            class="form-input password-input" 
                            required
                        >
                        <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="current_password"></i>
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="new_password">Nova Senha</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="new_password" 
                            name="new_password" 
                            class="form-input password-input password-validation" 
                            required
                            minlength="6"
                        >
                        <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="new_password"></i>
                    </div>
                    <small class="form-hint password-hint" id="new_password_hint">Mínimo 6 caracteres</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirmar Nova Senha</label>
                    <div class="password-input-wrapper">
                        <input 
                            type="password" 
                            id="confirm_password" 
                            name="confirm_password" 
                            class="form-input password-input password-match-validation" 
                            required
                            minlength="6"
                            data-match-target="new_password"
                        >
                        <i class="bi bi-eye password-toggle-icon password-icon-inactive" data-target="confirm_password"></i>
                    </div>
                    <small class="form-hint password-match-hint" id="confirm_password_hint" style="display: none;">As senhas não coincidem</small>
                </div>
                
                <button type="submit" name="save_password" class="btn btn-primary">
                    <i class="bi bi-shield-check"></i> Salvar Alterações de Senha
                </button>
            </form>
        </div>
        
        <!-- Conteúdo da Aba Avatar -->
        <div class="tab-content" data-tab-content="avatar">
            <h2 class="section-subtitle">Avatar</h2>
            
            <?php if ($avatarSuccess): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Avatar atualizado com sucesso!
                </div>
            <?php endif; ?>
            
            <?php if ($avatarError): ?>
                <div class="alert alert-error">
                    <i class="bi bi-exclamation-circle"></i> <?php echo htmlspecialchars($avatarError); ?>
                </div>
            <?php endif; ?>
            
            <div class="avatar-preview-section">
                <div class="avatar-preview">
                    <?php if (!empty($user['avatar_path'])): ?>
                        <img src="<?php echo htmlspecialchars($user['avatar_path']); ?>" alt="Avatar atual">
                    <?php else: ?>
                        <svg class="default-avatar-icon" fill="currentColor" viewBox="0 0 20 20" xmlns="http://www.w3.org/2000/svg">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"></path>
                        </svg>
                    <?php endif; ?>
                </div>
                <div class="avatar-info">
                    <p><strong>Foto de perfil atual</strong></p>
                    <p class="text-muted">Formatos aceitos: JPG, PNG, GIF, WEBP</p>
                    <p class="text-muted">Tamanho máximo: 5MB</p>
                </div>
            </div>
            
            <form method="POST" enctype="multipart/form-data" class="settings-form">
                <div class="form-group">
                    <label for="avatar">Escolher nova foto</label>
                    <input 
                        type="file" 
                        id="avatar" 
                        name="avatar" 
                        class="form-input-file" 
                        accept="image/jpeg,image/png,image/gif,image/webp"
                    >
                </div>
                
                <button type="submit" name="save_avatar" class="btn btn-primary">
                    <i class="bi bi-upload"></i> Salvar Avatar
                </button>
            </form>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>

<!-- (Link de exclusão movido para ficar abaixo do botão de salvar alterações) -->

<!-- Modal: Excluir Conta -->
<div id="deleteAccountModal" class="modal">
    <div class="modal-content modal-small">
        <div class="modal-header">
            <h2 class="modal-title">Confirmar Exclusão</h2>
            <button class="modal-close" aria-label="Fechar" onclick="closeDeleteAccountModal()">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <div class="modal-body">
            <p>Tem certeza que deseja excluir permanentemente sua conta? Esta ação não pode ser desfeita e todos os seus dados serão apagados.</p>
        </div>
        <div class="modal-actions">
            <button type="button" class="btn-secondary" onclick="closeDeleteAccountModal()">Cancelar</button>
            <button type="button" id="confirmDeleteAccountBtn" class="btn-danger" onclick="deleteAccount()">Excluir Conta</button>
        </div>
    </div>
</div>

<script>
function openDeleteAccountModal() {
    const modal = document.getElementById('deleteAccountModal');
    modal.classList.add('show');
    document.body.classList.add('modal-open');
}

function closeDeleteAccountModal() {
    const modal = document.getElementById('deleteAccountModal');
    modal.classList.remove('show');
    document.body.classList.remove('modal-open');
}

function deleteAccount() {
    const btn = document.getElementById('confirmDeleteAccountBtn');
    btn.disabled = true;
    btn.textContent = 'Excluindo...';

    fetch('includes/delete-account.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded'
        },
        body: 'confirm=1'
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Redirecionar para home após logout
            window.location.href = 'index.php';
        } else {
            alert(data.message || 'Erro ao excluir conta');
            btn.disabled = false;
            btn.textContent = 'Excluir Conta';
            closeDeleteAccountModal();
        }
    })
    .catch(err => {
        console.error(err);
        alert('Erro ao excluir conta');
        btn.disabled = false;
        btn.textContent = 'Excluir Conta';
        closeDeleteAccountModal();
    });
}
</script>
