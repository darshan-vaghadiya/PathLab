<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    settings: Object,
});

const defaultDesign = {
    primary_color: '#1a5276',
    accent_color: '#d6eaf8',
    abnormal_color: '#c0392b',
    normal_color: '#1e8449',
    font_size: '11',
    header_font_size: '22',
    logo_position: 'center',
    logo_max_height: '60',
    header_border_style: 'double',
    header_border_width: '3',
    lab_name_uppercase: true,
    show_header_subtitle: true,
    show_contact_info: true,
    show_patient_section: true,
    show_order_section: true,
    show_results_heading: true,
    show_test_method: true,
    show_status_badge: true,
    show_alternating_rows: true,
    table_border_style: 'solid',
    table_border_color: '#cccccc',
    section_heading_bg: '#1a5276',
    section_heading_color: '#ffffff',
    test_header_bg: '#eaf2f8',
    test_header_color: '#1a5276',
    signature_position: 'right',
    show_report_number_footer: true,
    show_generated_date: true,
    footer_border_style: 'solid',
    footer_border_width: '2',
    page_margin_top: '15',
    page_margin_bottom: '20',
    page_margin_sides: '12',
};

let savedDesign = {};
try { savedDesign = props.settings.report_design ? JSON.parse(props.settings.report_design) : {}; } catch (e) { savedDesign = {}; }
const design = ref({ ...defaultDesign, ...savedDesign });

const form = useForm({
    lab_name: props.settings.lab_name || '',
    lab_phone: props.settings.lab_phone || '',
    lab_email: props.settings.lab_email || '',
    lab_address: props.settings.lab_address || '',
    lab_logo: null,
    doctor_signature: null,
    doctor_name: props.settings.doctor_name || '',
    doctor_qualification: props.settings.doctor_qualification || '',
    technician_signature: null,
    technician_name: props.settings.technician_name || '',
    approver_signature: null,
    approver_name: props.settings.approver_name || '',
    approver_qualification: props.settings.approver_qualification || '',
    report_header_text: props.settings.report_header_text || '',
    report_footer_text: props.settings.report_footer_text || '',
    remove_logo: false,
    remove_signature: false,
    remove_technician_signature: false,
    remove_approver_signature: false,
    sms_enabled: props.settings.sms_enabled || '0',
    sms_gateway_url: props.settings.sms_gateway_url || '',
    sms_api_key: props.settings.sms_api_key || '',
    sms_sender_id: props.settings.sms_sender_id || '',
    sms_report_ready_template: props.settings.sms_report_ready_template || 'Dear {patient_name}, your lab report #{report_number} is ready. View at: {link} - {lab_name}',
    report_design: JSON.stringify({ ...defaultDesign, ...savedDesign }),
});

watch(design, (val) => { form.report_design = JSON.stringify(val); }, { deep: true });

const activeTab = ref('general');

const tabs = [
    { key: 'general', label: 'General', icon: 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.94-1.11.94h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z' },
    { key: 'report', label: 'Report Design', icon: 'M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-4.648l3.876-5.814a1.151 1.151 0 0 0-1.597-1.597L14.146 6.32a15.996 15.996 0 0 0-4.649 4.763m3.42 3.42a6.776 6.776 0 0 0-3.42-3.42' },
    { key: 'sms', label: 'SMS', icon: 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3m-6 18h6' },
];

// Collapsible sections for Report Design tab
const openSections = ref({ text: true, colors: true, header: false, table: false, preview: false });
const toggle = (key) => { openSections.value[key] = !openSections.value[key]; };

const today = new Date().toLocaleDateString('en-IN', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit', hour12: true });

const sampleTests = [
    {
        name: 'Complete Blood Count (CBC)', method: 'Automated Hematology Analyzer',
        parameters: [
            { name: 'Hemoglobin', result: '12.5', unit: 'g/dL', range: '13.0 - 17.0', abnormal: true },
            { name: 'RBC Count', result: '4.8', unit: 'mill/cumm', range: '4.5 - 5.5', abnormal: false },
            { name: 'WBC Count', result: '7200', unit: '/cumm', range: '4000 - 11000', abnormal: false },
            { name: 'Platelet Count', result: '2.5', unit: 'Lakh/cumm', range: '1.5 - 4.0', abnormal: false },
            { name: 'PCV (Hematocrit)', result: '38.2', unit: '%', range: '40.0 - 50.0', abnormal: true },
        ],
    },
    {
        name: 'Blood Sugar Fasting', method: 'GOD-POD',
        parameters: [{ name: 'Blood Sugar (Fasting)', result: '110', unit: 'mg/dL', range: '70 - 100', abnormal: true }],
    },
];

const previewPdfLoading = ref(false);
const downloadSamplePdf = () => {
    previewPdfLoading.value = true;
    window.open(route('admin.settings.previewPdf'), '_blank');
    setTimeout(() => { previewPdfLoading.value = false; }, 2000);
};

const logoPreview = ref(props.settings.lab_logo_url || null);
const signaturePreview = ref(props.settings.doctor_signature_url || null);
const techSigPreview = ref(props.settings.technician_signature_url || null);
const appSigPreview = ref(props.settings.approver_signature_url || null);

const onLogoChange = (e) => { const file = e.target.files[0]; if (file) { form.lab_logo = file; form.remove_logo = false; logoPreview.value = URL.createObjectURL(file); } };
const removeLogo = () => { form.lab_logo = null; form.remove_logo = true; logoPreview.value = null; const el = document.getElementById('logo_input'); if (el) el.value = ''; };
const onSignatureChange = (e) => { const file = e.target.files[0]; if (file) { form.doctor_signature = file; form.remove_signature = false; signaturePreview.value = URL.createObjectURL(file); } };
const removeSignature = () => { form.doctor_signature = null; form.remove_signature = true; signaturePreview.value = null; const el = document.getElementById('signature_input'); if (el) el.value = ''; };
const onTechSigChange = (e) => { const file = e.target.files[0]; if (file) { form.technician_signature = file; form.remove_technician_signature = false; techSigPreview.value = URL.createObjectURL(file); } };
const removeTechSig = () => { form.technician_signature = null; form.remove_technician_signature = true; techSigPreview.value = null; const el = document.getElementById('tech_sig_input'); if (el) el.value = ''; };
const onAppSigChange = (e) => { const file = e.target.files[0]; if (file) { form.approver_signature = file; form.remove_approver_signature = false; appSigPreview.value = URL.createObjectURL(file); } };
const removeAppSig = () => { form.approver_signature = null; form.remove_approver_signature = true; appSigPreview.value = null; const el = document.getElementById('app_sig_input'); if (el) el.value = ''; };

const resetDesignToDefaults = () => { Object.assign(design.value, defaultDesign); };

const colorPresets = [
    { name: 'Navy Blue', primary: '#1a5276', accent: '#d6eaf8', test_header_bg: '#eaf2f8' },
    { name: 'Dark Green', primary: '#145a32', accent: '#d5f5e3', test_header_bg: '#eafaf1' },
    { name: 'Maroon', primary: '#78281f', accent: '#f9ebea', test_header_bg: '#fdedec' },
    { name: 'Purple', primary: '#4a235a', accent: '#e8daef', test_header_bg: '#f4ecf7' },
    { name: 'Teal', primary: '#0e6655', accent: '#d1f2eb', test_header_bg: '#e8f8f5' },
    { name: 'Slate', primary: '#2c3e50', accent: '#d5dbdb', test_header_bg: '#eaecee' },
];

const applyPreset = (preset) => {
    design.value.primary_color = preset.primary;
    design.value.accent_color = preset.accent;
    design.value.test_header_bg = preset.test_header_bg;
    design.value.test_header_color = preset.primary;
    design.value.section_heading_bg = preset.primary;
    design.value.section_heading_color = '#ffffff';
};

const submit = () => {
    form.transform((data) => ({ ...data, _method: 'PUT' })).post(route('admin.settings.update'), { forceFormData: true });
};
</script>

<template>
    <Head title="Lab Settings" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">Lab Settings</h2>
        </template>

        <div class="mx-auto" :class="activeTab === 'report' ? 'max-w-5xl' : 'max-w-3xl'">
            <form @submit.prevent="submit">
                <!-- 3 Tabs -->
                <div class="mb-6 overflow-hidden rounded-lg bg-white shadow-sm">
                    <nav class="flex border-b border-gray-200">
                        <button
                            v-for="tab in tabs" :key="tab.key" type="button" @click="activeTab = tab.key"
                            class="flex flex-1 items-center justify-center gap-2 px-4 py-3.5 text-sm font-medium transition-colors"
                            :class="activeTab === tab.key ? 'border-b-2 border-indigo-500 text-indigo-600 bg-indigo-50/50' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-50'"
                        >
                            <svg class="h-4 w-4 shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" :d="tab.icon" /></svg>
                            {{ tab.label }}
                        </button>
                    </nav>
                </div>

                <!-- ═══════════ TAB: GENERAL ═══════════ -->
                <div v-show="activeTab === 'general'" class="space-y-6">

                    <!-- Lab Information -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                            <h3 class="text-sm font-semibold text-gray-700">Lab Information</h3>
                        </div>
                        <div class="p-6 space-y-5">
                            <div>
                                <InputLabel value="Lab Name *" />
                                <TextInput v-model="form.lab_name" class="mt-1 block w-full" required />
                                <InputError class="mt-1" :message="form.errors.lab_name" />
                            </div>
                            <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                <div>
                                    <InputLabel value="Phone" />
                                    <TextInput v-model="form.lab_phone" class="mt-1 block w-full" />
                                    <InputError class="mt-1" :message="form.errors.lab_phone" />
                                </div>
                                <div>
                                    <InputLabel value="Email" />
                                    <TextInput v-model="form.lab_email" type="email" class="mt-1 block w-full" />
                                    <InputError class="mt-1" :message="form.errors.lab_email" />
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Address" />
                                <textarea v-model="form.lab_address" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                <InputError class="mt-1" :message="form.errors.lab_address" />
                            </div>
                        </div>
                    </div>

                    <!-- Lab Logo -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                            <h3 class="text-sm font-semibold text-gray-700">Lab Logo</h3>
                            <p class="text-xs text-gray-400">Appears on report headers. JPG, PNG or SVG, max 2MB.</p>
                        </div>
                        <div class="p-6">
                            <div class="flex items-start gap-6">
                                <div class="flex h-24 w-24 shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden">
                                    <img v-if="logoPreview" :src="logoPreview" alt="Logo" class="h-full w-full object-contain" />
                                    <svg v-else class="h-8 w-8 text-gray-300" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Z" /></svg>
                                </div>
                                <div class="flex-1 space-y-2">
                                    <input id="logo_input" type="file" accept="image/jpeg,image/png,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-4 file:rounded-md file:border-0 file:bg-indigo-50 file:px-4 file:py-2 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" @change="onLogoChange" />
                                    <InputError class="mt-1" :message="form.errors.lab_logo" />
                                    <button v-if="logoPreview" type="button" @click="removeLogo" class="text-xs font-medium text-red-600 hover:text-red-800">Remove logo</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Report Signatures (3 blocks) -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                            <h3 class="text-sm font-semibold text-gray-700">Report Signatures</h3>
                            <p class="text-xs text-gray-400">These appear in the PDF footer. Only signatures with a name or image will show.</p>
                        </div>
                        <div class="divide-y divide-gray-100">

                            <!-- 1. Lab Technician -->
                            <div class="p-6 space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600">1. Lab Technician</h4>
                                <div>
                                    <InputLabel value="Technician Name" />
                                    <TextInput v-model="form.technician_name" class="mt-1 block w-full" placeholder="Lab Technician Name" />
                                </div>
                                <div>
                                    <InputLabel value="Signature Image" />
                                    <div class="mt-1 flex items-start gap-4">
                                        <div class="flex h-14 w-36 shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden">
                                            <img v-if="techSigPreview" :src="techSigPreview" alt="Signature" class="h-full w-full object-contain" />
                                            <span v-else class="text-xs text-gray-300">No image</span>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <input id="tech_sig_input" type="file" accept="image/jpeg,image/png,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" @change="onTechSigChange" />
                                            <button v-if="techSigPreview" type="button" @click="removeTechSig" class="text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 2. Doctor / Pathologist -->
                            <div class="p-6 space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600">2. Doctor / Pathologist</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel value="Doctor Name" />
                                        <TextInput v-model="form.doctor_name" class="mt-1 block w-full" placeholder="Dr. John Doe" />
                                        <InputError class="mt-1" :message="form.errors.doctor_name" />
                                    </div>
                                    <div>
                                        <InputLabel value="Qualification" />
                                        <TextInput v-model="form.doctor_qualification" class="mt-1 block w-full" placeholder="MD, Pathology" />
                                        <InputError class="mt-1" :message="form.errors.doctor_qualification" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Signature Image" />
                                    <div class="mt-1 flex items-start gap-4">
                                        <div class="flex h-14 w-36 shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden">
                                            <img v-if="signaturePreview" :src="signaturePreview" alt="Signature" class="h-full w-full object-contain" />
                                            <span v-else class="text-xs text-gray-300">No image</span>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <input id="signature_input" type="file" accept="image/jpeg,image/png,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" @change="onSignatureChange" />
                                            <InputError class="mt-1" :message="form.errors.doctor_signature" />
                                            <button v-if="signaturePreview" type="button" @click="removeSignature" class="text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- 3. Approver / Authorized Signatory -->
                            <div class="p-6 space-y-4">
                                <h4 class="text-xs font-bold uppercase tracking-wider text-indigo-600">3. Approver / Authorized Signatory</h4>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div>
                                        <InputLabel value="Approver Name" />
                                        <TextInput v-model="form.approver_name" class="mt-1 block w-full" placeholder="Dr. Approver Name" />
                                    </div>
                                    <div>
                                        <InputLabel value="Qualification" />
                                        <TextInput v-model="form.approver_qualification" class="mt-1 block w-full" placeholder="MD, Pathology" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Signature Image" />
                                    <div class="mt-1 flex items-start gap-4">
                                        <div class="flex h-14 w-36 shrink-0 items-center justify-center rounded-lg border-2 border-dashed border-gray-200 bg-gray-50 overflow-hidden">
                                            <img v-if="appSigPreview" :src="appSigPreview" alt="Signature" class="h-full w-full object-contain" />
                                            <span v-else class="text-xs text-gray-300">No image</span>
                                        </div>
                                        <div class="flex-1 space-y-1">
                                            <input id="app_sig_input" type="file" accept="image/jpeg,image/png,image/svg+xml" class="block w-full text-sm text-gray-500 file:mr-3 file:rounded-md file:border-0 file:bg-indigo-50 file:px-3 file:py-1.5 file:text-sm file:font-medium file:text-indigo-700 hover:file:bg-indigo-100" @change="onAppSigChange" />
                                            <button v-if="appSigPreview" type="button" @click="removeAppSig" class="text-xs font-medium text-red-600 hover:text-red-800">Remove</button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════════ TAB: REPORT DESIGN ═══════════ -->
                <div v-show="activeTab === 'report'" class="space-y-3">

                    <!-- Section: Report Text -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <button type="button" @click="toggle('text')" class="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-3 text-left">
                            <h3 class="text-sm font-semibold text-gray-700">Report Text</h3>
                            <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.text }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div v-show="openSections.text" class="p-6 space-y-5">
                            <div>
                                <InputLabel value="Report Header Text" />
                                <textarea v-model="form.report_header_text" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. NABL Accredited | ISO 15189:2012"></textarea>
                                <InputError class="mt-1" :message="form.errors.report_header_text" />
                            </div>
                            <div>
                                <InputLabel value="Report Footer Text" />
                                <textarea v-model="form.report_footer_text" rows="2" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500" placeholder="e.g. This is a computer-generated report."></textarea>
                                <InputError class="mt-1" :message="form.errors.report_footer_text" />
                            </div>
                        </div>
                    </div>

                    <!-- Section: Color Theme -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <button type="button" @click="toggle('colors')" class="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-3 text-left">
                            <div class="flex items-center gap-3">
                                <h3 class="text-sm font-semibold text-gray-700">Color Theme</h3>
                                <div class="flex -space-x-1">
                                    <span class="h-4 w-4 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: design.primary_color }"></span>
                                    <span class="h-4 w-4 rounded-full border-2 border-white shadow-sm" :style="{ backgroundColor: design.accent_color }"></span>
                                </div>
                            </div>
                            <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.colors }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div v-show="openSections.colors" class="p-6 space-y-5">
                            <!-- Presets -->
                            <div>
                                <div class="flex items-center justify-between mb-2">
                                    <InputLabel value="Quick Presets" />
                                    <button type="button" @click="resetDesignToDefaults" class="text-xs text-gray-500 hover:text-gray-700 underline">Reset All</button>
                                </div>
                                <div class="flex flex-wrap gap-2">
                                    <button v-for="preset in colorPresets" :key="preset.name" type="button" @click="applyPreset(preset)"
                                        class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-xs font-medium transition-colors hover:shadow-sm"
                                        :class="design.primary_color === preset.primary ? 'border-gray-400 bg-gray-100' : 'border-gray-200 hover:border-gray-300'">
                                        <span class="h-3 w-3 rounded-full" :style="{ backgroundColor: preset.primary }"></span>
                                        {{ preset.name }}
                                    </button>
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4 sm:grid-cols-3">
                                <div v-for="{ key, label } in [
                                    { key: 'primary_color', label: 'Primary' },
                                    { key: 'accent_color', label: 'Accent / Table Head' },
                                    { key: 'abnormal_color', label: 'Abnormal' },
                                    { key: 'normal_color', label: 'Normal' },
                                    { key: 'section_heading_bg', label: 'Section Heading' },
                                    { key: 'table_border_color', label: 'Table Border' },
                                ]" :key="key">
                                    <InputLabel :value="label" />
                                    <div class="mt-1 flex items-center gap-2">
                                        <input type="color" v-model="design[key]" class="h-8 w-10 cursor-pointer rounded border border-gray-300" />
                                        <TextInput v-model="design[key]" class="block w-full font-mono text-xs" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Header & Layout -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <button type="button" @click="toggle('header')" class="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-3 text-left">
                            <h3 class="text-sm font-semibold text-gray-700">Header & Layout</h3>
                            <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.header }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div v-show="openSections.header" class="p-6 space-y-5">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <InputLabel value="Logo Position" />
                                    <select v-model="design.logo_position" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="left">Left</option><option value="center">Center</option><option value="right">Right</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Logo Max Height (px)" />
                                    <TextInput v-model="design.logo_max_height" type="number" min="30" max="120" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <InputLabel value="Lab Name Font (px)" />
                                    <TextInput v-model="design.header_font_size" type="number" min="14" max="32" class="mt-1 block w-full" />
                                </div>
                            </div>
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <InputLabel value="Header Border" />
                                    <select v-model="design.header_border_style" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="double">Double</option><option value="solid">Solid</option><option value="dashed">Dashed</option><option value="none">None</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Border Width (px)" />
                                    <TextInput v-model="design.header_border_width" type="number" min="1" max="6" class="mt-1 block w-full" />
                                </div>
                                <div>
                                    <InputLabel value="Body Font Size (px)" />
                                    <TextInput v-model="design.font_size" type="number" min="8" max="14" class="mt-1 block w-full" />
                                </div>
                            </div>
                            <div class="flex flex-wrap gap-x-6 gap-y-2">
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.lab_name_uppercase" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Uppercase Name</label>
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_header_subtitle" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Subtitle</label>
                                <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_contact_info" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Contact Info</label>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Table & Footer -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <button type="button" @click="toggle('table')" class="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-3 text-left">
                            <h3 class="text-sm font-semibold text-gray-700">Table, Footer & Toggles</h3>
                            <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.table }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </button>
                        <div v-show="openSections.table" class="p-6 space-y-5">
                            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                                <div>
                                    <InputLabel value="Table Border Style" />
                                    <select v-model="design.table_border_style" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="solid">Solid</option><option value="dashed">Dashed</option><option value="dotted">Dotted</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Signature Position" />
                                    <select v-model="design.signature_position" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="right">Right</option><option value="center">Center</option><option value="left">Left</option>
                                    </select>
                                </div>
                                <div>
                                    <InputLabel value="Footer Border" />
                                    <select v-model="design.footer_border_style" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500">
                                        <option value="solid">Solid</option><option value="double">Double</option><option value="dashed">Dashed</option><option value="none">None</option>
                                    </select>
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Page Margins (mm)" />
                                <div class="mt-2 grid grid-cols-3 gap-3">
                                    <div><label class="text-xs text-gray-500">Top</label><TextInput v-model="design.page_margin_top" type="number" min="5" max="30" class="mt-0.5 block w-full" /></div>
                                    <div><label class="text-xs text-gray-500">Bottom</label><TextInput v-model="design.page_margin_bottom" type="number" min="5" max="30" class="mt-0.5 block w-full" /></div>
                                    <div><label class="text-xs text-gray-500">Sides</label><TextInput v-model="design.page_margin_sides" type="number" min="5" max="30" class="mt-0.5 block w-full" /></div>
                                </div>
                            </div>
                            <div>
                                <InputLabel value="Show / Hide Sections" />
                                <div class="mt-2 flex flex-wrap gap-x-5 gap-y-2">
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_patient_section" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Patient Info</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_order_section" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Order Info</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_results_heading" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Results Heading</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_test_method" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Test Method</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_status_badge" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Status Badge</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_alternating_rows" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Alternating Rows</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_report_number_footer" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Report No. Footer</label>
                                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" v-model="design.show_generated_date" class="h-4 w-4 rounded border-gray-300 text-indigo-600" /> Date Footer</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Section: Live Preview -->
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <button type="button" @click="toggle('preview')" class="flex w-full items-center justify-between border-b border-gray-200 bg-gray-50 px-6 py-3 text-left">
                            <h3 class="text-sm font-semibold text-gray-700">Live Preview</h3>
                            <div class="flex items-center gap-3">
                                <button type="button" @click.stop="downloadSamplePdf" :disabled="previewPdfLoading" class="inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-3 py-1 text-xs font-medium text-white hover:bg-indigo-700 disabled:opacity-50">
                                    <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
                                    {{ previewPdfLoading ? 'Opening...' : 'Sample PDF' }}
                                </button>
                                <svg class="h-4 w-4 text-gray-400 transition-transform" :class="{ 'rotate-180': openSections.preview }" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                            </div>
                        </button>
                        <div v-show="openSections.preview">
                            <div class="overflow-auto bg-gray-200 p-4 sm:p-6">
                                <div class="mx-auto bg-white shadow-lg" :style="{
                                    maxWidth: '680px', minHeight: '900px', fontFamily: '\'Segoe UI\', Arial, sans-serif',
                                    fontSize: design.font_size + 'px', color: '#1a1a1a', lineHeight: '1.4',
                                    padding: design.page_margin_top + 'px ' + design.page_margin_sides * 2 + 'px ' + design.page_margin_bottom + 'px',
                                }">
                                    <!-- HEADER -->
                                    <div :style="{ textAlign: design.logo_position, borderBottom: design.header_border_style !== 'none' ? design.header_border_width + 'px ' + design.header_border_style + ' ' + design.primary_color : 'none', paddingBottom: '10px', marginBottom: '14px' }">
                                        <div v-if="logoPreview" :style="{ marginBottom: '6px', textAlign: design.logo_position }">
                                            <img :src="logoPreview" alt="Logo" :style="{ maxHeight: design.logo_max_height + 'px', maxWidth: '200px' }" />
                                        </div>
                                        <div :style="{ fontSize: design.header_font_size + 'px', fontWeight: 'bold', color: design.primary_color, textTransform: design.lab_name_uppercase ? 'uppercase' : 'none', letterSpacing: '1px', marginBottom: '2px', textAlign: design.logo_position }">{{ form.lab_name || 'Your Lab Name' }}</div>
                                        <div v-if="design.show_header_subtitle" :style="{ fontSize: (parseInt(design.font_size) - 1) + 'px', color: '#555', marginBottom: '4px', textAlign: design.logo_position }">{{ form.report_header_text || 'Diagnostic & Pathology Services' }}</div>
                                        <div v-if="design.show_contact_info" :style="{ fontSize: (parseInt(design.font_size) - 2) + 'px', color: '#333', lineHeight: '1.5', textAlign: design.logo_position }">
                                            <span v-if="form.lab_address">{{ form.lab_address }}<br/></span>
                                            <span v-if="form.lab_phone" style="margin-right:10px;">Phone: {{ form.lab_phone }}</span>
                                            <span v-if="form.lab_email">Email: {{ form.lab_email }}</span>
                                        </div>
                                    </div>

                                    <!-- PATIENT INFO -->
                                    <table v-if="design.show_patient_section" :style="{ width: '100%', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, borderCollapse: 'collapse', marginBottom: '10px' }">
                                        <tr><td colspan="4" :style="{ backgroundColor: design.section_heading_bg, color: design.section_heading_color, fontWeight: 'bold', fontSize: design.font_size + 'px', textTransform: 'uppercase', padding: '5px 8px' }">Patient Information</td></tr>
                                        <tr>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444', width: '25%' }">Patient Name</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">Rajesh Kumar</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444', width: '25%' }">Patient ID</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">PL-0042</td>
                                        </tr>
                                        <tr>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444' }">Age / Gender</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">35 Yrs / Male</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444' }">Date</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">{{ today }}</td>
                                        </tr>
                                    </table>

                                    <!-- ORDER INFO -->
                                    <table v-if="design.show_order_section" :style="{ width: '100%', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, borderCollapse: 'collapse', marginBottom: '10px' }">
                                        <tr><td colspan="4" :style="{ backgroundColor: design.section_heading_bg, color: design.section_heading_color, fontWeight: 'bold', fontSize: design.font_size + 'px', textTransform: 'uppercase', padding: '5px 8px' }">Order Information</td></tr>
                                        <tr>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444', width: '25%' }">Order No.</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">ORD-20260319-0001</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444', width: '25%' }">Report No.</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">RPT-20260319-0001</td>
                                        </tr>
                                        <tr>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444' }">Referring Doctor</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">Dr. Sharma</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd', fontWeight: 'bold', color: '#444' }">Report Date</td>
                                            <td :style="{ padding: '5px 8px', border: '1px ' + design.table_border_style + ' #ddd' }">{{ today }}</td>
                                        </tr>
                                    </table>

                                    <!-- RESULTS HEADING -->
                                    <div v-if="design.show_results_heading" :style="{ backgroundColor: design.section_heading_bg, color: design.section_heading_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) + 1) + 'px', textTransform: 'uppercase', padding: '6px 8px', textAlign: 'center', marginTop: '5px' }">Test Results</div>

                                    <!-- TESTS -->
                                    <div v-for="(test, ti) in sampleTests" :key="ti">
                                        <div :style="{ backgroundColor: design.test_header_bg, border: '1px ' + design.table_border_style + ' ' + design.accent_color, borderBottom: 'none', padding: '6px 10px', fontSize: (parseInt(design.font_size) + 1) + 'px', fontWeight: 'bold', color: design.test_header_color, textTransform: 'uppercase', marginTop: '8px' }">
                                            {{ test.name }}
                                            <span v-if="design.show_test_method" :style="{ fontSize: (parseInt(design.font_size) - 2) + 'px', fontWeight: 'normal', color: '#666', textTransform: 'none', marginLeft: '10px' }">(Method: {{ test.method }})</span>
                                        </div>
                                        <table :style="{ width: '100%', borderCollapse: 'collapse', marginBottom: '2px' }">
                                            <thead>
                                                <tr>
                                                    <th :style="{ backgroundColor: design.accent_color, color: design.primary_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) - 1) + 'px', textTransform: 'uppercase', padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'left', width: '28%' }">Parameter</th>
                                                    <th :style="{ backgroundColor: design.accent_color, color: design.primary_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) - 1) + 'px', textTransform: 'uppercase', padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center', width: '17%' }">Result</th>
                                                    <th :style="{ backgroundColor: design.accent_color, color: design.primary_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) - 1) + 'px', textTransform: 'uppercase', padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center', width: '10%' }">Unit</th>
                                                    <th :style="{ backgroundColor: design.accent_color, color: design.primary_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) - 1) + 'px', textTransform: 'uppercase', padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center', width: '28%' }">Normal Range</th>
                                                    <th v-if="design.show_status_badge" :style="{ backgroundColor: design.accent_color, color: design.primary_color, fontWeight: 'bold', fontSize: (parseInt(design.font_size) - 1) + 'px', textTransform: 'uppercase', padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center', width: '17%' }">Status</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <tr v-for="(param, pi) in test.parameters" :key="pi" :style="{ backgroundColor: (pi % 2 === 1 && design.show_alternating_rows) ? '#f9f9f9' : 'transparent' }">
                                                    <td :style="{ padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'left', fontWeight: '500' }">{{ param.name }}</td>
                                                    <td :style="{ padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center', color: param.abnormal ? design.abnormal_color : '#1a1a1a', fontWeight: param.abnormal ? 'bold' : 'normal' }">{{ param.result }}</td>
                                                    <td :style="{ padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center' }">{{ param.unit }}</td>
                                                    <td :style="{ padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center' }">{{ param.range }}</td>
                                                    <td v-if="design.show_status_badge" :style="{ padding: '5px 6px', border: '1px ' + design.table_border_style + ' ' + design.table_border_color, textAlign: 'center' }">
                                                        <span :style="{ display: 'inline-block', padding: '1px 8px', borderRadius: '3px', fontSize: '9px', fontWeight: 'bold', textTransform: 'uppercase', backgroundColor: param.abnormal ? '#fadbd8' : '#d5f5e3', color: param.abnormal ? design.abnormal_color : design.normal_color }">{{ param.abnormal ? 'Abnormal' : 'Normal' }}</span>
                                                    </td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </div>

                                    <!-- FOOTER -->
                                    <div :style="{ borderTop: design.footer_border_style !== 'none' ? design.footer_border_width + 'px ' + design.footer_border_style + ' ' + design.primary_color : 'none', marginTop: '20px', paddingTop: '8px' }">
                                        <div :style="{ fontSize: (parseInt(design.font_size) - 2) + 'px', color: '#999', textAlign: 'center', marginBottom: '10px' }">
                                            <span v-if="design.show_report_number_footer">Report: <strong>RPT-20260319-0001</strong> &nbsp;|&nbsp; </span>
                                            Order: <strong>ORD-20260319-0001</strong>
                                            <span v-if="design.show_generated_date"> &nbsp;|&nbsp; Generated: {{ today }}</span>
                                        </div>
                                        <table style="width: 100%; border-collapse: collapse;">
                                            <tr>
                                                <td v-if="form.technician_name || techSigPreview" style="text-align: center; vertical-align: bottom; padding: 0 6px;">
                                                    <div v-if="techSigPreview" style="margin-bottom: 2px;"><img :src="techSigPreview" style="max-height: 35px; max-width: 120px;" /></div>
                                                    <div v-else style="height: 35px;"></div>
                                                    <div style="border-top: 1px solid #555; padding-top: 3px; display: inline-block; min-width: 120px;">
                                                        <div :style="{ fontSize: (parseInt(design.font_size) - 1) + 'px', fontWeight: 'bold', color: design.primary_color }">{{ form.technician_name || 'Lab Technician' }}</div>
                                                    </div>
                                                </td>
                                                <td v-if="form.doctor_name || signaturePreview" style="text-align: center; vertical-align: bottom; padding: 0 6px;">
                                                    <div v-if="signaturePreview" style="margin-bottom: 2px;"><img :src="signaturePreview" style="max-height: 35px; max-width: 120px;" /></div>
                                                    <div v-else style="height: 35px;"></div>
                                                    <div style="border-top: 1px solid #555; padding-top: 3px; display: inline-block; min-width: 120px;">
                                                        <div :style="{ fontSize: (parseInt(design.font_size) - 1) + 'px', fontWeight: 'bold', color: design.primary_color }">{{ form.doctor_name }}</div>
                                                        <div v-if="form.doctor_qualification" :style="{ fontSize: (parseInt(design.font_size) - 2) + 'px', color: '#666' }">({{ form.doctor_qualification }})</div>
                                                    </div>
                                                </td>
                                                <td v-if="form.approver_name || appSigPreview" style="text-align: center; vertical-align: bottom; padding: 0 6px;">
                                                    <div v-if="appSigPreview" style="margin-bottom: 2px;"><img :src="appSigPreview" style="max-height: 35px; max-width: 120px;" /></div>
                                                    <div v-else style="height: 35px;"></div>
                                                    <div style="border-top: 1px solid #555; padding-top: 3px; display: inline-block; min-width: 120px;">
                                                        <div :style="{ fontSize: (parseInt(design.font_size) - 1) + 'px', fontWeight: 'bold', color: design.primary_color }">{{ form.approver_name }}</div>
                                                        <div v-if="form.approver_qualification" :style="{ fontSize: (parseInt(design.font_size) - 2) + 'px', color: '#666' }">({{ form.approver_qualification }})</div>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                        <div :style="{ textAlign: 'center', marginTop: '8px', fontSize: (parseInt(design.font_size) - 2.5) + 'px', color: '#aaa', fontStyle: 'italic' }">
                                            {{ form.report_footer_text || 'This is a computer-generated report. Please correlate clinically.' }}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- ═══════════ TAB: SMS ═══════════ -->
                <div v-show="activeTab === 'sms'" class="space-y-6">
                    <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                        <div class="border-b border-gray-200 bg-gray-50 px-6 py-3">
                            <h3 class="text-sm font-semibold text-gray-700">SMS Configuration</h3>
                            <p class="text-xs text-gray-400">Configure SMS gateway for sending report notifications to patients.</p>
                        </div>
                        <div class="p-6 space-y-5">
                            <div class="flex items-center gap-3">
                                <input id="sms_enabled" type="checkbox" :checked="form.sms_enabled === '1'" @change="form.sms_enabled = $event.target.checked ? '1' : '0'" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500" />
                                <InputLabel for="sms_enabled" value="Enable SMS Notifications" class="!mb-0" />
                            </div>
                            <div class="rounded-lg border border-gray-200 bg-gray-50/50 p-4 space-y-5" :class="{ 'opacity-50 pointer-events-none': form.sms_enabled !== '1' }">
                                <div>
                                    <InputLabel value="Gateway URL" />
                                    <TextInput v-model="form.sms_gateway_url" class="mt-1 block w-full" placeholder="https://api.smsgateway.com/send" />
                                    <InputError class="mt-1" :message="form.errors.sms_gateway_url" />
                                </div>
                                <div class="grid grid-cols-1 gap-5 sm:grid-cols-2">
                                    <div>
                                        <InputLabel value="API Key" />
                                        <TextInput v-model="form.sms_api_key" type="password" class="mt-1 block w-full" placeholder="Your API key" />
                                        <InputError class="mt-1" :message="form.errors.sms_api_key" />
                                    </div>
                                    <div>
                                        <InputLabel value="Sender ID" />
                                        <TextInput v-model="form.sms_sender_id" class="mt-1 block w-full" placeholder="LABSMS" />
                                        <InputError class="mt-1" :message="form.errors.sms_sender_id" />
                                    </div>
                                </div>
                                <div>
                                    <InputLabel value="Report Ready Template" />
                                    <textarea v-model="form.sms_report_ready_template" rows="3" class="mt-1 block w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
                                    <div class="mt-2 rounded-md bg-blue-50 p-3">
                                        <p class="text-xs font-medium text-blue-700 mb-1">Available placeholders:</p>
                                        <div class="flex flex-wrap gap-2">
                                            <code class="rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-800">{patient_name}</code>
                                            <code class="rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-800">{report_number}</code>
                                            <code class="rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-800">{link}</code>
                                            <code class="rounded bg-blue-100 px-1.5 py-0.5 text-xs text-blue-800">{lab_name}</code>
                                        </div>
                                    </div>
                                    <InputError class="mt-1" :message="form.errors.sms_report_ready_template" />
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Submit -->
                <div class="mt-6 flex items-center justify-end gap-4">
                    <Transition enter-active-class="transition ease-in-out" enter-from-class="opacity-0" leave-active-class="transition ease-in-out" leave-to-class="opacity-0">
                        <p v-if="form.recentlySuccessful" class="text-sm text-green-600">Saved.</p>
                    </Transition>
                    <PrimaryButton :disabled="form.processing" class="px-8">{{ form.processing ? 'Saving...' : 'Save Settings' }}</PrimaryButton>
                </div>
            </form>
        </div>
    </AuthenticatedLayout>
</template>
