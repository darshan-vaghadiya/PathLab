<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';

const props = defineProps({
    patients: Array,
    tests: Object, // grouped by category: { "Hematology": [...], "Biochemistry": [...] }
    packages: Array,
    referringDoctors: Array,
    b2bClients: Array,
});

const currentStep = ref(1);
const totalSteps = 4;

// Patient search
const patientSearch = ref('');
const showPatientResults = ref(false);
const createNewPatient = ref(false);

const filteredPatients = computed(() => {
    if (!patientSearch.value || patientSearch.value.length < 2) return [];
    const query = patientSearch.value.toLowerCase();
    return (props.patients || []).filter(p =>
        p.name.toLowerCase().includes(query) ||
        (p.phone && p.phone.includes(query)) ||
        (p.patient_id && p.patient_id.toLowerCase().includes(query))
    ).slice(0, 10);
});

// Form
const form = useForm({
    // Patient
    patient_id: null,
    new_patient: {
        name: '',
        age: '',
        age_unit: 'years',
        gender: '',
        phone: '',
    },
    // Source
    source: 'walk_in',
    referring_doctor_id: null,
    b2b_client_id: null,
    home_visit_address: '',
    home_visit_charge: 0,
    // Tests & Packages
    selected_tests: [],
    selected_packages: [],
    // Billing
    discount: 0,
    discount_type: 'amount', // amount or percent
    payment_mode: 'cash',
    amount_paid: 0,
    notes: '',
});

// Select patient
const selectPatient = (patient) => {
    form.patient_id = patient.id;
    patientSearch.value = `${patient.name} - ${patient.phone || 'No phone'}`;
    showPatientResults.value = false;
    createNewPatient.value = false;
};

const startNewPatient = () => {
    createNewPatient.value = true;
    form.patient_id = null;
    showPatientResults.value = false;
};

const cancelNewPatient = () => {
    createNewPatient.value = false;
    form.new_patient = { name: '', age: '', age_unit: 'years', gender: '', phone: '' };
};

// Test selection
const toggleTest = (testId) => {
    const index = form.selected_tests.indexOf(testId);
    if (index > -1) {
        form.selected_tests.splice(index, 1);
    } else {
        form.selected_tests.push(testId);
    }
};

const togglePackage = (packageId) => {
    const index = form.selected_packages.indexOf(packageId);
    if (index > -1) {
        form.selected_packages.splice(index, 1);
    } else {
        form.selected_packages.push(packageId);
    }
};

const isTestSelected = (testId) => form.selected_tests.includes(testId);
const isPackageSelected = (packageId) => form.selected_packages.includes(packageId);

// All tests flattened for price lookup
const allTests = computed(() => {
    const list = [];
    if (props.tests) {
        Object.values(props.tests).forEach(tests => {
            tests.forEach(t => list.push(t));
        });
    }
    return list;
});

// Price calculations
const selectedTestItems = computed(() => {
    return allTests.value.filter(t => form.selected_tests.includes(t.id));
});

const selectedPackageItems = computed(() => {
    return (props.packages || []).filter(p => form.selected_packages.includes(p.id));
});

const testsTotal = computed(() => {
    return selectedTestItems.value.reduce((sum, t) => sum + parseFloat(t.price || 0), 0);
});

const packagesTotal = computed(() => {
    return selectedPackageItems.value.reduce((sum, p) => sum + parseFloat(p.price || 0), 0);
});

const homeVisitCharge = computed(() => {
    return form.source === 'home_visit' ? parseFloat(form.home_visit_charge || 0) : 0;
});

const subtotal = computed(() => {
    return testsTotal.value + packagesTotal.value + homeVisitCharge.value;
});

const discountAmount = computed(() => {
    if (form.discount_type === 'percent') {
        return (subtotal.value * parseFloat(form.discount || 0)) / 100;
    }
    return parseFloat(form.discount || 0);
});

const netAmount = computed(() => {
    return Math.max(0, subtotal.value - discountAmount.value);
});

const balanceDue = computed(() => {
    return Math.max(0, netAmount.value - parseFloat(form.amount_paid || 0));
});

// Price formatting
const formatPrice = (p) => '\u20B9' + parseFloat(p || 0).toFixed(0);

// Step navigation
const canProceed = computed(() => {
    switch (currentStep.value) {
        case 1:
            return form.patient_id || (createNewPatient.value && form.new_patient.name && form.new_patient.gender);
        case 2:
            if (form.source === 'referral') return !!form.referring_doctor_id;
            if (form.source === 'b2b') return !!form.b2b_client_id;
            if (form.source === 'home_visit') return !!form.home_visit_address;
            return true;
        case 3:
            return form.selected_tests.length > 0 || form.selected_packages.length > 0;
        case 4:
            return true;
        default:
            return false;
    }
});

const nextStep = () => {
    if (currentStep.value < totalSteps && canProceed.value) {
        currentStep.value++;
    }
};

const prevStep = () => {
    if (currentStep.value > 1) {
        currentStep.value--;
    }
};

// Category expansion state
const expandedCategories = ref({});
const toggleCategory = (category) => {
    expandedCategories.value[category] = !expandedCategories.value[category];
};

// Submit
const submit = () => {
    form.post(route('orders.store'));
};
</script>

<template>
    <Head title="New Order" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('orders.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    New Order
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-5xl">
                <!-- Step Indicator -->
                <div class="mb-8">
                    <nav class="flex items-center justify-center" aria-label="Progress">
                        <ol class="flex items-center space-x-5">
                            <li v-for="step in totalSteps" :key="step" class="flex items-center">
                                <span
                                    v-if="step < currentStep"
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-600"
                                >
                                    <svg class="h-5 w-5 text-white" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd" />
                                    </svg>
                                </span>
                                <span
                                    v-else-if="step === currentStep"
                                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-indigo-600 bg-white text-sm font-semibold text-indigo-600"
                                >
                                    {{ step }}
                                </span>
                                <span
                                    v-else
                                    class="flex h-8 w-8 items-center justify-center rounded-full border-2 border-gray-300 bg-white text-sm font-medium text-gray-500"
                                >
                                    {{ step }}
                                </span>
                                <span
                                    v-if="step < totalSteps"
                                    class="ml-5 h-0.5 w-12 sm:w-20"
                                    :class="step < currentStep ? 'bg-indigo-600' : 'bg-gray-300'"
                                ></span>
                            </li>
                        </ol>
                    </nav>
                    <div class="mt-2 flex justify-center">
                        <span class="text-sm font-medium text-gray-600">
                            {{ ['Patient', 'Source', 'Tests', 'Billing'][currentStep - 1] }}
                        </span>
                    </div>
                </div>

                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <!-- STEP 1: Patient Selection -->
                        <div v-show="currentStep === 1">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Select Patient</h3>

                            <div v-if="!createNewPatient">
                                <!-- Patient Search -->
                                <div class="relative">
                                    <InputLabel value="Search patient by name or phone" />
                                    <div class="relative mt-1">
                                        <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                            </svg>
                                        </div>
                                        <input
                                            v-model="patientSearch"
                                            type="text"
                                            placeholder="Type at least 2 characters..."
                                            class="block w-full rounded-md border-gray-300 pl-10 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            @focus="showPatientResults = true"
                                            @blur="setTimeout(() => showPatientResults = false, 200)"
                                        />
                                    </div>

                                    <!-- Search Results -->
                                    <div
                                        v-if="showPatientResults && filteredPatients.length > 0"
                                        class="absolute z-10 mt-1 max-h-60 w-full overflow-auto rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5"
                                    >
                                        <button
                                            v-for="patient in filteredPatients"
                                            :key="patient.id"
                                            type="button"
                                            class="flex w-full items-center justify-between gap-3 px-4 py-2.5 text-left text-sm hover:bg-indigo-50"
                                            @mousedown.prevent="selectPatient(patient)"
                                        >
                                            <div class="min-w-0 flex-1">
                                                <span class="font-medium text-gray-900">{{ patient.name }}</span>
                                                <span class="ml-1.5 text-xs text-gray-400">({{ patient.patient_id }})</span>
                                            </div>
                                            <span v-if="patient.phone" class="shrink-0 font-mono text-xs text-gray-500">{{ patient.phone }}</span>
                                            <span v-else class="shrink-0 text-xs text-gray-300 italic">No phone</span>
                                        </button>
                                    </div>

                                    <div v-if="showPatientResults && patientSearch.length >= 2 && filteredPatients.length === 0"
                                        class="absolute z-10 mt-1 w-full rounded-md bg-white p-4 text-center text-sm text-gray-500 shadow-lg ring-1 ring-black ring-opacity-5">
                                        No patients found.
                                    </div>
                                </div>

                                <!-- Selected Patient indicator -->
                                <div v-if="form.patient_id" class="mt-3 rounded-md bg-green-50 p-3">
                                    <div class="flex items-center">
                                        <svg class="h-5 w-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                        </svg>
                                        <span class="ml-2 text-sm font-medium text-green-800">Patient selected</span>
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button
                                        type="button"
                                        @click="startNewPatient"
                                        class="text-sm font-medium text-indigo-600 hover:text-indigo-500"
                                    >
                                        + Create new patient
                                    </button>
                                </div>
                                <InputError :message="form.errors.patient_id" class="mt-2" />
                            </div>

                            <!-- Inline New Patient Form -->
                            <div v-else class="rounded-md border border-indigo-200 bg-indigo-50 p-4">
                                <div class="mb-4 flex items-center justify-between">
                                    <h4 class="text-sm font-semibold text-indigo-900">New Patient</h4>
                                    <button
                                        type="button"
                                        @click="cancelNewPatient"
                                        class="text-sm text-gray-500 hover:text-gray-700"
                                    >
                                        Cancel
                                    </button>
                                </div>
                                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="sm:col-span-2">
                                        <InputLabel value="Name *" />
                                        <TextInput
                                            v-model="form.new_patient.name"
                                            type="text"
                                            class="mt-1 block w-full"
                                            required
                                        />
                                        <InputError :message="form.errors['new_patient.name']" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel value="Age" />
                                        <TextInput
                                            v-model="form.new_patient.age"
                                            type="number"
                                            min="0"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                    <div>
                                        <InputLabel value="Age Unit" />
                                        <select
                                            v-model="form.new_patient.age_unit"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="years">Years</option>
                                            <option value="months">Months</option>
                                            <option value="days">Days</option>
                                        </select>
                                    </div>
                                    <div>
                                        <InputLabel value="Gender *" />
                                        <select
                                            v-model="form.new_patient.gender"
                                            required
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="" disabled>Select</option>
                                            <option value="male">Male</option>
                                            <option value="female">Female</option>
                                            <option value="other">Other</option>
                                        </select>
                                        <InputError :message="form.errors['new_patient.gender']" class="mt-2" />
                                    </div>
                                    <div>
                                        <InputLabel value="Phone" />
                                        <TextInput
                                            v-model="form.new_patient.phone"
                                            type="text"
                                            class="mt-1 block w-full"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- STEP 2: Source Selection -->
                        <div v-show="currentStep === 2">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Order Source</h3>

                            <div class="grid grid-cols-2 gap-3 sm:grid-cols-4">
                                <button
                                    v-for="sourceOption in [
                                        { value: 'walk_in', label: 'Walk In', icon: 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z' },
                                        { value: 'referral', label: 'Referral', icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z' },
                                        { value: 'b2b', label: 'B2B', icon: 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4' },
                                        { value: 'home_visit', label: 'Home Visit', icon: 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6' },
                                    ]"
                                    :key="sourceOption.value"
                                    type="button"
                                    @click="form.source = sourceOption.value"
                                    class="flex flex-col items-center rounded-lg border-2 p-4 text-sm font-medium transition-colors"
                                    :class="form.source === sourceOption.value
                                        ? 'border-indigo-600 bg-indigo-50 text-indigo-700'
                                        : 'border-gray-200 bg-white text-gray-700 hover:border-gray-300'"
                                >
                                    <svg class="mb-2 h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="sourceOption.icon" />
                                    </svg>
                                    {{ sourceOption.label }}
                                </button>
                            </div>
                            <InputError :message="form.errors.source" class="mt-2" />

                            <!-- Referral: Doctor Selection -->
                            <div v-if="form.source === 'referral'" class="mt-6">
                                <InputLabel value="Referring Doctor *" />
                                <select
                                    v-model="form.referring_doctor_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option :value="null" disabled>Select a doctor</option>
                                    <option v-for="doctor in referringDoctors" :key="doctor.id" :value="doctor.id">
                                        {{ doctor.name }} {{ doctor.specialization ? `(${doctor.specialization})` : '' }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.referring_doctor_id" class="mt-2" />
                            </div>

                            <!-- B2B: Client Selection -->
                            <div v-if="form.source === 'b2b'" class="mt-6">
                                <InputLabel value="B2B Client *" />
                                <select
                                    v-model="form.b2b_client_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                >
                                    <option :value="null" disabled>Select a client</option>
                                    <option v-for="client in b2bClients" :key="client.id" :value="client.id">
                                        {{ client.name }}
                                    </option>
                                </select>
                                <InputError :message="form.errors.b2b_client_id" class="mt-2" />
                            </div>

                            <!-- Home Visit: Address & Charge -->
                            <div v-if="form.source === 'home_visit'" class="mt-6 space-y-4">
                                <div>
                                    <InputLabel value="Visit Address *" />
                                    <textarea
                                        v-model="form.home_visit_address"
                                        rows="2"
                                        class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        placeholder="Enter the home visit address"
                                    ></textarea>
                                    <InputError :message="form.errors.home_visit_address" class="mt-2" />
                                </div>
                                <div class="max-w-xs">
                                    <InputLabel value="Home Visit Charge" />
                                    <TextInput
                                        v-model="form.home_visit_charge"
                                        type="number"
                                        min="0"
                                        step="0.01"
                                        class="mt-1 block w-full"
                                    />
                                </div>
                            </div>
                        </div>

                        <!-- STEP 3: Test Selection -->
                        <div v-show="currentStep === 3">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Select Tests & Packages</h3>

                            <!-- Packages -->
                            <div v-if="packages && packages.length > 0" class="mb-6">
                                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Packages</h4>
                                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-3">
                                    <button
                                        v-for="pkg in packages"
                                        :key="pkg.id"
                                        type="button"
                                        @click="togglePackage(pkg.id)"
                                        class="flex items-center justify-between rounded-lg border-2 p-3 text-left transition-colors"
                                        :class="isPackageSelected(pkg.id)
                                            ? 'border-indigo-600 bg-indigo-50'
                                            : 'border-gray-200 hover:border-gray-300'"
                                    >
                                        <div>
                                            <div class="text-sm font-medium text-gray-900">{{ pkg.name }}</div>
                                            <div class="text-xs text-gray-500">{{ pkg.tests?.length ?? 0 }} tests</div>
                                        </div>
                                        <div class="text-sm font-semibold text-gray-900">{{ formatPrice(pkg.price) }}</div>
                                    </button>
                                </div>
                            </div>

                            <!-- Tests by Category -->
                            <div>
                                <h4 class="mb-3 text-sm font-semibold uppercase tracking-wider text-gray-500">Individual Tests</h4>
                                <div class="space-y-2">
                                    <div v-for="(categoryTests, category) in tests" :key="category" class="rounded-lg border border-gray-200">
                                        <button
                                            type="button"
                                            @click="toggleCategory(category)"
                                            class="flex w-full items-center justify-between px-4 py-3 text-left"
                                        >
                                            <span class="text-sm font-semibold text-gray-900">{{ category }}</span>
                                            <div class="flex items-center gap-2">
                                                <span class="text-xs text-gray-500">{{ categoryTests.length }} tests</span>
                                                <svg
                                                    class="h-4 w-4 text-gray-400 transition-transform"
                                                    :class="{ 'rotate-180': expandedCategories[category] }"
                                                    fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                                >
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                                </svg>
                                            </div>
                                        </button>
                                        <div v-show="expandedCategories[category]" class="border-t border-gray-200 px-4 py-2">
                                            <label
                                                v-for="test in categoryTests"
                                                :key="test.id"
                                                class="flex cursor-pointer items-center justify-between py-2 hover:bg-gray-50 rounded px-2"
                                            >
                                                <div class="flex items-center">
                                                    <input
                                                        type="checkbox"
                                                        :checked="isTestSelected(test.id)"
                                                        @change="toggleTest(test.id)"
                                                        class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                    />
                                                    <div class="ml-3">
                                                        <span class="text-sm text-gray-900">{{ test.name }}</span>
                                                        <span v-if="test.code" class="ml-2 text-xs text-gray-400">{{ test.code }}</span>
                                                    </div>
                                                </div>
                                                <span class="text-sm font-medium text-gray-700">{{ formatPrice(test.price) }}</span>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Selection Summary -->
                            <div v-if="form.selected_tests.length > 0 || form.selected_packages.length > 0" class="mt-4 rounded-md bg-indigo-50 p-3">
                                <p class="text-sm text-indigo-800">
                                    <strong>{{ form.selected_tests.length }}</strong> test(s) and
                                    <strong>{{ form.selected_packages.length }}</strong> package(s) selected
                                </p>
                            </div>
                            <InputError :message="form.errors.selected_tests" class="mt-2" />

                            <!-- B2B pricing note -->
                            <div v-if="form.source === 'b2b' && form.b2b_client_id" class="mt-3 rounded-md bg-amber-50 border border-amber-200 p-3">
                                <p class="text-sm text-amber-800">
                                    <strong>Note:</strong> B2B custom pricing will be applied at checkout.
                                </p>
                            </div>
                        </div>

                        <!-- STEP 4: Billing Summary -->
                        <div v-show="currentStep === 4">
                            <h3 class="mb-4 text-lg font-semibold text-gray-900">Billing Summary</h3>

                            <!-- Selected Items -->
                            <div class="rounded-lg border border-gray-200">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Item</th>
                                            <th class="px-4 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Type</th>
                                            <th class="px-4 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 bg-white">
                                        <tr v-for="test in selectedTestItems" :key="'test-' + test.id">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ test.name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">Test</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ formatPrice(test.price) }}</td>
                                        </tr>
                                        <tr v-for="pkg in selectedPackageItems" :key="'pkg-' + pkg.id">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">{{ pkg.name }}</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">Package</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ formatPrice(pkg.price) }}</td>
                                        </tr>
                                        <tr v-if="form.source === 'home_visit' && homeVisitCharge > 0">
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-900">Home Visit Charge</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-sm text-gray-500">Extra</td>
                                            <td class="whitespace-nowrap px-4 py-3 text-right text-sm text-gray-900">{{ formatPrice(homeVisitCharge) }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Totals and Payment -->
                            <div class="mt-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
                                <!-- Discount & Payment Mode -->
                                <div class="space-y-4">
                                    <div>
                                        <InputLabel value="Discount" />
                                        <div class="mt-1 flex gap-2">
                                            <TextInput
                                                v-model="form.discount"
                                                type="number"
                                                min="0"
                                                step="0.01"
                                                class="block w-full"
                                            />
                                            <select
                                                v-model="form.discount_type"
                                                class="rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            >
                                                <option value="amount">Flat</option>
                                                <option value="percent">%</option>
                                            </select>
                                        </div>
                                        <InputError :message="form.errors.discount" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel value="Payment Mode" />
                                        <select
                                            v-model="form.payment_mode"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                        >
                                            <option value="cash">Cash</option>
                                            <option value="upi">UPI</option>
                                            <option value="card">Card</option>
                                            <option value="credit">Credit</option>
                                        </select>
                                        <InputError :message="form.errors.payment_mode" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel value="Amount Paid" />
                                        <TextInput
                                            v-model="form.amount_paid"
                                            type="number"
                                            min="0"
                                            step="0.01"
                                            class="mt-1 block w-full"
                                        />
                                        <InputError :message="form.errors.amount_paid" class="mt-2" />
                                    </div>

                                    <div>
                                        <InputLabel value="Notes" />
                                        <textarea
                                            v-model="form.notes"
                                            rows="3"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm"
                                            placeholder="Any additional notes for this order..."
                                        ></textarea>
                                        <InputError :message="form.errors.notes" class="mt-2" />
                                    </div>
                                </div>

                                <!-- Summary Card -->
                                <div class="rounded-lg bg-gray-50 p-4">
                                    <h4 class="mb-4 text-sm font-semibold uppercase tracking-wider text-gray-500">Summary</h4>
                                    <dl class="space-y-2">
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Tests Total</dt>
                                            <dd class="font-medium text-gray-900">{{ formatPrice(testsTotal) }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Packages Total</dt>
                                            <dd class="font-medium text-gray-900">{{ formatPrice(packagesTotal) }}</dd>
                                        </div>
                                        <div v-if="homeVisitCharge > 0" class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Home Visit Charge</dt>
                                            <dd class="font-medium text-gray-900">{{ formatPrice(homeVisitCharge) }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-2 text-sm">
                                            <dt class="text-gray-600">Subtotal</dt>
                                            <dd class="font-medium text-gray-900">{{ formatPrice(subtotal) }}</dd>
                                        </div>
                                        <div v-if="discountAmount > 0" class="flex justify-between text-sm text-red-600">
                                            <dt>Discount</dt>
                                            <dd class="font-medium">-{{ formatPrice(discountAmount) }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-300 pt-2 text-base">
                                            <dt class="font-semibold text-gray-900">Net Amount</dt>
                                            <dd class="font-bold text-gray-900">{{ formatPrice(netAmount) }}</dd>
                                        </div>
                                        <div class="flex justify-between text-sm">
                                            <dt class="text-gray-600">Amount Paid</dt>
                                            <dd class="font-medium text-green-600">{{ formatPrice(form.amount_paid) }}</dd>
                                        </div>
                                        <div class="flex justify-between border-t border-gray-200 pt-2 text-sm">
                                            <dt class="font-medium text-gray-900">Balance Due</dt>
                                            <dd class="font-bold" :class="balanceDue > 0 ? 'text-red-600' : 'text-green-600'">
                                                {{ formatPrice(balanceDue) }}
                                            </dd>
                                        </div>
                                    </dl>
                                </div>
                            </div>
                        </div>

                        <!-- Navigation Buttons -->
                        <div class="mt-8 flex items-center justify-between border-t border-gray-200 pt-6">
                            <button
                                v-if="currentStep > 1"
                                type="button"
                                @click="prevStep"
                                class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-semibold text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
                            >
                                <svg class="-ml-0.5 mr-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                </svg>
                                Previous
                            </button>
                            <div v-else></div>

                            <button
                                v-if="currentStep < totalSteps"
                                type="button"
                                @click="nextStep"
                                :disabled="!canProceed"
                                class="inline-flex items-center rounded-md px-4 py-2 text-sm font-semibold text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                                :class="canProceed
                                    ? 'bg-indigo-600 hover:bg-indigo-500'
                                    : 'cursor-not-allowed bg-indigo-300'"
                            >
                                Next
                                <svg class="-mr-0.5 ml-1.5 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                            </button>

                            <PrimaryButton
                                v-if="currentStep === totalSteps"
                                :disabled="form.processing"
                                @click="submit"
                            >
                                <svg v-if="form.processing" class="mr-2 h-4 w-4 animate-spin" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Place Order
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
