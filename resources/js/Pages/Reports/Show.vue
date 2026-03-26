<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { formatDate } from '@/Composables/useFormatDate.js';

const copySuccess = ref(false);
const sendingSms = ref(false);

const props = defineProps({
    report: { type: Object, required: true },
    doctor: { type: Object, default: null },
});

const copyPublicLink = () => {
    if (props.report.public_url) {
        navigator.clipboard.writeText(props.report.public_url).then(() => {
            copySuccess.value = true;
            setTimeout(() => { copySuccess.value = false; }, 2000);
        });
    }
};

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

const printReport = () => { window.open(route('reports.pdf', props.report.ulid), '_blank'); };
const patientHasPhone = !!props.report.order?.patient?.phone;

const sendSms = () => {
    if (sendingSms.value) return;
    sendingSms.value = true;
    router.post(route('reports.sendSms', props.report.ulid), {}, {
        preserveScroll: true,
        onFinish: () => { sendingSms.value = false; },
    });
};

const patient = props.report.order?.patient;
const order = props.report.order;
</script>

<template>
    <Head :title="`Report #${report.report_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Report #{{ report.report_number }}</h2>
                <div class="flex flex-wrap gap-2">
                    <button @click="printReport" class="inline-flex items-center gap-1.5 rounded-md bg-gray-600 px-3 py-2 text-sm font-medium text-white hover:bg-gray-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6.72 13.829c-.24.03-.48.062-.72.096m.72-.096a42.415 42.415 0 0 1 10.56 0m-10.56 0L6.34 18m10.94-4.171c.24.03.48.062.72.096m-.72-.096L17.66 18m0 0 .229 2.523a1.125 1.125 0 0 1-1.12 1.227H7.231c-.662 0-1.18-.568-1.12-1.227L6.34 18m11.318 0h1.091A2.25 2.25 0 0 0 21 15.75V9.456c0-1.081-.768-2.015-1.837-2.175a48.055 48.055 0 0 0-1.913-.247M6.34 18H5.25A2.25 2.25 0 0 1 3 15.75V9.456c0-1.081.768-2.015 1.837-2.175a48.041 48.041 0 0 1 1.913-.247m0 0a48.159 48.159 0 0 1 18.5 0m-18.5 0v-.003c0-1.18.91-2.164 2.09-2.201a51.964 51.964 0 0 1 14.32 0A2.105 2.105 0 0 1 21 7.076v.003" /></svg>
                        Print
                    </button>
                    <a :href="route('reports.download', report.ulid)" class="inline-flex items-center gap-1.5 rounded-md bg-green-600 px-3 py-2 text-sm font-medium text-white hover:bg-green-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                        Download
                    </a>
                    <button v-if="report.public_url" @click="copyPublicLink" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-2 text-sm font-medium text-white hover:bg-indigo-700">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" /></svg>
                        {{ copySuccess ? 'Copied!' : 'Patient Link' }}
                    </button>
                    <button @click="sendSms" :disabled="!patientHasPhone || sendingSms" class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-3 py-2 text-sm font-medium text-white hover:bg-emerald-700 disabled:opacity-50">
                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-3 18.75h3" /></svg>
                        {{ sendingSms ? 'Sending...' : 'SMS' }}
                    </button>
                </div>
            </div>
        </template>

        <div class="mx-auto max-w-5xl space-y-5">

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
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ patient?.age ?? '—' }} {{ patient?.age_unit ?? 'Yrs' }} / {{ patient?.gender ? patient.gender.charAt(0).toUpperCase() + patient.gender.slice(1) : '—' }}</p>
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
                <!-- Test title bar -->
                <div class="bg-indigo-700 px-6 py-3">
                    <div class="flex items-center justify-between">
                        <h4 class="text-sm font-bold uppercase tracking-wider text-white">{{ ot.test?.name }}</h4>
                        <span v-if="ot.status === 'approved'" class="rounded-full bg-white/20 px-2.5 py-0.5 text-xs font-semibold text-white">Approved</span>
                    </div>
                </div>

                <!-- Sample & Method -->
                <div v-if="ot.test?.sample_type || ot.test?.method" class="border-b bg-indigo-50 px-6 py-2 text-xs text-gray-600">
                    <span v-if="ot.test?.sample_type"><strong>Sample:</strong> {{ ot.test.sample_type }} &nbsp;&nbsp;</span>
                    <span v-if="ot.test?.method"><strong>Method:</strong> {{ ot.test.method }}</span>
                </div>

                <!-- Results table -->
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

                <!-- Interpretation / Remarks -->
                <div v-if="ot.result_remarks" class="border-t border-amber-100 bg-amber-50 px-6 py-3">
                    <span class="text-xs font-bold uppercase text-amber-700">Interpretation:</span>
                    <span class="ml-2 text-sm text-amber-900">{{ ot.result_remarks }}</span>
                </div>
            </div>

            <!-- Approval Info -->
            <div class="overflow-hidden rounded-lg bg-white shadow-sm">
                <div class="border-b bg-indigo-700 px-6 py-3">
                    <h3 class="text-sm font-bold uppercase tracking-wider text-white">Approval Details</h3>
                </div>
                <div class="grid grid-cols-1 gap-px bg-gray-100 sm:grid-cols-3">
                    <div class="bg-white p-4">
                        <p class="text-xs font-medium text-gray-400">Approved By</p>
                        <p class="mt-1 text-sm font-semibold text-gray-900">{{ doctor?.name ? 'Dr. ' + doctor.name : 'N/A' }}</p>
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
        </div>
    </AuthenticatedLayout>
</template>
