const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('confirm_password');
const strengthBar = document.getElementById('strengthBar');
const passwordMatch = document.getElementById('passwordMatch');
const submitBtn = document.getElementById('submitBtn');

// Verificar força da senha
function checkPasswordStrength(password) {
    let strength = 0;
    
    if (password.length >= 6) strength++;
    if (password.length >= 10) strength++;
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/\d/.test(password)) strength++;
    if (/[^A-Za-z0-9]/.test(password)) strength++;
    
    return strength;
}

// Atualizar indicador de força
function updateStrengthIndicator(strength) {
    strengthBar.className = 'password-strength-bar';
    
    if (strength <= 1) {
        strengthBar.classList.add('strength-weak');
    } else if (strength <= 2) {
        strengthBar.classList.add('strength-fair');
    } else if (strength <= 3) {
        strengthBar.classList.add('strength-good');
    } else {
        strengthBar.classList.add('strength-strong');
    }
}

// Verificar se as senhas coincidem
function checkPasswordMatch() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    
    if (confirmPassword === '') {
        passwordMatch.textContent = '';
        passwordMatch.className = 'password-match';
        return false;
    }
    
    if (password === confirmPassword) {
        passwordMatch.textContent = '✓ As senhas coincidem';
        passwordMatch.className = 'password-match match-success';
        return true;
    } else {
        passwordMatch.textContent = '✗ As senhas não coincidem';
        passwordMatch.className = 'password-match match-error';
        return false;
    }
}

// Validar formulário
function validateForm() {
    const password = passwordInput.value;
    const confirmPassword = confirmPasswordInput.value;
    const strength = checkPasswordStrength(password);
    const match = checkPasswordMatch();
    
    submitBtn.disabled = !(password.length >= 6 && match);
}

// Event listeners
passwordInput.addEventListener('input', function() {
    const strength = checkPasswordStrength(this.value);
    updateStrengthIndicator(strength);
    validateForm();
});

confirmPasswordInput.addEventListener('input', function() {
    validateForm();
});

// Feedback visual durante o envio
document.querySelector('form')?.addEventListener('submit', function(e) {
    const btn = document.getElementById('submitBtn');
    
    if (!btn.disabled) {
        btn.innerHTML = 'Redefinindo...';
        btn.disabled = true;
        btn.style.opacity = '0.7';
    }
});

validateForm();