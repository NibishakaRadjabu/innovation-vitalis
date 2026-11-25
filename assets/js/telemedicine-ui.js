(() => {
    const tabs = [...document.querySelectorAll('[data-panel-target]')];
    const panels = [...document.querySelectorAll('[data-session-panel]')];

    tabs.forEach((tab) => {
        tab.addEventListener('click', () => {
            tabs.forEach((item) => {
                const active = item === tab;
                item.classList.toggle('is-active', active);
                item.setAttribute('aria-selected', String(active));
            });
            panels.forEach((panel) => {
                panel.hidden = panel.id !== tab.dataset.panelTarget;
            });
        });
    });

    const toggles = [
        ['[data-toggle-mic]', '[data-mic-label]', 'Mute mic', 'Unmute mic'],
        ['[data-toggle-camera]', '[data-camera-label]', 'Turn camera off', 'Turn camera on'],
        ['[data-toggle-share]', '[data-share-label]', 'Share screen', 'Stop sharing'],
    ];

    toggles.forEach(([buttonSelector, labelSelector, normalLabel, activeLabel]) => {
        const button = document.querySelector(buttonSelector);
        const label = button?.querySelector(labelSelector);
        button?.addEventListener('click', () => {
            if (window.telemedicineControls && window.telemedicineControls[buttonSelector.includes('mic') ? 'mic' : 'camera'] && document.querySelector('[data-call-ui]')?.dataset.callUi === 'doctor' && !button.disabled) return;
            const active = button.getAttribute('aria-pressed') !== 'true';
            button.setAttribute('aria-pressed', String(active));
            if (label) label.textContent = active ? activeLabel : normalLabel;
        });
    });

    const chatForm = document.querySelector('[data-chat-form]');
    const chatInput = chatForm?.querySelector('input');
    const chatMessages = document.querySelector('[data-chat-messages]');
    chatForm?.addEventListener('submit', (event) => {
        event.preventDefault();
        const message = chatInput?.value.trim();
        if (!message || !chatMessages) return;

        if (typeof window.telemedicineSendChat === 'function' && document.querySelector('[data-call-ui]') && !window.telemedicineSendChat(message)) return;

        chatMessages.querySelector('.chat-empty')?.remove();
        if (typeof window.telemedicineSendChat === 'function' && document.querySelector('[data-call-ui]')) {
            chatInput.value = '';
            return;
        }
        const bubble = document.createElement('p');
        bubble.className = 'chat-message';
        bubble.textContent = message;
        chatMessages.appendChild(bubble);
        chatInput.value = '';
        chatMessages.scrollTop = chatMessages.scrollHeight;
    });

    document.querySelector('[data-save-note]')?.addEventListener('click', () => {
        const status = document.querySelector('[data-note-status]');
        if (status) status.textContent = 'Preview only. Notes have not been saved.';
    });

    const swapButton = document.querySelector('[data-swap-views]');
    let viewsSwapped = false;
    swapButton?.addEventListener('click', () => {
        viewsSwapped = !viewsSwapped;
        const mainAvatar = document.querySelector('[data-main-avatar]');
        const mainLabel = document.querySelector('[data-main-video-label]');
        const thumbAvatar = document.querySelector('[data-thumb-avatar]');
        const thumbLabel = document.querySelector('[data-thumb-video-label]');
        if (mainAvatar) mainAvatar.textContent = viewsSwapped ? 'DR' : 'IV';
        if (mainLabel) mainLabel.textContent = viewsSwapped ? 'Doctor video window' : 'Patient video window';
        if (thumbAvatar) thumbAvatar.textContent = viewsSwapped ? 'IV' : 'DR';
        if (thumbLabel) thumbLabel.textContent = viewsSwapped ? 'Patient video preview' : 'Your camera preview';
        swapButton.setAttribute('aria-pressed', String(viewsSwapped));
    });

    if (!document.querySelector('[data-call-ui]')) document.querySelector('[data-end-session]')?.addEventListener('click', () => {
        const confirmed = window.confirm('End this interface preview? No live call is connected.');
        if (!confirmed) return;
        const indicator = document.querySelector('.session-indicator');
        if (indicator) {
            indicator.innerHTML = '<i></i> Preview ended';
            indicator.classList.add('is-ended');
        }
    });
})();
