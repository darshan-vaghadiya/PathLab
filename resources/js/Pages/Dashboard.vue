<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import { formatDateShort } from '@/Composables/useFormatDate.js';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({
            todayOrders: 0, pendingSamples: 0, pendingResults: 0, pendingApprovals: 0,
            todayRevenue: 0, monthlyRevenue: 0, todaysReports: 0,
            todayAmountPaid: 0, monthlyCollection: 0, totalOutstanding: 0, overduePayments: 0,
        }),
    },
    recentOrders: { type: Array, default: () => [] },
});

const page = usePage();
const permissions = computed(() => page.props.auth?.permissions ?? []);
const can = (perm) => permissions.value.includes(perm);

const fmt = (n) => '₹' + Number(n || 0).toLocaleString('en-IN');
const fmtDate = (d) => formatDateShort(d);
const statusLabel = (s) => s ? s.replace(/_/g, ' ').replace(/\b\w/g, c => c.toUpperCase()) : '-';

const payClasses = { pending: 'bg-yellow-50 text-yellow-700', partial: 'bg-orange-50 text-orange-700', paid: 'bg-green-50 text-green-700' };
const testClasses = { pending: 'bg-gray-50 text-gray-600', in_progress: 'bg-blue-50 text-blue-700', completed: 'bg-emerald-50 text-emerald-700', no_tests: 'bg-red-50 text-red-600' };
</script>

<template>
    <Head title="Dashboard" />

    <AuthenticatedLayout>
        <template #header>
            <h1 class="text-xl font-semibold text-gray-900">Dashboard</h1>
        </template>

        <!-- 1. Quick Actions -->
        <div class="mb-5 flex flex-wrap gap-2">
            <Link v-if="can('create_orders')" :href="route('orders.create')" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-indigo-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v6m3-3H9m12 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                New Order
            </Link>
            <Link v-if="can('manage_patients')" :href="route('patients.create')" class="inline-flex items-center gap-2 rounded-lg bg-blue-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-blue-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M18 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0ZM3 19.235v-.11a6.375 6.375 0 0 1 12.75 0v.109A12.318 12.318 0 0 1 9.374 21c-2.331 0-4.512-.645-6.374-1.766Z" /></svg>
                New Patient
            </Link>
            <Link v-if="can('collect_samples')" :href="route('lab.pendingSamples')" class="inline-flex items-center gap-2 rounded-lg bg-amber-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-amber-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104" /></svg>
                Collect Samples
            </Link>
            <Link v-if="can('enter_results')" :href="route('lab.pendingResults')" class="inline-flex items-center gap-2 rounded-lg bg-orange-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-orange-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                Enter Results
            </Link>
            <Link v-if="can('approve_reports')" :href="route('doctor.pendingApprovals')" class="inline-flex items-center gap-2 rounded-lg bg-rose-600 px-4 py-2.5 text-sm font-medium text-white shadow-sm transition hover:bg-rose-700">
                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                Approvals
            </Link>
        </div>

        <!-- 2. Overdue Alert -->
        <div v-if="stats.overduePayments > 0" class="mb-5 flex items-center gap-3 rounded-lg border border-amber-200 bg-amber-50 px-4 py-3">
            <svg class="h-5 w-5 shrink-0 text-amber-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            <p class="text-sm font-medium text-amber-800"><strong>{{ stats.overduePayments }}</strong> {{ stats.overduePayments === 1 ? 'order' : 'orders' }} with payments overdue by 7+ days.</p>
        </div>

        <!-- 3. Stat Cards -->
        <div class="mb-6 grid grid-cols-2 gap-3 md:grid-cols-4">
            <!-- Today's Orders -->
            <div class="flex items-center gap-3 rounded-xl border border-blue-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-blue-50">
                    <svg class="h-5 w-5 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Today's Orders</p><p class="text-2xl font-bold text-gray-900">{{ stats.todayOrders }}</p></div>
            </div>

            <!-- Pending Samples -->
            <div class="flex items-center gap-3 rounded-xl border border-amber-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-amber-50">
                    <svg class="h-5 w-5 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Pending Samples</p><p class="text-2xl font-bold text-amber-700">{{ stats.pendingSamples }}</p></div>
            </div>

            <!-- Pending Results -->
            <div class="flex items-center gap-3 rounded-xl border border-orange-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-orange-50">
                    <svg class="h-5 w-5 text-orange-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Pending Results</p><p class="text-2xl font-bold text-orange-700">{{ stats.pendingResults }}</p></div>
            </div>

            <!-- Pending Approvals -->
            <div class="flex items-center gap-3 rounded-xl border border-rose-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-rose-50">
                    <svg class="h-5 w-5 text-rose-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Pending Approvals</p><p class="text-2xl font-bold text-rose-700">{{ stats.pendingApprovals }}</p></div>
            </div>

            <!-- Today's Collection -->
            <div class="flex items-center gap-3 rounded-xl border border-emerald-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-emerald-50">
                    <svg class="h-5 w-5 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Today's Collection</p><p class="text-2xl font-bold text-emerald-700">{{ fmt(stats.todayAmountPaid) }}</p></div>
            </div>

            <!-- Monthly Collection -->
            <div class="flex items-center gap-3 rounded-xl border border-green-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-green-50">
                    <svg class="h-5 w-5 text-green-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Monthly Collection</p><p class="text-2xl font-bold text-green-700">{{ fmt(stats.monthlyCollection) }}</p></div>
            </div>

            <!-- Outstanding -->
            <div class="flex items-center gap-3 rounded-xl border border-red-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-red-50">
                    <svg class="h-5 w-5 text-red-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Outstanding</p><p class="text-2xl font-bold text-red-700">{{ fmt(stats.totalOutstanding) }}</p></div>
            </div>

            <!-- Today's Reports -->
            <div class="flex items-center gap-3 rounded-xl border border-purple-100 bg-white p-4 shadow-sm">
                <div class="flex h-11 w-11 shrink-0 items-center justify-center rounded-lg bg-purple-50">
                    <svg class="h-5 w-5 text-purple-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                </div>
                <div><p class="text-xs font-medium text-gray-500">Today's Reports</p><p class="text-2xl font-bold text-purple-700">{{ stats.todaysReports }}</p></div>
            </div>
        </div>

        <!-- 4. Recent Orders -->
        <div class="rounded-xl bg-white shadow-sm ring-1 ring-gray-900/5">
            <div class="flex items-center justify-between border-b px-5 py-3">
                <h2 class="text-sm font-semibold text-gray-900">Recent Orders</h2>
                <Link v-if="recentOrders.length" :href="route('orders.index')" class="text-xs font-medium text-indigo-600 hover:text-indigo-800">View all</Link>
            </div>

            <div class="overflow-x-auto">
                <table v-if="recentOrders.length" class="min-w-full divide-y divide-gray-100">
                    <thead>
                        <tr class="bg-gray-50/50">
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500">Order</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500">Patient</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500 max-sm:hidden">Source</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500">Payment</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500">Status</th>
                            <th class="px-5 py-2.5 text-right text-xs font-medium uppercase text-gray-500">Amount</th>
                            <th class="px-5 py-2.5 text-left text-xs font-medium uppercase text-gray-500 max-sm:hidden">Date</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-50">
                        <tr v-for="o in recentOrders" :key="o.id" class="cursor-pointer transition hover:bg-gray-50" @click="$inertia.visit(route('orders.show', o.ulid))">
                            <td class="whitespace-nowrap px-5 py-3 text-sm font-medium text-indigo-600">{{ o.order_number }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-900">{{ o.patient?.name ?? '-' }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm capitalize text-gray-500 max-sm:hidden">{{ (o.source ?? 'walk_in').replace('_',' ') }}</td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', payClasses[o.payment_status] || 'bg-gray-50 text-gray-600']">{{ statusLabel(o.payment_status) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3">
                                <span :class="['inline-flex rounded-full px-2 py-0.5 text-xs font-medium', testClasses[o.test_status] || 'bg-gray-50 text-gray-600']">{{ statusLabel(o.test_status) }}</span>
                            </td>
                            <td class="whitespace-nowrap px-5 py-3 text-right text-sm font-semibold text-gray-900">{{ fmt(o.net_amount ?? 0) }}</td>
                            <td class="whitespace-nowrap px-5 py-3 text-sm text-gray-400 max-sm:hidden">{{ fmtDate(o.created_at) }}</td>
                        </tr>
                    </tbody>
                </table>

                <div v-else class="px-6 py-14 text-center">
                    <svg class="mx-auto h-10 w-10 text-gray-300" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" /></svg>
                    <p class="mt-3 text-sm text-gray-500">No orders yet.</p>
                    <Link v-if="can('create_orders')" :href="route('orders.create')" class="mt-3 inline-flex items-center gap-1.5 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700">New Order</Link>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
