<?php
    session_start();

    require 'vendor/autoload.php';
    require 'process/db_connect.php';

    if (!isset($_SESSION['id'])) {
        header('Location: login.php');
        exit();
    }

    $sql = "SELECT email, username, nome_completo, codigo_unico, photo FROM users WHERE id = ?";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([$_SESSION['id']]);
    $usuario = $stmt->fetch();

    if (!$usuario) {
        die('Erro: Usuário não encontrado.');
    }
    
    $user_photo_url = !empty($usuario['photo']) ? htmlspecialchars($usuario['photo']) : 'img/user.png';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações da Conta - Neptune Miners</title>
    <link rel="shortcut icon" type="imagex/png" href="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//Neptune.png">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/config.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-content">
            <div class="logo">
                <img src="https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/empresa//logo-white.png" alt="logo da empresa" class="logo-img"/>
            </div>
            <div class="user-info">
                <div class="avatar">
                    <?php echo '<img src="' . $user_photo_url . '" alt="Foto do perfil" class="header-img" />'; ?>
                </div>
                <span><?php echo htmlspecialchars($usuario['username']); ?></span>
            </div>
        </div>
    </header>

    <div class="main-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="profile-section">
                <div class="profile-avatar">
                <?php echo '<img src="' . $user_photo_url . '" alt="Foto do perfil" class="header-img" />'; ?>
                    <button class="camera-btn">
                        <i class="fas fa-camera"></i>
                    </button>
                </div>
                <h2><?php echo htmlspecialchars($usuario['username']); ?></h2>
            </div>

            <nav class="sidebar-nav">
                <button class="nav-item active" data-tab="profile">
                    <i class="fas fa-user"></i>
                    Perfil
                </button>
                <button class="nav-item" data-tab="security">
                    <i class="fas fa-shield-alt"></i>
                    Segurança
                </button>
                <button class="nav-item" data-tab="notifications">
                    <i class="fas fa-bell"></i>
                    Notificações
                </button>
                <button class="nav-item" data-tab="privacy">
                    <i class="fas fa-globe"></i>
                    Privacidade
                </button>
                <button class="nav-item help">
                    <i class="fas fa-question-circle"></i>
                    Ajuda
                </button>
                <div class="separator"></div>
                <button class="nav-item return">
                    <i class="fas fa-undo-alt"></i>
                    Voltar
                </button>
                <button class="nav-item logout">
                    <i class="fas fa-sign-out-alt"></i>
                    Sair
                </button>
            </nav>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <div class="content-header">
                <i class="fas fa-cog"></i>
                <h1>Configurações da Conta</h1>
            </div>

            <!-- Tab Navigation -->
            <div class="tab-navigation">
                <button class="tab-btn active" data-tab="profile">Perfil</button>
                <button class="tab-btn" data-tab="security">Segurança</button>
                <button class="tab-btn" data-tab="notifications">Notificações</button>
                <button class="tab-btn" data-tab="privacy">Privacidade</button>
            </div>

            <!-- Profile Tab -->
            <div class="tab-content active" id="profile">
                <div class="card">
                    <div class="card-header">
                        <h3>Informações Pessoais</h3>
                        <p>Atualize suas informações pessoais e de contato.</p>
                    </div>
                    <div class="card-content">
                        <form class="form">
                            <div class="form-row">
                                <div class="form-group">
                                    <label for="firstName">Username</label>
                                    <input type="text" id="firstName" value="<?php echo htmlspecialchars($usuario['username']); ?>" placeholder="Seu nome">
                                </div>
                                <div class="form-group">
                                    <label for="lastName">Nome Completo</label>
                                    <input type="text" id="lastName" value="<?php echo htmlspecialchars($usuario['nome_completo']); ?>" placeholder="Seu sobrenome">
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="email">Email</label>
                                <input type="email" id="email" value="<?php echo htmlspecialchars($usuario['email']); ?>" placeholder="seu@email.com">
                            </div>
                            <button type="submit" class="btn-primary">Salvar Alterações</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Security Tab -->
            <div class="tab-content" id="security">
                <div class="card">
                    <div class="card-header">
                        <h3>Alterar Senha</h3>
                        <p>Mantenha sua conta segura com uma senha forte.</p>
                    </div>
                    <div class="card-content">
                        <form class="form">
                            <div class="form-group">
                                <label for="currentPassword">Senha Atual</label>
                                <div class="password-input">
                                    <input type="password" id="currentPassword">
                                    <button type="button" class="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="newPassword">Nova Senha</label>
                                <div class="password-input">
                                    <input type="password" id="newPassword">
                                    <button type="button" class="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirmPassword">Confirmar Nova Senha</label>
                                <div class="password-input">
                                    <input type="password" id="confirmPassword">
                                    <button type="button" class="password-toggle">
                                        <i class="fas fa-eye"></i>
                                    </button>
                                </div>
                            </div>
                            <button type="submit" class="btn-primary">Alterar Senha</button>
                        </form>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <h3>Autenticação de Dois Fatores</h3>
                        <p>Adicione uma camada extra de segurança à sua conta.</p>
                    </div>
                    <div class="card-content">
                        <div class="setting-item">
                            <div class="setting-info">
                                <p class="setting-title">Autenticação de Dois Fatores</p>
                                <p class="setting-description">Proteja sua conta com 2FA</p>
                            </div>
                            <label class="switch">
                                <input type="checkbox">
                                <span class="slider"></span>
                            </label>
                        </div>
                        <button class="btn-secondary">Configurar 2FA</button>
                    </div>
                </div>
            </div>

            <!-- Notifications Tab -->
            <div class="tab-content" id="notifications">
                <div class="card">
                    <div class="card-header">
                        <h3>Preferências de Notificação</h3>
                        <p>Escolha como e quando você quer receber notificações.</p>
                    </div>
                    <div class="card-content">
                        <div class="settings-list">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Notificações por Email</p>
                                    <p class="setting-description">Receba atualizações importantes por email</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Notificações Push</p>
                                    <p class="setting-description">Receba notificações no navegador</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Mensagens</p>
                                    <p class="setting-description">Notificações de novas mensagens</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Atualizações de Sistema</p>
                                    <p class="setting-description">Notificações sobre atualizações e manutenção</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Privacy Tab -->
            <div class="tab-content" id="privacy">
                <div class="card">
                    <div class="card-header">
                        <h3>Configurações de Privacidade</h3>
                        <p>Controle quem pode ver suas informações e atividades.</p>
                    </div>
                    <div class="card-content">
                        <div class="settings-list">
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Perfil Público</p>
                                    <p class="setting-description">Permitir que outros vejam seu perfil</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Mostrar Status Online</p>
                                    <p class="setting-description">Exibir quando você está online</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox">
                                    <span class="slider"></span>
                                </label>
                            </div>
                            <div class="setting-item">
                                <div class="setting-info">
                                    <p class="setting-title">Permitir Mensagens Diretas</p>
                                    <p class="setting-description">Receber mensagens de outros usuários</p>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" checked>
                                    <span class="slider"></span>
                                </label>
                            </div>
                        </div>
                        <div class="separator"></div>
                        <div class="danger-zone">
                            <h4>Zona de Perigo</h4>
                            <div class="danger-buttons">
                                <button class="btn-danger">Excluir Conta</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script src="js/config.js"></script>
    <script src="js/thema.js"></script>
</body>
</html>