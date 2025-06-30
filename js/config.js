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

    // ========== SWITCHES/TOGGLES DE CONFIGURAÇÃO ==========
    const switches = document.querySelectorAll('.switch input[type="checkbox"]');
    switches.forEach(switchInput => {
        switchInput.addEventListener('change', function() {
            const settingItem = this.closest('.setting-item');
            const settingTitle = settingItem?.querySelector('.setting-title')?.textContent || 'Configuração';
            
            console.log(`${settingTitle} ${this.checked ? 'ativada' : 'desativada'}`);
        
            showNotification(`${settingTitle} ${this.checked ? 'ativada' : 'desativada'}`, 'info');
        });
    });

    // ========== BOTÕES DE PERIGO ==========
    const dangerButtons = document.querySelectorAll('.btn-danger');
    dangerButtons.forEach(button => {
        button.addEventListener('click', function() {
            const action = this.textContent.trim();
            const confirmMessage = `Tem certeza que deseja ${action.toLowerCase()}? Esta ação não pode ser desfeita.`;
            
            if (confirm(confirmMessage)) {
                showNotification(`${action} solicitada. Entre em contato com o suporte para prosseguir.`, 'warning');
            }
        });
    });

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
            window.location.href = 'protected.php';
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

// ========== SISTEMA DE NOTIFICAÇÕES ==========
function showNotification(message, type = 'info') {
    // Remove notificação existente
    const existing = document.querySelector('.notification');
    if (existing) {
        existing.remove();
    }

    // Cria nova notificação
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    
    // Cores baseadas no tipo
    const colors = {
        success: '#4CAF50',
        error: '#f44336',
        warning: '#ff9800',
        info: '#2196F3'
    };

    notification.innerHTML = `
        <div class="notification-content">
            <span>${message}</span>
            <button class="notification-close" aria-label="Fechar notificação">&times;</button>
        </div>
    `;

    // Estilos da notificação
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${colors[type] || colors.info};
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