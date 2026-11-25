(() => {
    const loader = document.querySelector('[data-transition-loader]');
    if (!loader) return;

    const CORNERS = [0, 3, 12, 15];
    const RING = [1, 2, 7, 11, 14, 13, 8, 4];
    const INNER = [5, 6, 9, 10];
    const TWINKLE = [7, 2, 11, 5, 14, 9, 0, 12, 3, 15, 6, 10, 13, 1, 8, 4];
    const cycle = parseFloat(getComputedStyle(document.documentElement).getPropertyValue('--matrix-cycle')) || 1200;

    document.querySelectorAll('.t-matrix').forEach((matrix) => {
        const variant = matrix.dataset.variant || 'scan';
        for (let index = 0; index < 16; index += 1) {
            const dot = document.createElement('i');
            const column = index % 4;
            if (matrix.dataset.rounded === 'true' && CORNERS.includes(index)) {
                dot.className = 'is-gap';
            } else if (variant === 'scan') {
                dot.style.setProperty('--d', String(Math.round(column * cycle / 10)));
            } else if (variant === 'twinkle') {
                dot.style.setProperty('--d', String(Math.round(TWINKLE[index] * cycle / 16)));
            } else if (variant === 'orbit') {
                const position = RING.indexOf(index);
                if (position < 0) dot.style.animation = 'none';
                else dot.style.setProperty('--d', String(Math.round(position * cycle / 8)));
            } else if (variant === 'pulse') {
                const ring = INNER.includes(index) ? 0 : 1;
                dot.style.setProperty('--d', String(Math.round(ring * cycle * 0.16)));
            }
            matrix.appendChild(dot);
        }
    });

    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    let leaving = false;
    const showLoader = () => {
        if (leaving) return;
        leaving = true;
        loader.setAttribute('aria-hidden', 'false');
        loader.classList.add('is-active');
    };

    document.addEventListener('click', (event) => {
        const link = event.target.closest('a[href]');
        if (!link || event.defaultPrevented || event.button !== 0 || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) return;
        if (link.hasAttribute('data-auth-switch')) return;
        if (link.target && link.target !== '_self' || link.hasAttribute('download')) return;
        const destination = new URL(link.href, window.location.href);
        if (destination.origin !== window.location.origin || destination.pathname === window.location.pathname && destination.search === window.location.search && destination.hash) return;
        showLoader();
    });

    document.addEventListener('submit', (event) => {
        const form = event.target;
        if (!(form instanceof HTMLFormElement) || form.target && form.target !== '_self') return;
        const destination = new URL(form.action || window.location.href, window.location.href);
        if (destination.origin !== window.location.origin) return;
        event.preventDefault();
        showLoader();
        window.setTimeout(() => form.submit(), 220);
    });
})();
