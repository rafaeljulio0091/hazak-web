import { onBeforeUnmount, onMounted, ref } from 'vue';

/**
 * Fades a section into view the first time it scrolls into the viewport.
 * Skips the observer entirely (content just appears) when the user has
 * asked the OS for reduced motion.
 */
export function useRevealOnScroll() {
    const target = ref(null);
    const visible = ref(false);

    let observer = null;

    onMounted(() => {
        if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            visible.value = true;
            return;
        }

        observer = new IntersectionObserver(
            ([entry]) => {
                if (entry.isIntersecting) {
                    visible.value = true;
                    observer?.disconnect();
                }
            },
            { threshold: 0.15 },
        );

        if (target.value) {
            observer.observe(target.value);
        }
    });

    onBeforeUnmount(() => observer?.disconnect());

    return { target, visible };
}
