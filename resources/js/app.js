import './bootstrap';

function initProductSliders(root = document) {
    root.querySelectorAll('[data-product-slider]').forEach((slider) => {
        if (slider.dataset.sliderReady === '1') {
            return;
        }

        const track = slider.querySelector('[data-slider-track]');
        if (! track) {
            return;
        }

        const slides = Array.from(track.children);
        const total = slides.length;
        if (total < 2) {
            return;
        }

        slider.dataset.sliderReady = '1';

        const localDots = Array.from(slider.querySelectorAll('[data-slider-dot]'));
        const thumbsRoot = slider.parentElement?.querySelector('[data-slider-thumbs]');
        const thumbDots = thumbsRoot
            ? Array.from(thumbsRoot.querySelectorAll('[data-slider-dot]'))
            : [];
        const allDots = [...localDots, ...thumbDots];

        let index = 0;
        let startX = 0;
        let deltaX = 0;
        let dragging = false;
        let suppressClick = false;

        const goTo = (nextIndex) => {
            index = ((nextIndex % total) + total) % total;
            track.style.transform = `translate3d(-${index * 100}%, 0, 0)`;

            allDots.forEach((dot) => {
                const active = Number(dot.dataset.sliderDot) === index;
                dot.classList.toggle('is-active', active);
            });

            slides.forEach((slide, i) => {
                const link = slide.matches('a') ? slide : slide.querySelector('a');
                if (! link) {
                    return;
                }

                if (i === index) {
                    link.removeAttribute('tabindex');
                    link.removeAttribute('aria-hidden');
                } else {
                    link.setAttribute('tabindex', '-1');
                    link.setAttribute('aria-hidden', 'true');
                }
            });
        };

        slider.querySelector('[data-slider-prev]')?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            goTo(index - 1);
        });

        slider.querySelector('[data-slider-next]')?.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();
            goTo(index + 1);
        });

        allDots.forEach((dot) => {
            dot.addEventListener('click', (event) => {
                event.preventDefault();
                event.stopPropagation();
                goTo(Number(dot.dataset.sliderDot) || 0);
            });
        });

        const onPointerDown = (clientX) => {
            dragging = true;
            startX = clientX;
            deltaX = 0;
            track.style.transition = 'none';
        };

        const onPointerMove = (clientX) => {
            if (! dragging) {
                return;
            }

            deltaX = clientX - startX;
            const percent = (deltaX / slider.offsetWidth) * 100;
            track.style.transform = `translate3d(calc(-${index * 100}% + ${percent}%), 0, 0)`;
        };

        const onPointerUp = () => {
            if (! dragging) {
                return;
            }

            dragging = false;
            track.style.transition = '';

            if (Math.abs(deltaX) > 40) {
                suppressClick = true;
                goTo(deltaX < 0 ? index + 1 : index - 1);
            } else {
                goTo(index);
            }

            window.setTimeout(() => {
                suppressClick = false;
                deltaX = 0;
            }, 50);
        };

        slider.addEventListener('touchstart', (event) => {
            onPointerDown(event.touches[0].clientX);
        }, { passive: true });

        slider.addEventListener('touchmove', (event) => {
            onPointerMove(event.touches[0].clientX);
        }, { passive: true });

        slider.addEventListener('touchend', onPointerUp);

        slider.addEventListener('mousedown', (event) => {
            if (event.button !== 0) {
                return;
            }
            onPointerDown(event.clientX);
        });

        window.addEventListener('mousemove', (event) => {
            onPointerMove(event.clientX);
        });

        window.addEventListener('mouseup', onPointerUp);

        track.addEventListener('click', (event) => {
            if (suppressClick || Math.abs(deltaX) > 10) {
                event.preventDefault();
                event.stopPropagation();
            }
        }, true);

        goTo(0);
    });
}

document.addEventListener('DOMContentLoaded', () => {
    initProductSliders();
});
