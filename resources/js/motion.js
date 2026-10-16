import gsap from 'gsap';
import { ScrollTrigger } from 'gsap/ScrollTrigger';
import { SplitText } from 'gsap/SplitText';
import Lenis from 'lenis';

gsap.registerPlugin(ScrollTrigger, SplitText);

const MOBILE_MAX = 768;

function prefersReducedMotion() {
    return window.matchMedia('(prefers-reduced-motion: reduce)').matches;
}

function isMobile() {
    return window.innerWidth <= MOBILE_MAX;
}

function parseStatValue(raw) {
    const text = String(raw ?? '').trim();
    const match = text.match(/^(\d+(?:\.\d+)?)(.*)$/);
    if (! match) {
        return { target: 0, suffix: text, decimals: 0 };
    }

    const target = Number(match[1]);
    const suffix = match[2] ?? '';
    const decimals = match[1].includes('.') ? (match[1].split('.')[1]?.length ?? 0) : 0;

    return { target, suffix, decimals };
}

let lenisInstance = null;
let splitInstances = [];

function destroySplits() {
    splitInstances.forEach((instance) => {
        try {
            instance.revert();
        } catch {
            // ignore
        }
    });
    splitInstances = [];
}

function initLenis() {
    if (prefersReducedMotion() || isMobile()) {
        return null;
    }

    const lenis = new Lenis({
        duration: 1.1,
        smoothWheel: true,
        syncTouch: false,
    });

    lenis.on('scroll', ScrollTrigger.update);

    gsap.ticker.add((time) => {
        lenis.raf(time * 1000);
    });
    gsap.ticker.lagSmoothing(0);

    lenisInstance = lenis;
    return lenis;
}

function animateHeroHeadline(root) {
    if (! root || prefersReducedMotion()) {
        return;
    }

    const headline = root.querySelector('[data-hero-headline]');
    if (! headline || headline.dataset.splitDone === '1') {
        return;
    }

    destroySplits();

    const reduce = prefersReducedMotion();
    const locale = document.documentElement.lang || 'en';
    const type = locale.startsWith('zh') ? 'chars' : 'words';

    headline.dataset.splitDone = '1';
    gsap.set(headline, { opacity: 1 });

    const split = new SplitText(headline, { type, linesClass: 'split-line' });
    splitInstances.push(split);

    const targets = type === 'chars' ? split.chars : split.words;
    if (! targets?.length) {
        return;
    }

    gsap.fromTo(
        targets,
        { yPercent: reduce ? 0 : 110, opacity: 0 },
        {
            yPercent: 0,
            opacity: 1,
            duration: reduce ? 0.4 : 0.85,
            stagger: reduce ? 0 : (type === 'chars' ? 0.02 : 0.06),
            ease: 'power3.out',
        },
    );
}

function initHeroSplit() {
    const heroRoot = document.querySelector('[data-home-hero]');
    const firstHeadline = heroRoot
        ? heroRoot.querySelector('[data-hero-copy-item].is-active [data-hero-headline], [data-hero-copy-item]:not([hidden]) [data-hero-headline]')
        : document.querySelector('[data-hero-headline]');

    if (firstHeadline) {
        animateHeroHeadline(firstHeadline.closest('[data-hero-copy-item]') || firstHeadline.parentElement);
    }

    if (! heroRoot) {
        return;
    }

    const observer = new MutationObserver(() => {
        const active = heroRoot.querySelector('[data-hero-copy-item].is-active [data-hero-headline]');
        if (active) {
            active.dataset.splitDone = '';
            animateHeroHeadline(active.closest('[data-hero-copy-item]'));
        }
    });

    const copyPanel = heroRoot.querySelector('[data-hero-copy]');
    if (copyPanel) {
        observer.observe(copyPanel, { attributes: true, subtree: true, attributeFilter: ['class', 'hidden'] });
    }

    heroRoot.querySelectorAll('[data-hero-dot], [data-hero-prev], [data-hero-next]').forEach((btn) => {
        btn.addEventListener('click', () => {
            window.setTimeout(() => {
                const active = heroRoot.querySelector('[data-hero-copy-item].is-active [data-hero-headline]');
                if (active) {
                    active.dataset.splitDone = '';
                    animateHeroHeadline(active.closest('[data-hero-copy-item]'));
                }
            }, 50);
        });
    });
}

function initReveals() {
    const reduce = prefersReducedMotion();

    document.querySelectorAll('[data-reveal]').forEach((el) => {
        const children = el.querySelectorAll('[data-reveal-child]');
        const targets = children.length ? children : [el];

        gsap.set(targets, { opacity: reduce ? 1 : 0, y: reduce ? 0 : 40 });

        ScrollTrigger.create({
            trigger: el,
            start: 'top 85%',
            once: true,
            onEnter: () => {
                gsap.to(targets, {
                    opacity: 1,
                    y: 0,
                    duration: reduce ? 0.35 : 0.8,
                    stagger: reduce ? 0 : 0.08,
                    ease: 'power3.out',
                });
            },
        });
    });
}

function initCountUp() {
    document.querySelectorAll('[data-count]').forEach((el) => {
        const raw = el.dataset.count ?? el.textContent;
        const { target, suffix, decimals } = parseStatValue(raw);
        const reduce = prefersReducedMotion();

        if (reduce) {
            el.textContent = raw;
            return;
        }

        const obj = { val: 0 };

        ScrollTrigger.create({
            trigger: el,
            start: 'top 90%',
            once: true,
            onEnter: () => {
                gsap.to(obj, {
                    val: target,
                    duration: 1.6,
                    ease: 'power2.out',
                    onUpdate: () => {
                        const n = decimals ? obj.val.toFixed(decimals) : Math.round(obj.val);
                        el.textContent = `${n}${suffix}`;
                    },
                });
            },
        });
    });
}

function initMarquee() {
    document.querySelectorAll('[data-marquee]').forEach((root) => {
        const track = root.querySelector('[data-marquee-track]');
        if (! track || prefersReducedMotion()) {
            return;
        }

        const items = track.innerHTML;
        track.innerHTML = `${items}${items}`;

        const distance = track.scrollWidth / 2;
        const tween = gsap.to(track, {
            x: -distance,
            duration: Math.max(18, distance / 45),
            ease: 'none',
            repeat: -1,
        });

        root.addEventListener('mouseenter', () => tween.pause());
        root.addEventListener('mouseleave', () => tween.play());
    });
}

function initMagnetic() {
    if (prefersReducedMotion() || isMobile()) {
        return;
    }

    document.querySelectorAll('[data-magnetic]').forEach((btn) => {
        const strength = Number(btn.dataset.magneticStrength) || 8;

        btn.addEventListener('mousemove', (event) => {
            const rect = btn.getBoundingClientRect();
            const x = event.clientX - rect.left - rect.width / 2;
            const y = event.clientY - rect.top - rect.height / 2;
            gsap.to(btn, {
                x: (x / rect.width) * strength,
                y: (y / rect.height) * strength,
                duration: 0.35,
                ease: 'power3.out',
            });
        });

        btn.addEventListener('mouseleave', () => {
            gsap.to(btn, { x: 0, y: 0, duration: 0.5, ease: 'power3.out' });
        });
    });
}

function initProcessPin() {
    const root = document.querySelector('[data-process-pin]');
    if (! root || prefersReducedMotion() || isMobile()) {
        return;
    }

    const track = root.querySelector('[data-process-track]');
    const panels = root.querySelectorAll('[data-process-step]');
    if (! track || panels.length < 2) {
        return;
    }

    const totalScroll = (panels.length - 1) * window.innerWidth * 0.75;

    gsap.to(track, {
        x: () => -(track.scrollWidth - window.innerWidth + 64),
        ease: 'none',
        scrollTrigger: {
            trigger: root,
            start: 'top top',
            end: () => `+=${totalScroll}`,
            pin: true,
            scrub: 0.6,
            invalidateOnRefresh: true,
        },
    });
}

function initPageTransition() {
    const overlay = document.querySelector('[data-page-transition]');
    if (! overlay || prefersReducedMotion()) {
        return;
    }

    const show = () => {
        overlay.classList.add('is-active');
        return new Promise((resolve) => {
            window.setTimeout(resolve, 380);
        });
    };

    document.querySelectorAll('a[href]').forEach((link) => {
        const href = link.getAttribute('href');
        if (! href || href.startsWith('#') || link.target === '_blank' || link.hasAttribute('download')) {
            return;
        }

        let url;
        try {
            url = new URL(href, window.location.origin);
        } catch {
            return;
        }

        if (url.origin !== window.location.origin) {
            return;
        }

        link.addEventListener('click', (event) => {
            if (event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            show().then(() => {
                window.location.href = url.href;
            });
        });
    });

    window.addEventListener('pageshow', () => {
        overlay.classList.remove('is-active');
    });
}

export function initMotion() {
    initLenis();
    initHeroSplit();
    initReveals();
    initCountUp();
    initMarquee();
    initMagnetic();
    initProcessPin();
    initPageTransition();

    ScrollTrigger.refresh();
}

window.sgMotion = { animateHeroHeadline, refresh: () => ScrollTrigger.refresh() };
