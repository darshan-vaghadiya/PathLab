<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '@/Composables/useFormatDate.js';

const props = defineProps({
    order: Object,
});

const statusColors = {
    pending: 'bg-gray-100 text-gray-800',
    sample_collected: 'bg-blue-100 text-blue-800',
    processing: 'bg-yellow-100 text-yellow-800',
    completed: 'bg-green-100 text-green-800',
    approved: 'bg-emerald-100 text-emerald-800',
    cancelled: 'bg-red-100 text-red-800',
    rejected: 'bg-orange-100 text-orange-800',
};

const paymentStatusColors = {
    unpaid: 'bg-red-100 text-red-800',
    partial: 'bg-yellow-100 text-yellow-800',
    paid: 'bg-green-100 text-green-800',
};

// Payment update modal
const showPaymentModal = ref(false);
const paymentForm = useForm({
    amount_paid: '',
    payment_mode: props.order.payment_mode || 'cash',
});

const openPaymentModal = () => {
    paymentForm.amount_paid = props.order.amount_paid;
    paymentForm.payment_mode = props.order.payment_mode || 'cash';
    showPaymentModal.value = true;
};

const submitPayment = () => {
    paymentForm.patch(route('orders.updatePayment', props.order.ulid), {
        onSuccess: () => { showPaymentModal.value = false; },
    });
};

const balance = parseFloat(props.order.net_amount) - parseFloat(props.order.amount_paid);

// Cancel modal state
const showCancelModal = ref(false);
const cancelForm = useForm({
    cancellation_reason: '',
});

const submitCancel = () => {
    cancelForm.post(route('orders.cancel', props.order.ulid), {
        onSuccess: () => {
            showCancelModal.value = false;
            cancelForm.reset();
        },
    });
};

// Check if a numeric result is abnormal
const isResultAbnormal = (result) => {
    if (!result || !result.result_value || !result.parameter) return false;
    if (result.parameter.field_type !== 'numeric') return false;
    if (!result.parameter.ranges || result.parameter.ranges.length === 0) return false;

    const value = parseFloat(result.result_value);
    if (isNaN(value)) return false;

    const range = result.parameter.ranges[0];
    if (range.min_value !== null && range.max_value !== null) {
        return value < parseFloat(range.min_value) || value > parseFloat(range.max_value);
    }
    return false;
};

const getRangeDisplay = (param) => {
    if (!param.ranges || param.ranges.length === 0) return '-';
    const range = param.ranges[0];
    if (range.min_value !== null && range.max_value !== null) {
        return `${range.min_value} - ${range.max_value}`;
    }
    if (range.text_value) return range.text_value;
    return '-';
};
</script>

<template>
    <Head :title="`Order: ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('orders.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Order #{{ order.order_number }}
                </h2>
                <div class="ml-auto flex items-center gap-2">
                    <button
                        v-if="order.status !== 'cancelled' && !order.report"
                        @click="showCancelModal = true"
                        class="inline-flex items-center gap-1.5 rounded-md bg-red-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-red-500 transition-colors"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Cancel Order
                    </button>
                    <a
                        :href="route('orders.receipt', order.ulid)"
                        target="_blank"
                        class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 transition-colors"
                        title="Print Receipt"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m10.5 0a48.536 48.536 0 0 0-10.5 0m10.5 0V3.375c0-.621-.504-1.125-1.125-1.125h-8.25c-.621 0-1.125.504-1.125 1.125v3.659M18.75 7.209A9.156 9.156 0 0 0 12 4.5a9.156 9.156 0 0 0-6.75 2.709" />
                        </svg>
                        Print Receipt
                    </a>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">
                <!-- Cancellation Banner -->
                <div v-if="order.status === 'cancelled'" class="overflow-hidden rounded-lg border border-red-300 bg-red-50">
                    <div class="p-4">
                        <div class="flex items-start gap-3">
                            <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <div>
                                <h3 class="text-sm font-semibold text-red-800">Order Cancelled</h3>
                                <p class="mt-1 text-sm text-red-700">
                                    <span class="font-medium">Reason:</span> {{ order.cancellation_reason }}
                                </p>
                                <p class="mt-1 text-xs text-red-600">
                                    Cancelled by {{ order.cancelled_by_user?.name || 'Unknown' }}
                                    <span v-if="order.cancelled_at"> on {{ formatDate(order.cancelled_at) }}</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order & Patient Info -->
                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Patient Info -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Patient Information</h3>
                            <dl class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Name</dt>
                                    <dd class="mt-1 text-sm text-gray-900">
                                        <Link
                                            v-if="order.patient"
                                            :href="route('patients.show', order.patient.ulid)"
                                            class="text-indigo-600 hover:text-indigo-900"
                                        >
                                            {{ order.patient.name }}
                                        </Link>
                                    </dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Patient ID</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.patient?.patient_id }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Age</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.patient?.age }} {{ order.patient?.age_unit }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Gender</dt>
                                    <dd class="mt-1 text-sm capitalize text-gray-900">{{ order.patient?.gender }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Phone</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.patient?.phone || '-' }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    <!-- Order Info -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="p-6">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Information</h3>
                            <dl class="grid grid-cols-2 gap-3">
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Order Number</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.order_number }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Date</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ formatDate(order.created_at) }}</dd>
                                </div>
                                <div>
                                    <dt class="text-sm font-medium text-gray-500">Source</dt>
                                    <dd class="mt-1 text-sm capitalize text-gray-900">{{ order.source?.replace('_', ' ') }}</dd>
                                </div>
                                <div v-if="order.referring_doctor">
                                    <dt class="text-sm font-medium text-gray-500">Referring Doctor</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.referring_doctor.name }}</dd>
                                </div>
                                <div v-if="order.b2b_client">
                                    <dt class="text-sm font-medium text-gray-500">B2B Client</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.b2b_client.name }}</dd>
                                </div>
                                <div v-if="order.home_visit_address">
                                    <dt class="text-sm font-medium text-gray-500">Visit Address</dt>
                                    <dd class="mt-1 text-sm text-gray-900">{{ order.home_visit_address }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <h3 class="mb-4 text-lg font-semibold text-gray-900">Test Results</h3>

                        <div v-if="!order.order_tests || order.order_tests.length === 0" class="text-sm text-gray-500">
                            No tests in this order.
                        </div>

                        <div v-else class="space-y-4">
                            <div
                                v-for="orderTest in order.order_tests"
                                :key="orderTest.id"
                                class="overflow-hidden rounded-lg border border-gray-200"
                            >
                                <!-- Test Header Row -->
                                <div class="flex items-center justify-between bg-gray-50 px-4 py-3">
                                    <div class="flex items-center gap-3">
                                        <span class="text-sm font-semibold text-gray-900">{{ orderTest.test?.name }}</span>
                                        <span
                                            class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium capitalize"
                                            :class="statusColors[orderTest.status] || 'bg-gray-100 text-gray-800'"
                                        >
                                            {{ orderTest.status?.replace('_', ' ') }}
                                        </span>
                                    </div>
                                    <span class="text-sm font-medium text-gray-700">{{ orderTest.price }}</span>
                                </div>

                                <!-- Parameter Results -->
                                <div v-if="orderTest.results && orderTest.results.length > 0" class="overflow-x-auto">
                                    <table class="min-w-full">
                                        <thead>
                                            <tr class="border-b border-gray-200 bg-gray-50/50">
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Parameter</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Result</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Unit</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Normal Range</th>
                                                <th class="px-4 py-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr
                                                v-for="result in orderTest.results"
                                                :key="result.id"
                                                class="border-b border-gray-100 last:border-b-0"
                                                :class="{ 'bg-red-50': isResultAbnormal(result) }"
                                            >
                                                <td class="px-4 py-2 text-sm font-medium text-gray-900">{{ result.parameter?.name }}</td>
                                                <td class="px-4 py-2 text-sm" :class="isResultAbnormal(result) ? 'font-bold text-red-700' : 'text-gray-900'">
                                                    {{ result.result_value ?? '-' }}
                                                </td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ result.parameter?.unit ?? '' }}</td>
                                                <td class="px-4 py-2 text-sm text-gray-500">{{ result.parameter ? getRangeDisplay(result.parameter) : '-' }}</td>
                                                <td class="px-4 py-2 text-sm">
                                                    <span
                                                        v-if="isResultAbnormal(result)"
                                                        class="inline-flex rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-800"
                                                    >
                                                        Abnormal
                                                    </span>
                                                    <span
                                                        v-else-if="result.parameter?.field_type === 'numeric' && result.result_value"
                                                        class="inline-flex rounded-full bg-green-100 px-2 py-0.5 text-xs font-semibold text-green-800"
                                                    >
                                                        Normal
                                                    </span>
                                                    <span v-else class="text-gray-400">-</span>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>

                                <!-- No results yet -->
                                <div v-else class="px-4 py-3 text-sm text-gray-500 italic">
                                    Results pending
                                </div>

                                <!-- Remarks -->
                                <div v-if="orderTest.result_remarks" class="border-t border-gray-100 bg-gray-50/50 px-4 py-2">
                                    <span class="text-xs font-medium uppercase text-gray-500">Remarks:</span>
                                    <span class="ml-2 text-sm text-gray-700">{{ orderTest.result_remarks }}</span>
                                </div>

                                <!-- Rejection Reason -->
                                <div v-if="orderTest.rejection_reason" class="border-t border-red-100 bg-red-50 px-4 py-2">
                                    <span class="text-xs font-semibold uppercase text-red-600">Rejection Reason:</span>
                                    <span class="ml-2 text-sm text-red-700">{{ orderTest.rejection_reason }}</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Info -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-gray-900">Payment Information</h3>
                            <button
                                v-if="order.status !== 'cancelled' && order.payment_status !== 'paid'"
                                @click="openPaymentModal"
                                class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-700"
                            >
                                <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18.75a60.07 60.07 0 0 1 15.797 2.101c.727.198 1.453-.342 1.453-1.096V18.75M3.75 4.5v.75A.75.75 0 0 1 3 6h-.75m0 0v-.375c0-.621.504-1.125 1.125-1.125H20.25M2.25 6v9m18-10.5v.75c0 .414.336.75.75.75h.75m-1.5-1.5h.375c.621 0 1.125.504 1.125 1.125v9.75c0 .621-.504 1.125-1.125 1.125h-.375m1.5-1.5H21a.75.75 0 0 0-.75.75v.75m0 0H3.75m0 0h-.375a1.125 1.125 0 0 1-1.125-1.125V15m1.5 1.5v-.75A.75.75 0 0 0 3 15h-.75M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm3 0h.008v.008H18V10.5Zm-12 0h.008v.008H6V10.5Z" /></svg>
                                Update Payment
                            </button>
                        </div>
                        <div class="grid grid-cols-2 gap-4 sm:grid-cols-5">
                            <div>
                                <dt class="text-xs font-medium text-gray-400">Subtotal</dt>
                                <dd class="mt-1 text-sm font-semibold text-gray-900">₹{{ order.total_amount }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400">Discount</dt>
                                <dd class="mt-1 text-sm font-semibold text-red-500">₹{{ order.discount || '0.00' }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400">Net Amount</dt>
                                <dd class="mt-1 text-base font-bold text-gray-900">₹{{ order.net_amount }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400">Amount Paid</dt>
                                <dd class="mt-1 text-sm font-semibold text-green-600">₹{{ order.amount_paid }}</dd>
                            </div>
                            <div>
                                <dt class="text-xs font-medium text-gray-400">Balance</dt>
                                <dd class="mt-1 text-sm font-semibold" :class="balance > 0 ? 'text-red-600' : 'text-green-600'">
                                    ₹{{ balance > 0 ? balance.toFixed(2) : '0.00' }}
                                </dd>
                            </div>
                        </div>
                        <div class="mt-4 flex flex-wrap items-center gap-4">
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-400">Status:</span>
                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold capitalize" :class="paymentStatusColors[order.payment_status] || 'bg-gray-100 text-gray-800'">{{ order.payment_status }}</span>
                            </div>
                            <div v-if="order.payment_mode" class="flex items-center gap-2">
                                <span class="text-xs font-medium text-gray-400">Mode:</span>
                                <span class="text-sm font-medium capitalize text-gray-700">{{ order.payment_mode?.replace('_', ' ') }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Payment Update Modal -->
                <div v-if="showPaymentModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
                    <div class="w-full max-w-md rounded-xl bg-white p-6 shadow-xl">
                        <h3 class="text-lg font-semibold text-gray-900">Update Payment</h3>
                        <p class="mt-1 text-sm text-gray-500">Net amount: ₹{{ order.net_amount }}</p>

                        <div class="mt-5 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Amount Paid (₹)</label>
                                <input v-model="paymentForm.amount_paid" type="number" step="0.01" min="0" :max="order.net_amount" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" />
                                <p v-if="paymentForm.errors.amount_paid" class="mt-1 text-sm text-red-600">{{ paymentForm.errors.amount_paid }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700">Payment Mode</label>
                                <select v-model="paymentForm.payment_mode" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                    <option value="cash">Cash</option>
                                    <option value="upi">UPI</option>
                                    <option value="card">Card</option>
                                    <option value="credit">Credit</option>
                                </select>
                            </div>
                        </div>

                        <div class="mt-6 flex justify-end gap-3">
                            <button @click="showPaymentModal = false" class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50">Cancel</button>
                            <button @click="submitPayment" :disabled="paymentForm.processing" class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                {{ paymentForm.processing ? 'Saving...' : 'Update Payment' }}
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Report Link -->
                <div v-if="order.report" class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between p-6">
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900">Report</h3>
                            <p class="mt-1 text-sm text-gray-500">Report has been generated and is available for download.</p>
                        </div>
                        <a
                            :href="order.report.url || route('reports.show', order.report.ulid)"
                            target="_blank"
                            class="inline-flex items-center rounded-md bg-emerald-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-emerald-500"
                        >
                            <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            View Report
                        </a>
                    </div>
                </div>
            </div>

        <!-- Cancel Order Modal -->
        <Teleport to="body">
            <div v-if="showCancelModal" class="fixed inset-0 z-50 flex items-center justify-center overflow-y-auto">
                <div class="fixed inset-0 bg-gray-500/75 transition-opacity" @click="showCancelModal = false"></div>
                <div class="relative z-10 w-full max-w-md transform rounded-lg bg-white p-6 shadow-xl transition-all">
                    <h3 class="text-lg font-semibold text-gray-900">Cancel Order</h3>
                    <p class="mt-2 text-sm text-gray-500">
                        Are you sure you want to cancel this order? This action cannot be undone. All non-approved tests will be cancelled.
                    </p>
                    <div class="mt-4">
                        <label for="cancellation_reason" class="block text-sm font-medium text-gray-700">Reason for cancellation <span class="text-red-500">*</span></label>
                        <textarea
                            id="cancellation_reason"
                            v-model="cancelForm.cancellation_reason"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                            placeholder="Please provide a reason for cancelling this order..."
                        ></textarea>
                        <p v-if="cancelForm.errors.cancellation_reason" class="mt-1 text-sm text-red-600">{{ cancelForm.errors.cancellation_reason }}</p>
                    </div>
                    <div class="mt-5 flex justify-end gap-3">
                        <button
                            @click="showCancelModal = false"
                            class="rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                        >
                            Go Back
                        </button>
                        <button
                            @click="submitCancel"
                            :disabled="cancelForm.processing"
                            class="rounded-md bg-red-600 px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-red-500 disabled:opacity-50"
                        >
                            {{ cancelForm.processing ? 'Cancelling...' : 'Yes, Cancel Order' }}
                        </button>
                    </div>
                </div>
            </div>
        </Teleport>
    </AuthenticatedLayout>
</template>
