<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { reactive, computed } from 'vue';
import { formatDate } from '@/Composables/useFormatDate.js';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const orderTests = computed(() => props.order.order_tests ?? []);
const allCompleted = computed(() => orderTests.value.length === 0);

// Build a form per order_test
const forms = reactive({});

orderTests.value.forEach((ot) => {
    const parameters = ot.test?.parameters ?? [];
    const existingResults = ot.results ?? [];

    const results = parameters.map((param) => {
        const existing = existingResults.find(
            (r) => r.test_parameter_id === param.id || r.parameter_id === param.id
        );
        return {
            parameter_id: param.id,
            value: existing?.result_value ?? '',
        };
    });

    forms[ot.id] = useForm({
        results: results,
        result_remarks: ot.result_remarks ?? '',
    });
});

const saveResult = (orderTestId, orderTestUlid) => {
    forms[orderTestId].post(route('lab.enterResult', orderTestUlid), {
        preserveScroll: true,
    });
};

const getRangeDisplay = (param) => {
    if (!param.ranges || param.ranges.length === 0) return null;
    const range = param.ranges[0];
    if (range.min_value !== null && range.max_value !== null) {
        return `${range.min_value} - ${range.max_value}`;
    }
    if (range.text_value) {
        return range.text_value;
    }
    return null;
};

// Returns 'normal', 'abnormal', or null (can't determine)
const getValueStatus = (param, value) => {
    if (!value || value === '') return null;
    if (param.field_type !== 'numeric') return null;
    if (!param.ranges || param.ranges.length === 0) return null;

    const numValue = parseFloat(value);
    if (isNaN(numValue)) return null;

    const range = param.ranges[0];
    if (range.min_value === null || range.max_value === null) return null;

    const min = parseFloat(range.min_value);
    const max = parseFloat(range.max_value);
    return (numValue >= min && numValue <= max) ? 'normal' : 'abnormal';
};

const getInputClass = (param, value) => {
    const status = getValueStatus(param, value);
    if (status === 'normal') return 'border-green-400 bg-green-50 ring-1 ring-green-400 focus:border-green-500 focus:ring-green-500';
    if (status === 'abnormal') return 'border-red-400 bg-red-50 ring-1 ring-red-400 focus:border-red-500 focus:ring-red-500';
    return 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500';
};

const formatAge = (patient) => {
    if (!patient?.age) return '';
    const unitLabel = patient.age_unit === 'years' ? 'Y' : patient.age_unit === 'months' ? 'M' : 'D';
    const genderLabel = patient.gender === 'male' ? 'M' : patient.gender === 'female' ? 'F' : 'O';
    return `${patient.age}${unitLabel} / ${genderLabel}`;
};

const getReportTypeBadgeClass = (reportType) => {
    switch (reportType) {
        case 'numeric': return 'bg-blue-100 text-blue-700';
        case 'text': return 'bg-purple-100 text-purple-700';
        case 'options': return 'bg-teal-100 text-teal-700';
        default: return 'bg-gray-100 text-gray-700';
    }
};
</script>

<template>
    <Head :title="`Enter Results - ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lab.pendingResults')"
                    class="inline-flex items-center gap-1 text-sm text-gray-500 transition hover:text-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Enter Results
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
                <!-- Order Info Header -->
                <div class="mb-6 rounded-lg bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1">
                        <span class="font-mono text-lg font-bold text-indigo-700">{{ order.order_number }}</span>
                        <span class="text-base font-medium text-gray-900">{{ order.patient?.name }}</span>
                    </div>
                    <div class="mt-1 flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-gray-500">
                        <span v-if="order.patient?.phone">{{ order.patient.phone }}</span>
                        <span v-if="order.patient?.age">{{ formatAge(order.patient) }}</span>
                        <span>{{ formatDate(order.created_at) }}</span>
                    </div>
                </div>

                <!-- All Completed State -->
                <div v-if="allCompleted" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-700">All results for this order have been entered.</p>
                    <Link
                        :href="route('lab.pendingResults')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Pending Results
                    </Link>
                </div>

                <!-- Test Cards -->
                <div v-else class="space-y-5">
                    <div
                        v-for="ot in orderTests"
                        :key="ot.id"
                        class="overflow-hidden rounded-lg bg-white shadow-sm"
                    >
                        <!-- Rejection Banner -->
                        <div
                            v-if="ot.status === 'rejected' && ot.rejection_reason"
                            class="border-b border-red-200 bg-red-50 px-5 py-3"
                        >
                            <div class="flex items-start gap-2">
                                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z" />
                                </svg>
                                <div>
                                    <p class="text-sm font-semibold text-red-700">
                                        Rejected by {{ ot.rejector?.name ?? 'Doctor' }}
                                    </p>
                                    <p class="mt-0.5 text-sm text-red-600">{{ ot.rejection_reason }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Test Header -->
                        <div class="flex items-center justify-between bg-indigo-50 px-5 py-3">
                            <div class="flex items-center gap-3">
                                <span class="inline-flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600">
                                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                    </svg>
                                </span>
                                <h4 class="text-sm font-semibold text-indigo-900">{{ ot.test?.name }}</h4>
                                <span
                                    v-if="ot.test?.report_type"
                                    class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                    :class="getReportTypeBadgeClass(ot.test.report_type)"
                                >
                                    {{ ot.test.report_type }}
                                </span>
                            </div>
                            <button
                                v-if="forms[ot.id]"
                                @click="saveResult(ot.id, ot.ulid)"
                                :disabled="forms[ot.id].processing"
                                class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-1.5 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                            >
                                <svg v-if="!forms[ot.id].processing" class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                                {{ forms[ot.id].processing ? 'Saving...' : 'Save Results' }}
                            </button>
                        </div>

                        <!-- Parameters Form -->
                        <div v-if="forms[ot.id]" class="px-5 py-4">
                            <div v-if="!ot.test?.parameters || ot.test.parameters.length === 0" class="text-sm italic text-gray-400">
                                No parameters defined for this test.
                            </div>

                            <!-- Parameters Table -->
                            <div v-else class="overflow-x-auto">
                                <table class="w-full">
                                    <thead>
                                        <tr class="border-b border-gray-200">
                                            <th class="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Parameter</th>
                                            <th class="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Result</th>
                                            <th class="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Unit</th>
                                            <th class="pb-2 pr-4 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Normal Range</th>
                                            <th class="pb-2 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr
                                            v-for="(param, pIndex) in ot.test.parameters"
                                            :key="param.id"
                                            class="border-b border-gray-50 last:border-b-0"
                                        >
                                            <td class="py-2.5 pr-4 text-sm font-medium text-gray-900">
                                                {{ param.name }}
                                            </td>
                                            <td class="py-2.5 pr-4">
                                                <!-- Numeric Input -->
                                                <input
                                                    v-if="param.field_type === 'numeric'"
                                                    v-model="forms[ot.id].results[pIndex].value"
                                                    type="text"
                                                    inputmode="decimal"
                                                    class="w-36 rounded-md py-1.5 text-sm shadow-sm transition-colors"
                                                    :class="getInputClass(param, forms[ot.id].results[pIndex].value)"
                                                    placeholder="Enter value"
                                                    @keydown.enter.prevent="
                                                        pIndex < ot.test.parameters.length - 1
                                                            ? $el.closest('table').querySelectorAll('input, textarea, select')[pIndex + 1]?.focus()
                                                            : null
                                                    "
                                                />

                                                <!-- Text Input with Quick Fill -->
                                                <div v-else-if="param.field_type === 'text'">
                                                    <select
                                                        v-if="param.templates && param.templates.length"
                                                        class="mb-1 w-64 rounded-md border-gray-300 py-1 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        @change="(e) => { if (e.target.value) { forms[ot.id].results[pIndex].value = e.target.value; e.target.value = ''; } }"
                                                    >
                                                        <option value="">-- Quick Fill --</option>
                                                        <option
                                                            v-for="tpl in param.templates"
                                                            :key="tpl.id"
                                                            :value="tpl.content"
                                                        >
                                                            {{ tpl.name }}
                                                        </option>
                                                    </select>
                                                    <textarea
                                                        v-model="forms[ot.id].results[pIndex].value"
                                                        rows="2"
                                                        class="w-64 rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                        placeholder="Enter result"
                                                    ></textarea>
                                                </div>

                                                <!-- Options Dropdown -->
                                                <select
                                                    v-else-if="param.field_type === 'options'"
                                                    v-model="forms[ot.id].results[pIndex].value"
                                                    class="w-44 rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                >
                                                    <option value="">-- Select --</option>
                                                    <option
                                                        v-for="opt in (param.options ?? [])"
                                                        :key="opt"
                                                        :value="opt"
                                                    >
                                                        {{ opt }}
                                                    </option>
                                                </select>

                                                <!-- Fallback -->
                                                <input
                                                    v-else
                                                    v-model="forms[ot.id].results[pIndex].value"
                                                    type="text"
                                                    class="w-36 rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                                    placeholder="Enter value"
                                                />

                                                <div v-if="forms[ot.id].errors[`results.${pIndex}.value`]" class="mt-1 text-xs text-red-600">
                                                    {{ forms[ot.id].errors[`results.${pIndex}.value`] }}
                                                </div>
                                            </td>
                                            <td class="py-2.5 pr-4 text-sm text-gray-500">
                                                {{ param.unit ?? '' }}
                                            </td>
                                            <td class="py-2.5 text-sm text-gray-400">
                                                {{ getRangeDisplay(param) ?? '-' }}
                                            </td>
                                            <td class="py-2.5 pl-2">
                                                <span
                                                    v-if="getValueStatus(param, forms[ot.id].results[pIndex].value) === 'normal'"
                                                    class="inline-flex items-center rounded-full bg-green-100 px-2 py-0.5 text-xs font-medium text-green-700"
                                                >
                                                    Normal
                                                </span>
                                                <span
                                                    v-else-if="getValueStatus(param, forms[ot.id].results[pIndex].value) === 'abnormal'"
                                                    class="inline-flex items-center rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700"
                                                >
                                                    Abnormal
                                                </span>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Remarks -->
                            <div class="mt-4">
                                <label class="block text-xs font-medium uppercase tracking-wider text-gray-500">Remarks</label>
                                <textarea
                                    v-model="forms[ot.id].result_remarks"
                                    rows="2"
                                    class="mt-1 w-full rounded-md border-gray-300 py-1.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                    placeholder="Optional remarks for this test..."
                                ></textarea>
                            </div>

                            <!-- Success / Error Messages -->
                            <div v-if="forms[ot.id].recentlySuccessful" class="mt-2 text-sm font-medium text-emerald-600">
                                Result saved successfully.
                            </div>
                            <div v-if="forms[ot.id].errors.results" class="mt-2 text-sm text-red-600">
                                {{ forms[ot.id].errors.results }}
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
