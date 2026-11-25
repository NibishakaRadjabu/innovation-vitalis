(() => {
    const panels = [...document.querySelectorAll('[data-verification-panel]')];
    const steps = [...document.querySelectorAll('[data-verification-step]')];
    const next = document.querySelector('[data-verification-next]');
    const back = document.querySelector('[data-verification-back]');
    const progress = document.querySelector('[data-verification-progress]');
    const fileInput = document.querySelector('#identity-document');
    const dropzone = document.querySelector('[data-document-drop]');
    const preview = document.querySelector('[data-document-preview]');
    const thumbnail = document.querySelector('[data-document-thumbnail]');
    const error = document.querySelector('[data-document-error]');
    let currentStep = 0;
    let previewUrl = '';

    const showStep = (step) => {
        currentStep = Math.max(0, Math.min(panels.length - 1, step));
        panels.forEach((panel, index) => { panel.hidden = index !== currentStep; });
        steps.forEach((button, index) => {
            button.classList.toggle('is-current', index === currentStep);
            if (index === currentStep) button.setAttribute('aria-current', 'step');
            else button.removeAttribute('aria-current');
        });
        if (progress) progress.style.width = `${(currentStep / (panels.length - 1)) * 100}%`;
        if (back) back.disabled = currentStep === 0;
        if (next) next.innerHTML = currentStep === panels.length - 1 ? 'Return to portal <span aria-hidden="true">&#8599;</span>' : 'Continue <span aria-hidden="true">&#8594;</span>';
    };

    function setDocument(file) {
        if (!file || !preview || !thumbnail) return;
        error.hidden = true;
        const isImage = file.type.startsWith('image/');
        const isPdf = file.type === 'application/pdf' || file.name.toLowerCase().endsWith('.pdf');
        if ((!isImage && !isPdf) || file.size > 5 * 1024 * 1024) {
            error.textContent = !isImage && !isPdf ? 'Choose an image or PDF document.' : 'Choose a document smaller than 5 MB.';
            error.hidden = false;
            fileInput.value = '';
            return;
        }
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        thumbnail.replaceChildren();
        if (isImage) {
            previewUrl = URL.createObjectURL(file);
            const image = document.createElement('img');
            image.src = previewUrl;
            image.alt = 'Local preview of selected identity document';
            thumbnail.appendChild(image);
        } else {
            const pdf = document.createElement('span');
            pdf.textContent = 'PDF';
            pdf.className = 'pdf-thumbnail';
            thumbnail.appendChild(pdf);
        }
        document.querySelector('[data-document-name]').textContent = file.name;
        preview.hidden = false;
    }

    fileInput?.addEventListener('change', () => setDocument(fileInput.files?.[0]));
    document.querySelector('[data-remove-document]')?.addEventListener('click', () => {
        if (previewUrl) URL.revokeObjectURL(previewUrl);
        previewUrl = '';
        if (fileInput) fileInput.value = '';
        preview.hidden = true;
        thumbnail.replaceChildren();
    });
    ['dragenter', 'dragover'].forEach((name) => dropzone?.addEventListener(name, (event) => {
        event.preventDefault();
        dropzone.classList.add('is-dragging');
    }));
    ['dragleave', 'drop'].forEach((name) => dropzone?.addEventListener(name, (event) => {
        event.preventDefault();
        dropzone.classList.remove('is-dragging');
    }));
    dropzone?.addEventListener('drop', (event) => setDocument(event.dataTransfer?.files?.[0]));
    steps.forEach((button) => button.addEventListener('click', () => showStep(Number(button.dataset.verificationStep))));
    back?.addEventListener('click', () => showStep(currentStep - 1));
    next?.addEventListener('click', () => {
        if (currentStep === panels.length - 1) window.location.href = 'patientpage.php';
        else showStep(currentStep + 1);
    });
    showStep(0);
})();
