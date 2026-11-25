(() => {
    const password = document.querySelector('[data-password-input]');
    const confirmation = document.querySelector('[name="password_confirmation"]');
    const meter = document.querySelector('[data-password-meter]');
    const label = document.querySelector('[data-password-strength-text]');
    const check = () => {
        if (!password || !meter || !label) return;
        const value = password.value;
        let score = 0;
        if (value.length >= 12) score += 1;
        if (value.length >= 16) score += 1;
        if (/[a-z]/.test(value) && /[A-Z]/.test(value)) score += 1;
        if (/\d/.test(value)) score += 1;
        if (/[^A-Za-z0-9]/.test(value)) score += 1;
        meter.style.width = `${score * 20}%`;
        meter.dataset.strength = score < 2 ? 'weak' : score < 4 ? 'medium' : 'strong';
        label.textContent = value.length === 0 ? 'Use at least 12 characters.' : score < 2 ? 'Weak password' : score < 4 ? 'Fair password' : 'Strong password';
        confirmation?.setCustomValidity(confirmation.value && confirmation.value !== value ? 'Passwords do not match.' : '');
    };
    password?.addEventListener('input', check);
    confirmation?.addEventListener('input', check);
})();
