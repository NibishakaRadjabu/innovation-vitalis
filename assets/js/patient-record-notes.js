(() => {
    const input = document.querySelector('#patient-record-notes');
    const status = document.querySelector('[data-record-note-status]');
    const clear = document.querySelector('[data-clear-record-notes]');
    const patientId = document.querySelector('[data-patient-records]')?.dataset.patientRecords;
    if (!input || !patientId) return;

    const key = `patient-personal-record-notes:${patientId}`;
    try {
        input.value = localStorage.getItem(key) || '';
    } catch {
        if (status) status.textContent = 'Browser storage is unavailable. Notes will not be saved.';
    }

    input.addEventListener('input', () => {
        try {
            localStorage.setItem(key, input.value);
            if (status) status.textContent = 'Saved in this browser on this device.';
        } catch {
            if (status) status.textContent = 'Could not save on this device. Copy any notes you want to keep.';
        }
    });

    clear?.addEventListener('click', () => {
        input.value = '';
        try {
            localStorage.removeItem(key);
            if (status) status.textContent = 'Personal notes cleared from this device.';
        } catch {
            if (status) status.textContent = 'Could not clear local notes.';
        }
    });
})();
