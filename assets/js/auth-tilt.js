(() => {
    const wrapper = document.querySelector('[data-auth-panel]');
    const card = wrapper?.querySelector('.t-tilt-card');
    if (!wrapper || !card) return;

    const reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)');
    const page = window.location.pathname.endsWith('register.php') ? 'register' : 'login';
    const arrival = sessionStorage.getItem('iv-auth-tilt-switch');

    if (arrival === page && !reduceMotion.matches) {
        sessionStorage.removeItem('iv-auth-tilt-switch');
        card.style.setProperty('--auth-enter-ry', page === 'login' ? '-12deg' : '12deg');
        card.classList.add('is-switching-in');
        card.addEventListener('animationend', () => card.classList.remove('is-switching-in'), { once: true });
    } else if (arrival) {
        sessionStorage.removeItem('iv-auth-tilt-switch');
    }

    if (reduceMotion.matches) return;

    const maxTilt = 10;
    const reset = () => {
        wrapper.classList.remove('is-hover');
        card.classList.remove('is-tilting');
        card.style.setProperty('--tilt-rx', '0deg');
        card.style.setProperty('--tilt-ry', '0deg');
    };
    const track = (event) => {
        if (event.pointerType !== 'mouse') return;
        const bounds = wrapper.getBoundingClientRect();
        if (!bounds.width || !bounds.height) return;
        const px = Math.min(1, Math.max(0, (event.clientX - bounds.left) / bounds.width));
        const py = Math.min(1, Math.max(0, (event.clientY - bounds.top) / bounds.height));
        wrapper.classList.add('is-hover');
        card.classList.add('is-tilting');
        card.style.setProperty('--tilt-ry', `${((px - 0.5) * maxTilt).toFixed(2)}deg`);
        card.style.setProperty('--tilt-rx', `${((0.5 - py) * maxTilt).toFixed(2)}deg`);
        card.style.setProperty('--tilt-gx', `${(px * 100).toFixed(1)}%`);
        card.style.setProperty('--tilt-gy', `${(py * 100).toFixed(1)}%`);
    };

    wrapper.addEventListener('pointermove', track);
    wrapper.addEventListener('pointerleave', (event) => {
        if (event.pointerType === 'mouse') reset();
    });

    document.querySelectorAll('[data-auth-switch]').forEach((link) => {
        link.addEventListener('click', (event) => {
            if (event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
            const destination = new URL(link.href, window.location.href);
            if (destination.origin !== window.location.origin) return;
            event.preventDefault();
            sessionStorage.setItem('iv-auth-tilt-switch', link.dataset.authSwitch);
            card.style.setProperty('--auth-exit-ry', link.dataset.authSwitch === 'login' ? '12deg' : '-12deg');
            card.classList.add('is-switching-out');
            window.setTimeout(() => window.location.assign(destination.href), 240);
        });
    });
})();
