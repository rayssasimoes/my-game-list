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

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_profile'])) {
    $username = trim($_POST['username']);
    $firstName = trim($_POST['first_name']);
    $lastName = trim($_POST['last_name']);
    $email = trim($_POST['email']);
    $bio = trim($_POST['bio']);
    $pronouns = isset($_POST['pronouns']) ? $_POST['pronouns'] : 'male';
    
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
                // Atualizar dados
                $stmt = $db->prepare("
                    UPDATE users 
                    SET username = ?, first_name = ?, last_name = ?, email = ?, bio = ?, pronouns = ?
                    WHERE id = ?
                ");
                
                if ($stmt->execute([$username, $firstName, $lastName, $email, $bio, $pronouns, $userId])) {
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
            <button class="settings-tab" data-tab="auth">Autenticação</button>
            <button class="settings-tab" data-tab="avatar">Avatar</button>
            <button class="settings-tab" data-tab="notifications">Notificações</button>
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
        
        <!-- Conteúdo da Aba Notificações -->
        <div class="tab-content" data-tab-content="notifications">
            <h2 class="section-subtitle">Notificações</h2>
            <div class="placeholder-content">
                <i class="bi bi-bell"></i>
                <p>Configurações de notificação (Em breve)</p>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
