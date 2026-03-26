<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const approveForm = useForm({});
const rejectForm = useForm({ rejection_reason: '' });
const rejectAllForm = useForm({ rejection_reason: '' });

const showRejectModal = ref(false);
const rejectingOrderTest = ref(null);
const showRejectAllModal = ref(false);

const openRejectModal = (orderTest) => {
    rejectingOrderTest.value = orderTest;
    rejectForm.rejection_reason = '';
    showRejectModal.value = true;
};

const performReject = () => {
    rejectForm.post(route('doctor.rejectTest', rejectingOrderTest.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectModal.value = false;
            rejectingOrderTest.value = null;
            rejectForm.reset();
        },
    });
};

const openRejectAllModal = () => {
    rejectAllForm.rejection_reason = '';
    showRejectAllModal.value = true;
};

const performRejectAll = () => {
    rejectAllForm.post(route('doctor.rejectAll', props.order.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showRejectAllModal.value = false;
            rejectAllForm.reset();
        },
    });
};

// Check if a numeric result is abnormal based on parameter ranges
const isResultAbnormal = (result) => {
    if (!result || !result.result_value || !result.parameter) return false;
    const param = result.parameter;
    if (param.field_type !== 'numeric') return false;
    if (!param.ranges || param.ranges.length === 0) return false;

    const value = parseFloat(result.result_value);
    if (isNaN(value)) return false;

    const range = param.ranges[0]; // Use first range group
    if (range.min_value !== null && range.max_value !== null) {
        return value < parseFloat(range.min_value) || value > parseFloat(range.max_value);
    }
    return false;
};

// Check if any result in an order_test is abnormal
const hasAbnormalResult = (orderTest) => {
    if (!orderTest.results || orderTest.results.length === 0) return false;
    return orderTest.results.some((r) => isResultAbnormal(r));
};

// Get display range for a parameter
const getRangeDisplay = (param) => {
    if (!param.ranges || param.ranges.length === 0) return '-';
    const range = param.ranges[0];
    if (range.min_value !== null && range.max_value !== null) {
        return `${range.min_value} - ${range.max_value}`;
    }
    if (range.text_value) {
        return range.text_value;
    }
    return '-';
};

const approveTest = (orderTestUlid) => {
    approveForm.post(route('doctor.approveTest', orderTestUlid), {
        preserveScroll: true,
    });
};

const showApproveAllConfirm = ref(false);

const confirmApproveAll = () => {
    showApproveAllConfirm.value = true;
};

const performApproveAll = () => {
    showApproveAllConfirm.value = false;
    approveForm.post(route('doctor.approveAll', props.order.ulid), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head :title="`Review Order #${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Review Order #{{ order.order_number }}
            </h2>
        </template>

        <div class="mx-auto max-w-7xl">
                <!-- Patient Info -->
                <div class="mb-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Patient Information</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 p-6 sm:grid-cols-3">
                        <div>
                            <span class="text-sm font-medium text-gray-500">Name</span>
                            <p class="text-gray-900">{{ order.patient?.name }}</p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Age / Gender</span>
                            <p class="text-gray-900">
                                {{ order.patient?.age ?? 'N/A' }} / {{ order.patient?.gender ?? 'N/A' }}
                            </p>
                        </div>
                        <div>
                            <span class="text-sm font-medium text-gray-500">Phone</span>
                            <p class="text-gray-900">{{ order.patient?.phone ?? 'N/A' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Test Results -->
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="flex items-center justify-between border-b bg-gray-50 px-6 py-4">
                        <h3 class="text-lg font-medium text-gray-900">Test Results</h3>
                        <div class="flex items-center gap-2">
                            <button
                                @click="openRejectAllModal"
                                :disabled="rejectAllForm.processing"
                                class="rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                                title="Reject all completed tests in this order"
                            >
                                Reject All
                            </button>
                            <button
                                @click="confirmApproveAll"
                                :disabled="approveForm.processing"
                                class="rounded-md bg-green-600 px-4 py-2 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
                                title="Approve all tests in this order"
                            >
                                Approve All
                            </button>
                        </div>
                    </div>

                    <!-- Each Test -->
                    <div
                        v-for="ot in order.order_tests"
                        :key="ot.id"
                        class="border-b last:border-b-0"
                    >
                        <!-- Test Header -->
                        <div class="flex items-center justify-between px-6 py-3" :class="hasAbnormalResult(ot) ? 'bg-red-50' : 'bg-gray-50'">
                            <div class="flex items-center gap-3">
                                <h4 class="text-sm font-semibold text-gray-900">{{ ot.test?.name }}</h4>
                                <span
                                    v-if="ot.status === 'approved'"
                                    class="inline-flex rounded-full bg-green-100 px-2 text-xs font-semibold leading-5 text-green-800"
                                >
                                    Approved
                                </span>
                                <span
                                    v-else-if="ot.status === 'rejected'"
                                    class="inline-flex rounded-full bg-red-100 px-2 text-xs font-semibold leading-5 text-red-800"
                                >
                                    Rejected
                                </span>
                                <span
                                    v-else-if="ot.status === 'completed'"
                                    class="inline-flex rounded-full bg-yellow-100 px-2 text-xs font-semibold leading-5 text-yellow-800"
                                >
                                    Pending Approval
                                </span>
                                <span
                                    v-else
                                    class="inline-flex rounded-full bg-gray-100 px-2 text-xs font-semibold leading-5 text-gray-800"
                                >
                                    {{ ot.status }}
                                </span>
                            </div>
                            <div class="flex items-center gap-2">
                                <template v-if="ot.status === 'completed'">
                                    <button
                                        @click="openRejectModal(ot)"
                                        :disabled="rejectForm.processing"
                                        class="rounded-md bg-red-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                                        title="Reject this test"
                                    >
                                        Reject
                                    </button>
                                    <button
                                        @click="approveTest(ot.ulid)"
                                        :disabled="approveForm.processing"
                                        class="rounded-md bg-green-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 disabled:opacity-50"
                                        title="Approve this test"
                                    >
                                        Approve
                                    </button>
                                </template>
                                <span v-else-if="ot.status === 'approved'" class="text-sm text-green-600">Approved</span>
                                <span v-else-if="ot.status === 'rejected'" class="text-sm text-red-600">Sent back to lab</span>
                            </div>
                        </div>

                        <!-- Parameters Table -->
                        <div class="px-6 py-4">
                            <div v-if="!ot.results || ot.results.length === 0" class="text-sm text-gray-500 italic">
                                No results entered yet.
                            </div>

                            <table v-else class="w-full">
                                <thead>
                                    <tr class="border-b border-gray-200">
                                        <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Parameter</th>
                                        <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Result</th>
                                        <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Unit</th>
                                        <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Normal Range</th>
                                        <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr
                                        v-for="result in ot.results"
                                        :key="result.id"
                                        class="border-b border-gray-100 last:border-b-0"
                                        :class="{ 'bg-red-50': isResultAbnormal(result) }"
                                    >
                                        <td class="py-2.5 pr-4 text-sm font-medium text-gray-900">
                                            {{ result.parameter?.name }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-sm" :class="isResultAbnormal(result) ? 'font-bold text-red-700' : 'text-gray-900'">
                                            {{ result.result_value ?? '-' }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-sm text-gray-500">
                                            {{ result.parameter?.unit ?? '' }}
                                        </td>
                                        <td class="py-2.5 pr-4 text-sm text-gray-500">
                                            {{ result.parameter ? getRangeDisplay(result.parameter) : '-' }}
                                        </td>
                                        <td class="py-2.5 text-sm">
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
                                            <span v-else class="text-sm text-gray-400">-</span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>

                            <!-- Remarks -->
                            <div v-if="ot.result_remarks" class="mt-3 rounded-md bg-gray-50 p-3">
                                <span class="text-xs font-medium uppercase tracking-wider text-gray-500">Remarks:</span>
                                <p class="mt-1 text-sm text-gray-700">{{ ot.result_remarks }}</p>
                            </div>

                            <!-- Rejection Reason -->
                            <div v-if="ot.status === 'rejected' && ot.rejection_reason" class="mt-3 rounded-md border border-red-200 bg-red-50 p-3">
                                <span class="text-xs font-semibold uppercase tracking-wider text-red-600">Rejection Reason:</span>
                                <p class="mt-1 text-sm text-red-700">{{ ot.rejection_reason }}</p>
                                <p v-if="ot.rejector" class="mt-1 text-xs text-red-500">Rejected by {{ ot.rejector.name }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!-- Approve All Confirmation -->
        <Modal :show="showApproveAllConfirm" maxWidth="sm" @close="showApproveAllConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Approve All Tests</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to approve all tests in this order?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showApproveAllConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performApproveAll" :disabled="approveForm.processing">
                        Approve All
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Reject Single Test Modal -->
        <Modal :show="showRejectModal" maxWidth="md" @close="showRejectModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-red-600">Reject Test</h3>
                <p class="mt-1 text-sm text-gray-600">
                    Rejecting <span class="font-medium">{{ rejectingOrderTest?.test?.name }}</span>. The lab technician will need to re-enter the results.
                </p>
                <div class="mt-4">
                    <label for="rejection_reason" class="block text-sm font-medium text-gray-700">Reason for rejection <span class="text-red-500">*</span></label>
                    <textarea
                        id="rejection_reason"
                        v-model="rejectForm.rejection_reason"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                        placeholder="Describe why this test result is being rejected..."
                    ></textarea>
                    <p v-if="rejectForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">{{ rejectForm.errors.rejection_reason }}</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showRejectModal = false">Cancel</SecondaryButton>
                    <button
                        @click="performReject"
                        :disabled="rejectForm.processing || !rejectForm.rejection_reason"
                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        Reject Test
                    </button>
                </div>
            </div>
        </Modal>

        <!-- Reject All Tests Modal -->
        <Modal :show="showRejectAllModal" maxWidth="md" @close="showRejectAllModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-red-600">Reject All Tests</h3>
                <p class="mt-1 text-sm text-gray-600">
                    All completed tests in this order will be sent back to the lab for re-entry.
                </p>
                <div class="mt-4">
                    <label for="reject_all_reason" class="block text-sm font-medium text-gray-700">Reason for rejection <span class="text-red-500">*</span></label>
                    <textarea
                        id="reject_all_reason"
                        v-model="rejectAllForm.rejection_reason"
                        rows="3"
                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm"
                        placeholder="Describe why these test results are being rejected..."
                    ></textarea>
                    <p v-if="rejectAllForm.errors.rejection_reason" class="mt-1 text-sm text-red-600">{{ rejectAllForm.errors.rejection_reason }}</p>
                </div>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showRejectAllModal = false">Cancel</SecondaryButton>
                    <button
                        @click="performRejectAll"
                        :disabled="rejectAllForm.processing || !rejectAllForm.rejection_reason"
                        class="inline-flex items-center rounded-md bg-red-600 px-4 py-2 text-sm font-medium text-white hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 disabled:opacity-50"
                    >
                        Reject All
                    </button>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
