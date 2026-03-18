import { onMounted, onUnmounted } from 'vue';

/**
 * Composable for scroll-triggered reveal animations using IntersectionObserver.
 *
 * Usage in component:
 *   import { useScrollReveal } from '@/composables/useScrollReveal';
 *   useScrollReveal();
 *
 * In template, add `data-reveal` attribute to elements:
 *   <div data-reveal>Fades up</div>
 *   <div data-reveal="left">Slides from left</div>
 *   <div data-reveal="right">Slides from right</div>
 *   <div data-reveal="scale">Scales in</div>
 *   <div data-reveal-delay="100">With delay</div>
 *   <div data-reveal-delay="200">Staggered</div>
 */
export function useScrollReveal(options = {}) {
    const {
        threshold = 0.15,
        rootMargin = '0px 0px -40px 0px',
    } = options;

    let observer = null;

    onMounted(() => {
        observer = new IntersectionObserver(
            (entries) => {
                entries.forEach((entry) => {
                    if (entry.isIntersecting) {
                        const el = entry.target;
                        const delay = el.dataset.revealDelay || 0;

                        setTimeout(() => {
                            el.classList.add('revealed');
                        }, Number(delay));

                        observer.unobserve(el);
                    }
                });
            },
            { threshold, rootMargin }
        );

        document.querySelectorAll('[data-reveal]').forEach((el) => {
            observer.observe(el);
        });
    });

    onUnmounted(() => {
        if (observer) {
            observer.disconnect();
        }
    });
}
