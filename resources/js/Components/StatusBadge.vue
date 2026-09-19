<script setup>
import { computed } from 'vue';

const props = defineProps({
    status: {
        type: String,
        required: true,
    },
});

const variants = {
    critical: 'bg-hazak-critical/10 text-hazak-critical border-hazak-critical/30',
    warning: 'bg-amber-500/10 text-amber-400 border-amber-500/30',
    active: 'bg-hazak-blue/10 text-hazak-blue-hover border-hazak-blue/30',
    resolved: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
    idle: 'bg-white/5 text-slate-400 border-white/10',
};

const labels = {
    critical: 'Crítico',
    warning: 'Atenção',
    active: 'Em andamento',
    resolved: 'Resolvido',
    idle: 'Inativo',
};

const variantClasses = computed(() => variants[props.status] ?? variants.idle);
const label = computed(() => labels[props.status] ?? props.status);
</script>

<template>
    <span
        class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-xs font-medium"
        :class="variantClasses"
    >
        <span
            class="h-1.5 w-1.5 rounded-full"
            :class="status === 'critical' ? 'animate-pulse bg-hazak-critical' : 'bg-current'"
        ></span>
        {{ label }}
    </span>
</template>
