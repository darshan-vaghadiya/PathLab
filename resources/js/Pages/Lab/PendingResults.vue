<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';
import { formatDate } from '@/Composables/useFormatDate.js';
import Pagination from '@/Components/Pagination.vue';

const props = defineProps({
    orders: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
    filters: {
        type: Object,
        default: () => ({}),
    },
});

const search = ref(props.filters.search ?? '');

let searchTimeout = null;
watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(
            route('lab.pendingResults'),
            { search: value || undefined },
            { preserveState: true, replace: true }
        );
    }, 300);
});

const formatAge = (patient) => {
    if (!patient?.age) return '';
    const unitLabel = patient.age_unit === 'years' ? 'Y' : patient.age_unit === 'months' ? 'M' : 'D';
    const genderLabel = patient.gender === 'male' ? 'M' : patient.gender === 'female' ? 'F' : 'O';
    return `${patient.age}${unitLabel} / ${genderLabel}`;
};
</script>

<template>
    <Head title="Pending Results" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Pending Results
            </h2>
        </template>

        <div class="mx-auto max-w-7xl">
                <!-- Search Bar -->
                <div class="mb-5">
                    <div class="relative">
                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                            </svg>
                        </div>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Search by patient name, phone, or order number..."
                            class="block w-full rounded-lg border-gray-300 pl-10 pr-4 py-3 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        />
                    </div>
                </div>

                <!-- Empty State -->
                <div v-if="orders.data.length === 0" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    <p class="mt-3 text-sm text-gray-500">
                        {{ search ? 'No matching orders found.' : 'No tests pending result entry.' }}
                    </p>
                </div>

                <!-- Orders Table -->
                <div v-else class="overflow-hidden rounded-lg bg-white shadow-sm">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Order #</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Patient Name</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Age / Gender</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tests</th>
                                <th class="px-5 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Date</th>
                                <th class="px-5 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Action</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 bg-white">
                            <tr v-for="order in orders.data" :key="order.id" class="transition hover:bg-gray-50">
                                <td class="whitespace-nowrap px-5 py-3.5">
                                    <span class="font-mono text-sm font-bold text-indigo-700">{{ order.order_number }}</span>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm font-medium text-gray-900">
                                    {{ order.patient?.name }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                                    {{ order.patient?.phone ?? '-' }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-500">
                                    {{ formatAge(order.patient) || '-' }}
                                </td>
                                <td class="px-5 py-3.5">
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="ot in order.order_tests"
                                            :key="ot.id"
                                            class="inline-flex rounded bg-blue-50 px-1.5 py-0.5 text-xs font-medium text-blue-700 ring-1 ring-inset ring-blue-600/20"
                                        >
                                            {{ ot.test?.short_name || ot.test?.name }}
                                        </span>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-sm text-gray-400">
                                    {{ formatDate(order.created_at) }}
                                </td>
                                <td class="whitespace-nowrap px-5 py-3.5 text-center">
                                    <Link
                                        :href="route('lab.enterResults', order.ulid)"
                                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                        title="Enter Results"
                                    >
                                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                        Enter Results
                                    </Link>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                    <Pagination :links="orders.links" />
                </div>
            </div>
    </AuthenticatedLayout>
</template>
