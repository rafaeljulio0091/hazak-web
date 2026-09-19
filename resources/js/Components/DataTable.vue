<script setup>
import EmptyState from '@/Components/EmptyState.vue';

defineProps({
    columns: {
        type: Array,
        required: true, // [{ key, label, align }]
    },
    rows: {
        type: Array,
        default: () => [],
    },
    loading: {
        type: Boolean,
        default: false,
    },
    emptyTitle: {
        type: String,
        default: 'Nenhum registro encontrado',
    },
    emptyDescription: {
        type: String,
        default: '',
    },
});
</script>

<template>
    <div class="overflow-x-auto">
        <table class="w-full min-w-[640px] table-auto border-collapse text-left text-sm">
            <thead>
                <tr class="border-b border-white/10">
                    <th
                        v-for="column in columns"
                        :key="column.key"
                        scope="col"
                        class="whitespace-nowrap px-4 py-3 text-xs font-semibold uppercase tracking-wide text-slate-500"
                        :class="column.align === 'right' ? 'text-right' : 'text-left'"
                    >
                        {{ column.label }}
                    </th>
                </tr>
            </thead>

            <tbody v-if="loading" class="divide-y divide-white/5" aria-hidden="true">
                <tr v-for="n in 4" :key="n" class="animate-pulse">
                    <td v-for="column in columns" :key="column.key" class="px-4 py-3.5">
                        <div class="h-3.5 w-full max-w-[10rem] rounded bg-white/10"></div>
                    </td>
                </tr>
            </tbody>

            <tbody v-else-if="rows.length" class="divide-y divide-white/5">
                <tr
                    v-for="(row, index) in rows"
                    :key="row.id ?? index"
                    class="transition duration-150 ease-in-out hover:bg-white/[0.03]"
                >
                    <td
                        v-for="column in columns"
                        :key="column.key"
                        class="whitespace-nowrap px-4 py-3.5 text-slate-300"
                        :class="column.align === 'right' ? 'text-right' : 'text-left'"
                    >
                        <slot :name="`cell-${column.key}`" :row="row">
                            {{ row[column.key] }}
                        </slot>
                    </td>
                </tr>
            </tbody>
        </table>

        <div v-if="!loading && !rows.length">
            <EmptyState :title="emptyTitle" :description="emptyDescription" />
        </div>
    </div>
</template>
