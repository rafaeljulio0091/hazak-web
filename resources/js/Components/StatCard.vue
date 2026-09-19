<script setup>
defineProps({
    label: {
        type: String,
        required: true,
    },
    value: {
        type: [String, Number],
        default: null,
    },
    hint: {
        type: String,
        default: '',
    },
    trend: {
        type: String,
        default: null, // 'up' | 'down' | null
    },
    trendLabel: {
        type: String,
        default: '',
    },
    tone: {
        type: String,
        default: 'default', // 'default' | 'critical'
    },
    loading: {
        type: Boolean,
        default: false,
    },
});
</script>

<template>
    <div
        class="rounded-2xl border bg-hazak-graphite p-5 transition duration-150 ease-in-out"
        :class="tone === 'critical' ? 'border-hazak-critical/30' : 'border-white/10'"
    >
        <div v-if="loading" class="animate-pulse space-y-3" aria-hidden="true">
            <div class="h-3.5 w-24 rounded bg-white/10"></div>
            <div class="h-7 w-16 rounded bg-white/10"></div>
            <div class="h-3 w-20 rounded bg-white/10"></div>
        </div>

        <template v-else>
            <div class="flex items-center justify-between">
                <p class="text-sm font-medium text-slate-400">{{ label }}</p>
                <span
                    v-if="tone === 'critical'"
                    class="h-2 w-2 rounded-full bg-hazak-critical"
                    aria-hidden="true"
                ></span>
            </div>

            <p
                class="mt-2 text-3xl font-bold tracking-tight"
                :class="tone === 'critical' ? 'text-hazak-critical' : 'text-white'"
            >
                {{ value }}
            </p>

            <div v-if="hint || trend" class="mt-2 flex items-center gap-1.5 text-xs">
                <span
                    v-if="trend"
                    class="inline-flex items-center gap-0.5 font-medium"
                    :class="trend === 'up' ? 'text-hazak-critical' : 'text-emerald-400'"
                >
                    <svg
                        class="h-3.5 w-3.5"
                        :class="{ 'rotate-180': trend === 'down' }"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                        aria-hidden="true"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 10.5L12 3m0 0l7.5 7.5M12 3v18" />
                    </svg>
                    {{ trendLabel }}
                </span>
                <span v-else class="text-slate-500">{{ hint }}</span>
            </div>
        </template>
    </div>
</template>
