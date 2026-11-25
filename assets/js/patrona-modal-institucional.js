document.querySelectorAll('[data-patrona-modal]').forEach(root => {
    const slides = [...root.querySelectorAll('[data-pm-slide]')];
    const counter = root.querySelector('[data-pm-counter]');
    const close = root.querySelector('[data-pm-close]');
    let current = 0;

    function stop(slide) {
        const video = slide.querySelector('video');
        const frame = slide.querySelector('[data-pm-video]');
        if (video) video.pause();
        if (frame) frame.removeAttribute('src');
    }

    function show(index) {
        stop(slides[current]);
        slides[current].hidden = true;
        current = (index + slides.length) % slides.length;
        slides[current].hidden = false;
        const frame = slides[current].querySelector('[data-pm-video]');
        if (frame) frame.src = frame.dataset.pmVideo;
        if (counter) counter.textContent = (current + 1) + ' / ' + slides.length;
    }

    root.querySelectorAll('[data-pm-step]').forEach(button => {
        button.addEventListener('click', () => show(current + Number(button.dataset.pmStep)));
    });

    if (close) {
        document.body.append(root);
        const previous = document.activeElement;
        const previousOverflow = document.body.style.overflow;
        root.hidden = false;
        document.body.style.overflow = 'hidden';
        close.focus();
        function hide() {
            stop(slides[current]);
            root.hidden = true;
            document.body.style.overflow = previousOverflow;
            previous?.focus();
        }
        close.addEventListener('click', hide);
        root.addEventListener('click', event => {
            if (event.target === root) hide();
        });
        document.addEventListener('keydown', event => {
            if (root.hidden) return;
            if (event.key === 'Escape') hide();
            if (event.key !== 'Tab') return;
            const controls = [...root.querySelectorAll('button, a[href], video[controls], iframe')].filter(el => !el.closest('[hidden]'));
            const first = controls[0];
            const last = controls[controls.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        });
    }

    show(0);
});
