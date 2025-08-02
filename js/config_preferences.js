class PreferencesManager {
    constructor() {
        this.userId = this.getUserId();
        this.preferences = {};
        this.privacy = {};
        this.init();
    }

    getUserId() {
        if (typeof USER_ID !== 'undefined' && USER_ID) {
            return USER_ID;
        }
        
        if (window.USER_ID) {
            return window.USER_ID;
        }
        
        return this.extractUserIdFromPage();
    }

    extractUserIdFromPage() {
        return sessionStorage.getItem('user_id') || '1'; 
    }

    async init() {
        if (!this.userId) {
            console.error('Não foi possível inicializar PreferencesManager: USER_ID não encontrado');
            this.showNotification('Erro de inicialização: usuário não identificado', 'error');
            return;
        }
        
        await this.loadPreferences();
        await this.loadPrivacy();
        this.bindEvents();
        this.updateUI();
    }

    async loadPreferences() {
        try {
            const response = await fetch('api_mobile/adm/get_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.userId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.preferences = data.preferences;
                console.log('Preferências carregadas:', this.preferences);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erro ao carregar preferências:', error);
            this.showNotification('Erro ao carregar preferências: ' + error.message, 'error');
            this.preferences = {
                email_notifications: true,
                push_notifications: true,
                message_notifications: true,
                credit_alerts: true
            };
        }
    }

    async loadPrivacy() {
        try {
            const response = await fetch('api_mobile/adm/get_privacy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.userId
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.privacy = data.privacy;
                console.log('Configurações de privacidade carregadas:', this.privacy);
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erro ao carregar configurações de privacidade:', error);
            this.showNotification('Erro ao carregar configurações de privacidade: ' + error.message, 'error');
            this.privacy = {
                public_profile: true,
                show_online_status: false,
                allow_direct_messages: true,
                share_activity: false
            };
        }
    }

    async updatePreferences(newPreferences) {
        try {
            const response = await fetch('api_mobile/adm/update_preferences.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.userId,
                    ...newPreferences
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.preferences = { ...this.preferences, ...data.preferences };
                this.showNotification('Preferências atualizadas com sucesso!', 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erro ao atualizar preferências:', error);
            this.showNotification('Erro ao salvar preferências: ' + error.message, 'error');
            return false;
        }
    }

    async updatePrivacy(newPrivacy) {
        try {
            const response = await fetch('api_mobile/adm/update_privacy.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    user_id: this.userId,
                    ...newPrivacy
                })
            });

            const data = await response.json();
            
            if (data.success) {
                this.privacy = { ...this.privacy, ...data.privacy };
                this.showNotification('Configurações de privacidade atualizadas com sucesso!', 'success');
                return true;
            } else {
                throw new Error(data.message);
            }
        } catch (error) {
            console.error('Erro ao atualizar configurações de privacidade:', error);
            this.showNotification('Erro ao salvar configurações de privacidade: ' + error.message, 'error');
            return false;
        }
    }

    bindEvents() {
        this.bindNotificationSwitches();
        this.bindPrivacySwitches();
    }

    bindNotificationSwitches() {
        const notificationSwitches = {
            'email_notifications': document.querySelector('#notifications .setting-item:nth-child(1) input[type="checkbox"]'),
            'push_notifications': document.querySelector('#notifications .setting-item:nth-child(2) input[type="checkbox"]'),
            'message_notifications': document.querySelector('#notifications .setting-item:nth-child(3) input[type="checkbox"]'),
            'credit_alerts': document.querySelector('#notifications .setting-item:nth-child(4) input[type="checkbox"]')
        };

        Object.entries(notificationSwitches).forEach(([key, element]) => {
            if (element) {
                element.addEventListener('change', async (e) => {
                    const isChecked = e.target.checked;
                    const updateData = { [key]: isChecked };
                    
                    this.setElementLoading(element, true);
                    
                    const success = await this.updatePreferences(updateData);
                    
                    if (!success) {
                        e.target.checked = !isChecked;
                    }
                    
                    this.setElementLoading(element, false);
                });
            }
        });
    }

    bindPrivacySwitches() {
        const privacySwitches = {
            'public_profile': document.querySelector('#privacy .setting-item:nth-child(1) input[type="checkbox"]'),
            'show_online_status': document.querySelector('#privacy .setting-item:nth-child(2) input[type="checkbox"]'),
            'allow_direct_messages': document.querySelector('#privacy .setting-item:nth-child(3) input[type="checkbox"]'),
            'share_activity': document.querySelector('#privacy .setting-item:nth-child(4) input[type="checkbox"]')
        };

        Object.entries(privacySwitches).forEach(([key, element]) => {
            if (element) {
                element.addEventListener('change', async (e) => {
                    const isChecked = e.target.checked;
                    const updateData = { [key]: isChecked };
                    
                    this.setElementLoading(element, true);
                    
                    const success = await this.updatePrivacy(updateData);
                    
                    if (!success) {
                        e.target.checked = !isChecked;
                    }
                    
                    this.setElementLoading(element, false);
                });
            }
        });
    }

    updateUI() {
        const notificationMappings = [
            { key: 'email_notifications', selector: '#notifications .setting-item:nth-child(1) input[type="checkbox"]' },
            { key: 'push_notifications', selector: '#notifications .setting-item:nth-child(2) input[type="checkbox"]' },
            { key: 'message_notifications', selector: '#notifications .setting-item:nth-child(3) input[type="checkbox"]' },
            { key: 'credit_alerts', selector: '#notifications .setting-item:nth-child(4) input[type="checkbox"]' }
        ];

        notificationMappings.forEach(({ key, selector }) => {
            const element = document.querySelector(selector);
            if (element && this.preferences.hasOwnProperty(key)) {
                element.checked = this.preferences[key];
            }
        });

        const privacyMappings = [
            { key: 'public_profile', selector: '#privacy .setting-item:nth-child(1) input[type="checkbox"]' },
            { key: 'show_online_status', selector: '#privacy .setting-item:nth-child(2) input[type="checkbox"]' },
            { key: 'allow_direct_messages', selector: '#privacy .setting-item:nth-child(3) input[type="checkbox"]' },
            { key: 'share_activity', selector: '#privacy .setting-item:nth-child(4) input[type="checkbox"]' }
        ];

        privacyMappings.forEach(({ key, selector }) => {
            const element = document.querySelector(selector);
            if (element && this.privacy.hasOwnProperty(key)) {
                element.checked = this.privacy[key];
            }
        });
    }

    setElementLoading(element, isLoading) {
        const settingItem = element.closest('.setting-item');
        if (settingItem) {
            if (isLoading) {
                settingItem.style.opacity = '0.6';
                settingItem.style.pointerEvents = 'none';
            } else {
                settingItem.style.opacity = '1';
                settingItem.style.pointerEvents = 'auto';
            }
        }
    }

    getPrivacyLabel(key) {
        const labels = {
            'public_profile': 'Perfil Público',
            'show_online_status': 'Mostrar Status Online',
            'allow_direct_messages': 'Permitir Mensagens Diretas',
            'share_activity': 'Compartilhar Atividade'
        };
        return labels[key] || key;
    }

    showNotification(message, type = 'info') {
        const notification = document.createElement('div');
        notification.className = `notification notification-${type}`;
        notification.innerHTML = `
            <div class="notification-content">
                <i class="fas ${this.getNotificationIcon(type)}"></i>
                <span>${message}</span>
                <button class="notification-close">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        `;

        notification.style.cssText = `
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 10000;
            background: ${type === 'success' ? '#4CAF50' : type === 'error' ? '#f44336' : '#2196F3'};
            color: white;
            padding: 15px 20px;
            border-radius: 5px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            align-items: center;
            gap: 10px;
            max-width: 400px;
            animation: slideInRight 0.3s ease;
        `;

        document.body.appendChild(notification);

        const closeBtn = notification.querySelector('.notification-close');
        closeBtn.addEventListener('click', () => {
            notification.remove();
        });

        setTimeout(() => {
            if (notification.parentNode) {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => notification.remove(), 300);
            }
        }, 5000);
    }

    getNotificationIcon(type) {
        const icons = {
            'success': 'fa-check-circle',
            'error': 'fa-exclamation-circle',
            'warning': 'fa-exclamation-triangle',
            'info': 'fa-info-circle'
        };
        return icons[type] || icons.info;
    }

    async updateSinglePreference(key, value) {
        return await this.updatePreferences({ [key]: value });
    }

    async updateSinglePrivacy(key, value) {
        return await this.updatePrivacy({ [key]: value });
    }

    getPreferences() {
        return { ...this.preferences };
    }

    getPrivacy() {
        return { ...this.privacy };
    }
}

if (!document.getElementById('notification-styles')) {
    const notificationStyles = `
    @keyframes slideInRight {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes slideOutRight {
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
        align-items: center;
        gap: 10px;
        width: 100%;
    }
    
    .notification-close {
        background: none;
        border: none;
        color: white;
        cursor: pointer;
        padding: 0;
        margin-left: auto;
        opacity: 0.8;
        transition: opacity 0.2s;
    }
    
    .notification-close:hover {
        opacity: 1;
    }
`;

    const styleSheet = document.createElement('style');
    styleSheet.id = 'notification-styles';
    styleSheet.textContent = notificationStyles;
    document.head.appendChild(styleSheet);
}

document.addEventListener('DOMContentLoaded', () => {
    if (window.preferencesManager) {
        return;
    }
    
    if (document.querySelector('.tab-content#notifications')) {
        window.preferencesManager = new PreferencesManager();
        console.log('PreferencesManager inicializado com sucesso');
    }
});

if (!window.PreferencesManager) {
    window.PreferencesManager = PreferencesManager;
}