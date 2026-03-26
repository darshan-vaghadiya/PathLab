<script setup>
import { Link } from '@inertiajs/vue3';

defineProps({
    links: { type: Array, required: true },
});
</script>

<template>
    <div v-if="links && links.length > 3" class="border-t border-gray-200 px-4 py-3">
        <nav class="flex items-center justify-center gap-1">
            <template v-for="(link, i) in links" :key="i">
                <Link
                    v-if="link.url"
                    :href="link.url"
                    v-html="link.label"
                    class="relative inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium transition"
                    :class="link.active
                        ? 'bg-indigo-600 text-white shadow-sm'
                        : 'text-gray-600 hover:bg-gray-100 ring-1 ring-inset ring-gray-200'"
                    preserve-state
                />
                <span
                    v-else
                    v-html="link.label"
                    class="relative inline-flex items-center rounded-md px-3 py-1.5 text-sm font-medium text-gray-300"
                />
            </template>
        </nav>
    </div>
</template>
