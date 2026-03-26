<script>
export default { layout: false };
</script>

<script setup>
import { Head } from '@inertiajs/vue3';

defineProps({
    query: { type: String, default: '' },
});
</script>

<template>
    <Head title="Report Not Found" />
    <div class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
        <div class="max-w-md text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-50">
                <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Report Not Found</h1>
            <p class="mt-3 text-gray-500">We couldn't find a report matching <strong class="text-gray-700">"{{ query }}"</strong>. Please check the report number and try again.</p>
            <div class="mt-8 flex justify-center gap-3">
                <a href="/#report" class="rounded-lg bg-indigo-600 px-6 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">Try Again</a>
                <a href="/" class="rounded-lg border border-gray-200 bg-white px-6 py-2.5 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">Go Home</a>
            </div>
        </div>
    </div>
</template>
