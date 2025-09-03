document.addEventListener('DOMContentLoaded', function() {
    
    // ========== GERENCIAMENTO DE ABAS ==========
    const tabButtons = document.querySelectorAll('.tab-btn');
    const tabContents = document.querySelectorAll('.tab-content');
    const navItems = document.querySelectorAll('.nav-item[data-tab]');

    function switchTab(tabId) {
        // Remove active class de todos os elementos
        tabButtons.forEach(btn => btn.classList.remove('active'));
        tabContents.forEach(content => content.classList.remove('active'));
        navItems.forEach(item => item.classList.remove('active'));

        // Adiciona active class aos elementos selecionados
        const selectedTabBtn = document.querySelector(`.tab-btn[data-tab="${tabId}"]`);
        const selectedContent = document.getElementById(tabId);
        const selectedNavItem = document.querySelector(`.nav-item[data-tab="${tabId}"]`);

        if (selectedTabBtn) selectedTabBtn.classList.add('active');
        if (selectedContent) selectedContent.classList.add('active');
        if (selectedNavItem) selectedNavItem.classList.add('active');
    }

    // Event listeners para botões de aba
    tabButtons.forEach(button => {
        button.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            if (tabId) switchTab(tabId);
        });
    });

    // Event listeners para navegação lateral
    navItems.forEach(item => {
        item.addEventListener('click', function() {
            const tabId = this.getAttribute('data-tab');
            if (tabId) switchTab(tabId);
        });
    });

    // ========== TOGGLE DE SENHAS ==========
    const passwordToggles = document.querySelectorAll('.password-toggle');
    passwordToggles.forEach(toggle => {
        toggle.addEventListener('click', function() {
            // Busca o input tanto como irmão anterior quanto como filho do parent
            const input = this.previousElementSibling || this.parentElement.querySelector('input[type="password"], input[type="text"]');
            const icon = this.querySelector('i');
            
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    if (icon) {
                        icon.classList.remove('fa-eye');
                        icon.classList.add('fa-eye-slash');
                    }
                } else {
                    input.type = 'password';
                    if (icon) {
                        icon.classList.remove('fa-eye-slash');
                        icon.classList.add('fa-eye');
                    }
                }
            }
        });
    });

    // ========== FORMULÁRIO DE PERFIL ==========
    const profileForm = document.querySelector('#profile .form, #profile form');
    if (profileForm) {
        profileForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const button = this.querySelector('button[type="submit"]');
            const originalText = button ? button.textContent : '';
            
            // Feedback visual
            if (button) {
                button.textContent = 'Salvando...';
                button.disabled = true;
            }

            // Coleta dados do formulário
            const formData = {
                action: 'update_profile',
                username: document.getElementById('firstName')?.value || '',
                nome_completo: document.getElementById('lastName')?.value || '',
                email: document.getElementById('email')?.value || ''
            };

            // Faz requisição para o endpoint PHP
            fetch('process/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.json())
                .then(data => {
                    showNotification(data.message || 'Perfil atualizado com sucesso!', data.success ? 'success' : 'error');
                    if (data.success) {
                        setTimeout(() => location.reload(), 1500);
                    }
                })
                .catch(error => {
                    showNotification('Erro ao atualizar perfil', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    if (button) {
                        button.textContent = originalText;
                        button.disabled = false;
                    }
                });
        });
    }

    // ========== FORMULÁRIO DE SEGURANÇA (ALTERAR SENHA) ==========
    const securityForm = document.querySelector('#security .form, #security form');
    if (securityForm) {
        securityForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('currentPassword')?.value || '';
            const newPassword = document.getElementById('newPassword')?.value || '';
            const confirmPassword = document.getElementById('confirmPassword')?.value || '';
            const button = this.querySelector('button[type="submit"]');
            const originalText = button ? button.textContent : '';

            // Validação básica
            if (newPassword !== confirmPassword) {
                showNotification('Nova senha e confirmação não coincidem', 'error');
                return;
            }

            if (newPassword.length < 6) {
                showNotification('Nova senha deve ter pelo menos 6 caracteres', 'error');
                return;
            }

            // Feedback visual
            if (button) {
                button.textContent = 'Alterando...';
                button.disabled = true;
            }

            const formData = {
                action: 'change_password',
                current_password: currentPassword,
                new_password: newPassword,
                confirm_password: confirmPassword
            };

            // Faz requisição para o endpoint PHP
            fetch('process/update_profile.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify(formData)
            })
                .then(response => response.json())
                .then(data => {
                    showNotification(data.message || 'Senha alterada com sucesso!', data.success ? 'success' : 'error');
                    if (data.success) {
                        securityForm.reset();
                    }
                })
                .catch(error => {
                    showNotification('Erro ao alterar senha', 'error');
                    console.error('Error:', error);
                })
                .finally(() => {
                    if (button) {
                        button.textContent = originalText;
                        button.disabled = false;
                    }
                });
        });
    }

    // ========== ZONA DE PERIGO - BOTÕES DE AÇÕES PERIGOSAS ==========
    initializeDangerZone();

    // ========== LOGOUT ==========
    const logoutBtn = document.querySelector('.nav-item.logout');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', function() {
            if (confirm('Tem certeza que deseja sair?')) {
                window.location.href = 'process/logout.php';
            }
        });
    }

    // ========== ALTERAR FOTO ==========
    const cameraBtn = document.querySelector('.camera-btn');
    if (cameraBtn) {
        cameraBtn.addEventListener('click', function() {
            window.location.href = 'upload_foto.php';
        });
    }

    // ========== RETURN ==========
    const returnBtn = document.querySelector('.nav-item.return');
    if (returnBtn) {
        returnBtn.addEventListener('click', function() {
            window.location.href = 'index.php';
        });
    }

    // ========== HELP ==========
    const helpBtn = document.querySelector('.nav-item.help');
    if (helpBtn) {
        helpBtn.addEventListener('click', function() {
            window.location.href = 'https://hermes-neptune.github.io/site-produto/problemas.html';
        });
    }
});

// ========== FUNÇÕES DA ZONA DE PERIGO ==========

function initializeDangerZone() {
    const dangerButtons = document.querySelectorAll('.btn-danger');
    
    dangerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const buttonId = this.id;
            handleDangerAction(buttonId, this);
        });
    });
}

function handleDangerAction(buttonId, button) {
    let action, confirmMessage, warningMessage;
    
    if (buttonId === 'clear-all-data') {
        action = 'clear_all_data';
        confirmMessage = `Tem certeza que deseja limpar todos os seus dados?

• Todas as mensagens serão excluídas
• Todas as amizades serão removidas  
• Histórico de créditos será apagado
• Presenças serão removidas
• Configurações voltarão ao padrão

Sua conta permanecerá ativa. Esta ação não pode ser desfeita.`;
        warningMessage = 'ATENÇÃO: Esta ação limpará TODOS os seus dados!';
        
    } else if (buttonId === 'delete-account') {
        action = 'delete_account';
        confirmMessage = `ATENÇÃO: Você está prestes a EXCLUIR PERMANENTEMENTE sua conta!

• Todos os seus dados serão perdidos
• Não será possível recuperar a conta  
• Esta ação é IRREVERSÍVEL

Digite "CONFIRMO EXCLUSÃO" para prosseguir:`;
        warningMessage = 'PERIGO: Esta ação excluirá sua conta permanentemente!';
        
    } else {
        showNotification('Ação não reconhecida.', 'error');
        return;
    }
    
    if (!confirm(warningMessage + '\n\nDeseja continuar?')) {
        return;
    }
    
    if (buttonId === 'delete-account') {
        const userConfirmation = prompt(confirmMessage);
        if (userConfirmation !== 'CONFIRMO EXCLUSÃO') {
            showNotification('Exclusão cancelada. Texto de confirmação incorreto.', 'info');
            return;
        }
    } else {
        if (!confirm(confirmMessage)) {
            return;
        }
    }
    
    executeDangerAction(action, button);
}

function executeDangerAction(action, button) {
    const originalText = button.innerHTML;
    button.disabled = true;
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processando...';
    
    const scriptUrl = 'process/danger_actions.php';
    
    fetch(scriptUrl, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=${encodeURIComponent(action)}`
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            showNotification(data.message, 'success');
            
            if (data.redirect) {
                setTimeout(() => {
                    window.location.href = data.redirect_url;
                }, 2000);
            } else {
                setTimeout(() => {
                    window.location.reload();
                }, 2000);
            }
        } else {
            showNotification(data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Erro na requisição:', error);
        showNotification('Erro de conexão. Tente novamente.', 'error');
    })
    .finally(() => {
        button.disabled = false;
        button.innerHTML = originalText;
    });
}

// ========== SISTEMA DE NOTIFICAÇÕES APRIMORADO ==========
function showNotification(message, type = 'info') {
    // Remove notificação existente
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Cria nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Cores e ícones baseados no tipo
    const config = {
        success: { color: '#4CAF50', icon: 'fa-check-circle' },
        error: { color: '#f44336', icon: 'fa-exclamation-triangle' },
        warning: { color: '#ff9800', icon: 'fa-exclamation-circle' },
        info: { color: '#2196F3', icon: 'fa-info-circle' }
    };

    const currentConfig = config[type] || config.info;

    notification.innerHTML = `
        <div class="notification-content">
            <div class="notification-message">
                <i class="fas ${currentConfig.icon}" style="margin-right: 8px;"></i>
                <span>${message}</span>
            </div>
            <button class="notification-close" aria-label="Fechar notificação">&times;</button>
        </div>
    `;

    // Estilos da notificação
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${currentConfig.color};
        color: white;
        padding: 15px 20px;
        border-radius: 8px;
        box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        z-index: 10000;
        max-width: 400px;
        min-width: 300px;
        animation: slideIn 0.3s ease-out;
        font-family: inherit;
        font-size: 14px;
        line-height: 1.4;
    `;

    // Adiciona ao DOM
    document.body.appendChild(notification);

    // Event listener para fechar
    const closeBtn = notification.querySelector('.notification-close');
    closeBtn.addEventListener('click', () => {
        notification.style.animation = 'slideOut 0.3s ease-in';
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 300);
    });

    // Remove automaticamente após 5 segundos
    setTimeout(() => {
        if (notification.parentNode) {
            notification.style.animation = 'slideOut 0.3s ease-in';
            setTimeout(() => {
                if (notification.parentNode) {
                    notification.remove();
                }
            }, 300);
        }
    }, 5000);
}

// ========== ESTILOS CSS PARA NOTIFICAÇÕES ==========
const notificationStyles = document.createElement('style');
notificationStyles.textContent = `
    @keyframes slideIn {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOut {
        from {
            transform: translateX(0);
            opacity: 1;
        }
        to {
            transform: translateX(100%);
            opacity: 0;
        }
    }
    
    .notification-content {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 15px;
    }
    
    .notification-message {
        display: flex;
        align-items: center;
        flex: 1;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        font-size: 18px;
        cursor: pointer;
        padding: 0;
        width: 24px;
        height: 24px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background-color 0.2s ease;
        flex-shrink: 0;
    }
    
    .notification-close:hover {
        background-color: rgba(255, 255, 255, 0.2);
    }
    
    .notification-close:focus {
        outline: 2px solid rgba(255, 255, 255, 0.5);
        outline-offset: 2px;
    }
`;

// Adiciona os estilos apenas se ainda não existirem
if (!document.querySelector('#notification-styles')) {
    notificationStyles.id = 'notification-styles';
    document.head.appendChild(notificationStyles);
}

// ========== FUNÇÕES UTILITÁRIAS ==========

// Função para salvar configurações
function saveSettings(settings) {
    fetch('process/save_settings.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(settings)
    })
    .then(response => response.json())
    .then(data => {
        showNotification(data.message || 'Configurações salvas!', data.success ? 'success' : 'error');
    })
    .catch(error => {
        showNotification('Erro ao salvar configurações', 'error');
        console.error('Error:', error);
    });
}