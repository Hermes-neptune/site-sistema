class EmojiSystem {
    constructor() {
        this.customEmojis = [
            { name: 'happy', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/feliz.png', category: 'expressions' },
            { name: 'love', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/love.png', category: 'expressions' },
            { name: 'chorando', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/chorando.png', category: 'expressions' },
            { name: 'mil_jardas', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/mil_jardas.png', category: 'expressions' },
            { name: 'paz', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/paz.png', category: 'expressions' },
            { name: 'surpreso', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/surpreso.png', category: 'expressions' },
            { name: 'triste', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/triste.png', category: 'expressions' },
            { name: 'triste_2', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/triste_2.png', category: 'expressions' },
            { name: 'surpreso_2', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/surpreso_2.png', category: 'expressions' },
            { name: 'bravo', url: 'https://lfcostldktmoevensqdj.supabase.co/storage/v1/object/public/emoji/bravo.png', category: 'expressions' },

            { name: 'fire', url: 'emojis/custom/fire.png', category: 'objects' },
            { name: 'star', url: 'emojis/custom/star.png', category: 'objects' },
            { name: 'heart', url: 'emojis/custom/heart.png', category: 'objects' },
        ];

        this.standardEmojis = [
            { name: 'smile', emoji: '😊', category: 'expressions' },
            { name: 'laugh', emoji: '😂', category: 'expressions' },
            { name: 'wink', emoji: '😉', category: 'expressions' },
            { name: 'thumbs_up', emoji: '👍', category: 'gestures' },
            { name: 'thumbs_down', emoji: '👎', category: 'gestures' },
            { name: 'clap', emoji: '👏', category: 'gestures' },
            { name: 'wave', emoji: '👋', category: 'gestures' },
            { name: 'peace', emoji: '✌️', category: 'gestures' },
            { name: 'ok', emoji: '👌', category: 'gestures' },
            { name: 'fire_std', emoji: '🔥', category: 'objects' },
            { name: 'star_std', emoji: '⭐', category: 'objects' },
            { name: 'heart_std', emoji: '❤️', category: 'objects' },
            { name: 'pizza', emoji: '🍕', category: 'food' },
            { name: 'coffee', emoji: '☕', category: 'food' },
            { name: 'cake', emoji: '🎂', category: 'food' },
        ];

        this.categories = {
            'expressions': 'Expressões',
            'gestures': 'Gestos',
            'objects': 'Objetos',
            'food': 'Comida'
        };

        this.isOpen = false;
        this.currentCategory = 'expressions';
        this.init();
    }

    init() {
        this.createEmojiPicker();
        this.bindEvents();
    }

    createEmojiPicker() {
        const emojiPickerHTML = `
            <div class="emoji-picker" id="emoji-picker" style="display: none;">
                <div class="emoji-picker-header">
                    <div class="emoji-categories">
                        ${Object.entries(this.categories).map(([key, name]) => `
                            <button class="emoji-category-btn ${key === this.currentCategory ? 'active' : ''}" 
                                    data-category="${key}">
                                ${name}
                            </button>
                        `).join('')}
                    </div>
                </div>
                <div class="emoji-picker-content">
                    <div class="emoji-grid" id="emoji-grid">
                        ${this.renderEmojis(this.currentCategory)}
                    </div>
                </div>
            </div>
        `;

        const messageForm = document.getElementById('message-form');
        if (messageForm) {
            const emojiButton = document.createElement('button');
            emojiButton.type = 'button';
            emojiButton.className = 'emoji-button';
            emojiButton.innerHTML = '😊';
            emojiButton.title = 'Adicionar emoji';
            
            const submitButton = messageForm.querySelector('button[type="submit"]');
            messageForm.insertBefore(emojiButton, submitButton);
            
            messageForm.insertAdjacentHTML('afterend', emojiPickerHTML);
        }
    }

    renderEmojis(category) {
        const customEmojis = this.customEmojis.filter(emoji => emoji.category === category);
        const standardEmojis = this.standardEmojis.filter(emoji => emoji.category === category);
        
        let html = '';
        
        if (customEmojis.length > 0) {
            html += '<div class="emoji-section-title">Personalizados</div>';
            customEmojis.forEach(emoji => {
                html += `
                    <div class="emoji-item custom-emoji" data-emoji=":${emoji.name}:" title="${emoji.name}">
                        <img src="${emoji.url}" alt="${emoji.name}">
                    </div>
                `;
            });
        }
        
        if (standardEmojis.length > 0) {
            html += '<div class="emoji-section-title">Padrão</div>';
            standardEmojis.forEach(emoji => {
                html += `
                    <div class="emoji-item standard-emoji" data-emoji="${emoji.emoji}" title="${emoji.name}">
                        ${emoji.emoji}
                    </div>
                `;
            });
        }
        
        return html;
    }

    bindEvents() {
        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('emoji-button')) {
                e.preventDefault();
                this.togglePicker();
            }
            
            if (!e.target.closest('.emoji-picker') && !e.target.classList.contains('emoji-button')) {
                this.closePicker();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.classList.contains('emoji-category-btn')) {
                this.currentCategory = e.target.dataset.category;
                this.updateCategoryButtons();
                this.updateEmojiGrid();
            }
        });

        document.addEventListener('click', (e) => {
            if (e.target.closest('.emoji-item')) {
                const emojiItem = e.target.closest('.emoji-item');
                const emojiData = emojiItem.dataset.emoji;
                this.insertEmoji(emojiData);
                this.closePicker();
            }
        });
    }

    togglePicker() {
        const picker = document.getElementById('emoji-picker');
        if (this.isOpen) {
            this.closePicker();
        } else {
            this.openPicker();
        }
    }

    openPicker() {
        const picker = document.getElementById('emoji-picker');
        picker.style.display = 'block';
        this.isOpen = true;
        
        setTimeout(() => {
            picker.classList.add('show');
        }, 10);
    }

    closePicker() {
        const picker = document.getElementById('emoji-picker');
        picker.classList.remove('show');
        setTimeout(() => {
            picker.style.display = 'none';
            this.isOpen = false;
        }, 200);
    }

    updateCategoryButtons() {
        document.querySelectorAll('.emoji-category-btn').forEach(btn => {
            btn.classList.toggle('active', btn.dataset.category === this.currentCategory);
        });
    }

    updateEmojiGrid() {
        const grid = document.getElementById('emoji-grid');
        grid.innerHTML = this.renderEmojis(this.currentCategory);
    }

    insertEmoji(emojiData) {
        const messageInput = document.getElementById('message-input');
        if (messageInput) {
            const currentValue = messageInput.value;
            const cursorPosition = messageInput.selectionStart;
            const newValue = currentValue.slice(0, cursorPosition) + emojiData + currentValue.slice(cursorPosition);
            
            messageInput.value = newValue;
            messageInput.focus();
            
            const newCursorPosition = cursorPosition + emojiData.length;
            messageInput.setSelectionRange(newCursorPosition, newCursorPosition);
        }
    }

    processMessageEmojis(message) {
        let processedMessage = message.replace(/:(\w+):/g, (match, emojiName) => {
            const customEmoji = this.customEmojis.find(emoji => emoji.name === emojiName);
            if (customEmoji) {
                return `<img src="${customEmoji.url}" alt="${emojiName}" class="emoji-inline" title="${emojiName}">`;
            }
        });

        return processedMessage;
    }

    addCustomEmoji(name, url, category = 'expressions') {
        this.customEmojis.push({ name, url, category });
        
        if (!(category in this.categories)) {
            this.categories[category] = category.charAt(0).toUpperCase() + category.slice(1);
        }
        
        if (this.isOpen && this.currentCategory === category) {
            this.updateEmojiGrid();
        }
    }

    removeCustomEmoji(name) {
        this.customEmojis = this.customEmojis.filter(emoji => emoji.name !== name);
        
        if (this.isOpen) {
            this.updateEmojiGrid();
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    if (document.getElementById('message-form')) {
        window.emojiSystem = new EmojiSystem();
        
    }
});