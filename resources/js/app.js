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
    initHomeHero();
    initPdfPreview();
});

function initHomeHero() {
    const root = document.querySelector('[data-home-hero]');
    if (! root) {
        return;
    }

    const slides = Array.from(root.querySelectorAll('[data-hero-slide]'));
    const copies = Array.from(root.querySelectorAll('[data-hero-copy-item]'));
    const dots = Array.from(root.querySelectorAll('[data-hero-dot]'));
    const total = slides.length;

    if (total < 2) {
        return;
    }

    const autoplay = root.dataset.autoplay === '1';
    const speed = Math.max(2000, Number(root.dataset.speed) || 5000);
    let index = 0;
    let timer = null;

    const goTo = (nextIndex) => {
        index = ((nextIndex % total) + total) % total;

        slides.forEach((slide, i) => {
            slide.classList.toggle('is-active', i === index);
        });

        copies.forEach((copy, i) => {
            const active = i === index;
            copy.classList.toggle('is-active', active);
            copy.hidden = ! active;
        });

        dots.forEach((dot) => {
            dot.classList.toggle('is-active', Number(dot.dataset.heroDot) === index);
        });
    };

    const restart = () => {
        if (! autoplay) {
            return;
        }

        window.clearInterval(timer);
        timer = window.setInterval(() => goTo(index + 1), speed);
    };

    root.querySelector('[data-hero-prev]')?.addEventListener('click', () => {
        goTo(index - 1);
        restart();
    });

    root.querySelector('[data-hero-next]')?.addEventListener('click', () => {
        goTo(index + 1);
        restart();
    });

    dots.forEach((dot) => {
        dot.addEventListener('click', () => {
            goTo(Number(dot.dataset.heroDot) || 0);
            restart();
        });
    });

    goTo(0);
    restart();
}

function initPdfPreview() {
    const root = document.querySelector('[data-pdf-preview]');
    if (! root) {
        return;
    }

    const url = root.dataset.pdfUrl;
    const stage = root.querySelector('[data-pdf-stage]');
    if (! url || ! stage) {
        return;
    }

    const watermark = (() => {
        try {
            return JSON.parse(root.dataset.watermark || '{}');
        } catch {
            return {};
        }
    })();

    const blockEvent = (event) => {
        event.preventDefault();
        event.stopPropagation();
        return false;
    };

    root.addEventListener('contextmenu', blockEvent);
    root.addEventListener('dragstart', blockEvent);
    root.addEventListener('copy', blockEvent);

    const onKeyDown = (event) => {
        const key = event.key.toLowerCase();
        if ((event.ctrlKey || event.metaKey) && ['s', 'p', 'c', 'u', 'o'].includes(key)) {
            event.preventDefault();
        }
        if (key === 'f12' || (event.ctrlKey && event.shiftKey && ['i', 'j', 'c'].includes(key))) {
            event.preventDefault();
        }
    };

    document.addEventListener('keydown', onKeyDown);

    const loadPdfJs = async () => {
        const pdfjs = await import(/* @vite-ignore */ 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.min.mjs');
        pdfjs.GlobalWorkerOptions.workerSrc = 'https://cdnjs.cloudflare.com/ajax/libs/pdf.js/4.8.69/pdf.worker.min.mjs';
        return pdfjs;
    };

    const loadWatermarkImage = () => new Promise((resolve) => {
        if (! watermark.image) {
            resolve(null);
            return;
        }

        const image = new Image();
        image.crossOrigin = 'anonymous';
        image.onload = () => resolve(image);
        image.onerror = () => resolve(null);
        image.src = watermark.image;
    });

    const stampCanvas = (canvas, image) => {
        const ctx = canvas.getContext('2d');
        if (! ctx) {
            return;
        }

        const opacity = Number(watermark.opacity ?? 0.18);
        const size = Math.max(24, Number(watermark.size ?? 140));
        const spacing = Math.max(20, Number(watermark.spacing ?? 90));
        const pattern = watermark.pattern === 'centered' ? 'centered' : 'tiled';

        ctx.save();
        ctx.globalAlpha = opacity;

        const drawOne = (x, y, stampSize) => {
            ctx.save();
            ctx.translate(x, y);
            ctx.rotate(-Math.PI / 4);

            if (image) {
                const ratio = image.width / Math.max(image.height, 1);
                const w = stampSize;
                const h = stampSize / ratio;
                ctx.drawImage(image, -w / 2, -h / 2, w, h);
            } else {
                ctx.font = `700 ${Math.max(18, stampSize * 0.28)}px Inter, sans-serif`;
                ctx.fillStyle = '#c9a84c';
                ctx.textAlign = 'center';
                ctx.textBaseline = 'middle';
                ctx.fillText(watermark.text || 'SINO GOOD', 0, 0);
            }

            ctx.restore();
        };

        if (pattern === 'centered') {
            drawOne(canvas.width / 2, canvas.height / 2, size * 1.6);
        } else {
            for (let y = 0; y < canvas.height + size; y += size + spacing) {
                for (let x = 0; x < canvas.width + size; x += size + spacing) {
                    drawOne(x, y, size);
                }
            }
        }

        ctx.restore();
    };

    (async () => {
        try {
            const [pdfjs, watermarkImage] = await Promise.all([loadPdfJs(), loadWatermarkImage()]);
            const pdf = await pdfjs.getDocument({ url, withCredentials: true }).promise;
            stage.innerHTML = '';

            for (let pageNumber = 1; pageNumber <= pdf.numPages; pageNumber++) {
                const page = await pdf.getPage(pageNumber);
                const viewport = page.getViewport({ scale: 1.35 });
                const canvas = document.createElement('canvas');
                canvas.className = 'pdf-protect__page';
                canvas.width = viewport.width;
                canvas.height = viewport.height;
                await page.render({ canvasContext: canvas.getContext('2d'), viewport }).promise;
                stampCanvas(canvas, watermarkImage);
                stage.appendChild(canvas);
            }
        } catch (error) {
            console.error(error);
            stage.innerHTML = `<iframe class="h-[80vh] w-full border-0" src="${url}#toolbar=0&navpanes=0&scrollbar=0" title="PDF preview"></iframe>`;
        }
    })();
}
