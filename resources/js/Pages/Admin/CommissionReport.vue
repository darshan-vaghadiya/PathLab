<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    doctors: {
        type: Array,
        default: () => [],
    },
    filters: {
        type: Object,
        default: () => ({ from: '', to: '' }),
    },
});

const from = ref(props.filters.from ?? '');
const to = ref(props.filters.to ?? '');

const applyFilter = () => {
    router.get(route('admin.referring-doctors.commissionReport'), {
        from: from.value,
        to: to.value,
    }, {
        preserveState: true,
        preserveScroll: true,
    });
};

const totalOrders = () => {
    return props.doctors.reduce((sum, d) => sum + (d.orders_count ?? 0), 0);
};

const totalAmount = () => {
    return props.doctors.reduce((sum, d) => sum + Number(d.total_order_amount ?? 0), 0);
};

const totalCommission = () => {
    return props.doctors.reduce((sum, d) => sum + Number(d.commission_amount ?? 0), 0);
};
</script>

<template>
    <Head title="Commission Report" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Commission Report
            </h2>
        </template>

        <div class="mx-auto max-w-7xl">
                <!-- Date Range Filter -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-4">
                        <div class="flex flex-wrap items-end gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">From Date</label>
                                <input
                                    v-model="from"
                                    type="date"
                                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">To Date</label>
                                <input
                                    v-model="to"
                                    type="date"
                                    class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                />
                            </div>
                            <PrimaryButton @click="applyFilter">
                                Apply Filter
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Summary Cards -->
                <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Total Orders</p>
                        <p class="text-2xl font-bold text-gray-900">{{ totalOrders() }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Total Order Amount</p>
                        <p class="text-2xl font-bold text-gray-900">{{ totalAmount().toFixed(2) }}</p>
                    </div>
                    <div class="rounded-lg bg-white p-4 shadow-sm">
                        <p class="text-sm text-gray-500">Total Commission</p>
                        <p class="text-2xl font-bold text-indigo-600">{{ totalCommission().toFixed(2) }}</p>
                    </div>
                </div>

                <!-- Commission Table -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Doctor Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Orders Count</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Total Order Amount</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Commission Amount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="doctor in doctors" :key="doctor.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ doctor.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ doctor.orders_count }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ Number(doctor.total_order_amount).toFixed(2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-indigo-600">{{ Number(doctor.commission_amount).toFixed(2) }}</td>
                                </tr>
                                <tr v-if="!doctors.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No commission data found for the selected period.
                                    </td>
                                </tr>
                            </tbody>
                            <!-- Totals row -->
                            <tfoot v-if="doctors.length" class="bg-gray-50">
                                <tr>
                                    <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-gray-900">Total</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-gray-900">{{ totalOrders() }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-gray-900">{{ totalAmount().toFixed(2) }}</td>
                                    <td class="whitespace-nowrap px-6 py-3 text-sm font-bold text-indigo-600">{{ totalCommission().toFixed(2) }}</td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
