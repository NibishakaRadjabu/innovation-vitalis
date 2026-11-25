(() => {
    const root = document.querySelector('[data-call-ui="patient"]');
    const input = document.querySelector('#patient-call-notes-input');
    const status = document.querySelector('[data-patient-note-status]');
    const clear = document.querySelector('[data-clear-patient-notes]');
    if (!root || !input) return;

    const room = root.dataset.room || '';
    const storageKey = `patient-call-notes:${room}`;
    try {
        input.value = localStorage.getItem(storageKey) || '';
    } catch {
        if (status) status.textContent = 'Local storage is unavailable. Notes will not persist after you leave.';
    }

    input.addEventListener('input', () => {
        try {
            localStorage.setItem(storageKey, input.value);
            if (status) status.textContent = 'Saved in this browser on this device.';
        } catch {
            if (status) status.textContent = 'Could not save on this device. Copy any notes you want to keep.';
        }
    });

    clear?.addEventListener('click', () => {
        input.value = '';
        try {
            localStorage.removeItem(storageKey);
            if (status) status.textContent = 'Local notes cleared.';
        } catch {
            if (status) status.textContent = 'Could not clear local notes.';
        }
    });
})();
