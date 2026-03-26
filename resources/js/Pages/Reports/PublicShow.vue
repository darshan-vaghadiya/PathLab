<script>
export default { layout: false };
</script>

<script setup>
import { Head } from '@inertiajs/vue3';
import { formatDate } from '@/Composables/useFormatDate.js';

const props = defineProps({
    report: { type: Object, required: true },
    labSettings: { type: Object, default: () => ({}) },
    tokenValid: { type: Boolean, default: true },
});

const getHighLow = (result) => {
    if (!result?.result_value || !result?.parameter) return '';
    if (result.parameter.field_type !== 'numeric') return '';
    if (!result.parameter.ranges?.length) return '';
    const val = parseFloat(result.result_value);
    if (isNaN(val)) return '';
    const r = result.parameter.ranges[0];
    if (r.min_value !== null && val < parseFloat(r.min_value)) return 'Low';
    if (r.max_value !== null && val > parseFloat(r.max_value)) return 'High';
    return '';
};

const isAbnormal = (result) => getHighLow(result) !== '';

const getRangeDisplay = (param) => {
    if (!param?.ranges?.length) return '-';
    const r = param.ranges[0];
    if (r.min_value !== null && r.max_value !== null) return `${r.min_value} - ${r.max_value}`;
    if (r.text_value) return r.text_value;
    return '-';
};

const printReport = () => { window.open(`/report/${props.report.public_token}/print`, '_blank'); };

const patient = props.report.order?.patient;
const order = props.report.order;
</script>

<template>
    <Head :title="tokenValid ? `Report #${report.report_number}` : 'Report Expired'" />

    <!-- Token expired -->
    <div v-if="!tokenValid" class="flex min-h-screen items-center justify-center bg-gray-50 px-4">
        <div class="max-w-md text-center">
            <div class="mx-auto mb-6 flex h-20 w-20 items-center justify-center rounded-full bg-red-50">
                <svg class="h-10 w-10 text-red-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9 3.75h.008v.008H12v-.008Z" /></svg>
            </div>
            <h1 class="text-2xl font-bold text-gray-900">Report Link Expired</h1>
            <p class="mt-3 text-gray-500">This report link has expired. Please contact the laboratory for a new link.</p>
        </div>
    </div>

    <!-- Valid report -->
    <div v-else class="min-h-screen bg-gray-100 py-6">
        <div class="mx-auto max-w-4xl space-y-5 px-4">

            <!-- Lab Header -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="flex items-center justify-between bg-indigo-700 px-6 py-4 text-white">
                    <div class="flex items-center gap-4">
                        <div class="flex h-11 w-11 items-center justify-center rounded-lg bg-white/20 text-lg font-bold">{{ labSettings.lab_name?.charAt(0) ?? 'P' }}</div>
                        <div>
                            <h1 class="text-lg font-bold">{{ labSettings.lab_name || 'PathLab' }}</h1>
                            <p v-if="labSettings.lab_phone || labSettings.lab_email" class="text-xs text-indigo-200">
                                <span v-if="labSettings.lab_phone">{{ labSettings.lab_phone }}</span>
                                <span v-if="labSettings.lab_phone && labSettings.lab_email"> | </span>
                                <span v-if="labSettings.lab_email">{{ labSettings.lab_email }}</span>
                            </p>
                        </div>
                    </div>
                    <div class="text-right text-sm">
                        <p class="font-semibold">{{ report.report_number }}</p>
                        <p class="text-xs text-indigo-200">{{ formatDate(report.created_at) }}</p>
                    </div>
                </div>
                <div v-if="labSettings.lab_address" class="bg-indigo-50 px-6 py-2 text-center text-xs text-gray-600">{{ labSettings.lab_address }}</div>
            </div>

            <!-- Patient Information -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b bg-indigo-700 px-6 py-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Patient Information</h3>
                </div>
                <div class="grid grid-cols-2 gap-px bg-gray-100 sm:grid-cols-4">
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Patient Name</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ patient?.name ?? '—' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Age / Gender</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ patient?.age ?? '—' }} / {{ patient?.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : '—' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Patient ID</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ patient?.patient_id ?? '—' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Phone</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ patient?.phone ?? '—' }}</p>
                    </div>
                </div>
            </div>

            <!-- Report Details -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b bg-indigo-700 px-6 py-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Report Details</h3>
                </div>
                <div class="grid grid-cols-2 gap-px bg-gray-100 sm:grid-cols-4">
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Order No</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ order?.order_number ?? '—' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Report No</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ report.report_number }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Ref. Doctor</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ order?.referring_doctor ? 'Dr. ' + order.referring_doctor.name : 'Self' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Report Date</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(report.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Test Results -->
            <div v-for="ot in report.order?.order_tests" :key="ot.id" class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="bg-indigo-700 px-6 py-3">
                    <h4 class="text-sm font-bold uppercase tracking-wider text-white">{{ ot.test?.name }}</h4>
                </div>
                <div v-if="ot.test?.sample_type || ot.test?.method" class="border-b bg-indigo-50 px-6 py-2 text-xs text-gray-600">
                    <span v-if="ot.test?.sample_type"><strong>Sample:</strong> {{ ot.test.sample_type }} &nbsp;&nbsp;</span>
                    <span v-if="ot.test?.method"><strong>Method:</strong> {{ ot.test.method }}</span>
                </div>

                <div v-if="ot.results && ot.results.length > 0" class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead>
                            <tr class="border-b bg-gray-50">
                                <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500" style="width:35%">Investigation</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500" style="width:20%">Result</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500" style="width:25%">Reference Range</th>
                                <th class="px-6 py-2.5 text-left text-xs font-semibold uppercase tracking-wider text-gray-500" style="width:20%">Unit</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr v-for="result in ot.results" :key="result.id" class="border-b border-gray-50" :class="{ 'bg-red-50/50': isAbnormal(result) }">
                                <td class="px-6 py-2.5 text-sm font-medium text-gray-900">{{ result.parameter?.name }}</td>
                                <td class="px-6 py-2.5 text-sm" :class="isAbnormal(result) ? 'font-bold text-red-600' : 'text-gray-900'">
                                    {{ result.result_value ?? '—' }}
                                    <span v-if="getHighLow(result)" class="ml-1 text-xs font-bold text-red-500">{{ getHighLow(result) }}</span>
                                </td>
                                <td class="px-6 py-2.5 text-sm text-gray-500">{{ result.parameter ? getRangeDisplay(result.parameter) : '-' }}</td>
                                <td class="px-6 py-2.5 text-sm text-gray-500">{{ result.parameter?.unit ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div v-else class="px-6 py-4 text-sm italic text-gray-400">No results available.</div>

                <div v-if="ot.result_remarks" class="border-t border-amber-100 bg-amber-50 px-6 py-3">
                    <span class="text-xs font-bold uppercase text-amber-700">Interpretation:</span>
                    <span class="ml-2 text-sm text-amber-900">{{ ot.result_remarks }}</span>
                </div>
            </div>

            <!-- Approval -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b bg-indigo-700 px-6 py-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Approval Details</h3>
                </div>
                <div class="grid grid-cols-1 gap-px bg-gray-100 sm:grid-cols-3">
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Approved By</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ report.approved_by_user?.name ? 'Dr. ' + report.approved_by_user.name : '—' }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Approved At</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(report.approved_at) }}</p>
                    </div>
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Report Date</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ formatDate(report.created_at) }}</p>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex flex-wrap justify-center gap-3">
                <a :href="`/report/${report.public_token}/download`" class="inline-flex items-center gap-2 rounded-lg bg-indigo-600 px-6 py-3 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                    Download PDF
                </a>
                <button @click="printReport" class="inline-flex items-center gap-2 rounded-lg border border-gray-300 bg-white px-6 py-3 text-sm font-semibold text-gray-700 shadow-sm hover:bg-gray-50">
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.159 48.159 0 0 1 18.5 0m-18.5 0v-.003c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 0 1 14.32 0A2.105 2.105 0 0 1 21 7.076v.003" /></svg>
                    Print Report
                </button>
            </div>
        </div>
    </div>
</template>
