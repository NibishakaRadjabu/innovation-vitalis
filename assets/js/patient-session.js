(() => {
    const timeoutMs = 15 * 60 * 1000;
    const csrf = document.querySelector('meta[name="patient-csrf"]')?.content || '';
    let lastActivity = Date.now();
    let redirecting = false;
    const touch = () => { lastActivity = Date.now(); };
    ['pointerdown', 'keydown', 'touchstart', 'scroll'].forEach((eventName) => {
        window.addEventListener(eventName, touch, { passive: true });
    });
    window.setInterval(() => {
        if (redirecting) return;
        if (Date.now() - lastActivity >= timeoutMs) {
            redirecting = true;
            window.location.href = 'patient_logout.php?reason=inactive';
            return;
        }
        if (document.visibilityState !== 'visible' || !csrf) return;
        fetch('patient-session-ping.php', {
            method: 'POST',
            credentials: 'same-origin',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({ csrf_token: csrf }),
        }).then((response) => {
            if (response.redirected || response.status === 401) {
                redirecting = true;
                window.location.href = 'login.php?account=patient&expired=1';
            }
        }).catch(() => {});
    }, 2 * 60 * 1000);

    const profileInput = document.querySelector('[data-profile-picture]');
    const profilePreview = document.querySelector('[data-profile-photo]');
    let profileImageUrl = '';
    profileInput?.addEventListener('change', () => {
        const file = profileInput.files?.[0];
        if (!file || !file.type.startsWith('image/')) return;
        if (profileImageUrl) URL.revokeObjectURL(profileImageUrl);
        profileImageUrl = URL.createObjectURL(file);
        const image = document.createElement('img');
        image.src = profileImageUrl;
        image.alt = 'Profile picture preview';
        profilePreview.replaceChildren(image);
    });
})();
