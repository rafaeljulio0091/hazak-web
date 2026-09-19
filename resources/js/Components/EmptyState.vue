<script setup>
defineProps({
    title: {
        type: String,
        required: true,
    },
    description: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="flex flex-col items-center justify-center gap-3 px-6 py-12 text-center">
        <div
            class="flex h-12 w-12 items-center justify-center rounded-full bg-white/5 text-slate-500"
            aria-hidden="true"
        >
            <slot name="icon">
                <svg
                    class="h-6 w-6"
                    fill="none"
                    viewBox="0 0 24 24"
                    stroke="currentColor"
                    stroke-width="1.5"
                >
                    <path
                        stroke-linecap="round"
                        stroke-linejoin="round"
                        d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"
                    />
                </svg>
            </slot>
        </div>

        <div>
            <p class="text-sm font-semibold text-slate-200">{{ title }}</p>
            <p v-if="description" class="mt-1 text-sm text-slate-500">{{ description }}</p>
        </div>

        <slot name="action" />
    </div>
</template>
