<script setup>
defineProps({
    type: {
        type: String,
        default: 'button',
    },
});
</script>

<template>
    <button
        :type="type"
        class="inline-flex items-center rounded-xl border border-white/10 bg-transparent px-4 py-2.5 text-sm font-semibold text-slate-200 transition duration-150 ease-in-out hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-hazak-blue/50 focus:ring-offset-2 focus:ring-offset-hazak-graphite disabled:opacity-25"
    >
        <slot />
    </button>
</template>
