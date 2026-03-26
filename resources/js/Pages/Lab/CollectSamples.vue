<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed } from 'vue';
import { formatDate } from '@/Composables/useFormatDate.js';

const props = defineProps({
    order: {
        type: Object,
        required: true,
    },
});

const processing = ref({});

const pendingTests = computed(() => props.order.order_tests ?? []);
const allCollected = computed(() => pendingTests.value.length === 0);

const collectSample = (orderTest) => {
    processing.value[orderTest.ulid] = true;
    router.post(route('lab.collectSample', orderTest.ulid), {}, {
        preserveScroll: true,
        onFinish: () => {
            processing.value[orderTest.ulid] = false;
        },
    });
};

const showCollectAllConfirm = ref(false);

const confirmCollectAll = () => {
    if (pendingTests.value.length === 0) return;
    showCollectAllConfirm.value = true;
};

const performCollectAll = () => {
    const ids = pendingTests.value.map((ot) => ot.id);
    processing.value['batch'] = true;
    showCollectAllConfirm.value = false;
    router.post(
        route('lab.batchCollect', props.order.ulid),
        { order_test_ids: ids },
        {
            preserveScroll: true,
            onFinish: () => {
                processing.value['batch'] = false;
            },
        }
    );
};

const formatAge = (patient) => {
    if (!patient?.age) return '';
    const unitLabel = patient.age_unit === 'years' ? 'Y' : patient.age_unit === 'months' ? 'M' : 'D';
    const genderLabel = patient.gender === 'male' ? 'M' : patient.gender === 'female' ? 'F' : 'O';
    return `${patient.age}${unitLabel} / ${genderLabel}`;
};
</script>

<template>
    <Head :title="`Collect Samples - ${order.order_number}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-3">
                <Link
                    :href="route('lab.pendingSamples')"
                    class="inline-flex items-center gap-1 text-sm text-gray-500 transition hover:text-gray-700"
                >
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Collect Samples
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-4xl">
                <!-- Order Info Header -->
                <div class="mb-6 rounded-lg bg-white p-5 shadow-sm">
                    <div class="flex flex-wrap items-start justify-between gap-4">
                        <div>
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
                        <button
                            v-if="!allCollected"
                            @click="confirmCollectAll"
                            :disabled="processing['batch']"
                            class="inline-flex items-center gap-2 rounded-md bg-indigo-600 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ processing['batch'] ? 'Collecting...' : 'Collect All Samples' }}
                        </button>
                    </div>
                </div>

                <!-- All Collected State -->
                <div v-if="allCollected" class="rounded-lg bg-white p-12 text-center shadow-sm">
                    <svg class="mx-auto h-12 w-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <p class="mt-3 text-sm font-medium text-gray-700">All samples for this order have been collected.</p>
                    <Link
                        :href="route('lab.pendingSamples')"
                        class="mt-4 inline-flex items-center gap-1.5 rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-indigo-700"
                    >
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                        </svg>
                        Back to Pending Samples
                    </Link>
                </div>

                <!-- Pending Tests -->
                <div v-else class="space-y-3">
                    <div
                        v-for="ot in pendingTests"
                        :key="ot.id"
                        class="flex items-center justify-between rounded-lg bg-white px-5 py-4 shadow-sm"
                    >
                        <div class="flex items-center gap-3">
                            <span class="inline-flex h-9 w-9 items-center justify-center rounded-full bg-amber-50 text-amber-600">
                                <svg class="h-4.5 w-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z" />
                                </svg>
                            </span>
                            <div>
                                <p class="text-sm font-medium text-gray-900">{{ ot.test?.name }}</p>
                                <div class="mt-0.5 flex items-center gap-2">
                                    <span v-if="ot.test?.sample_type" class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-600">
                                        {{ ot.test.sample_type }}
                                    </span>
                                    <span v-if="ot.test?.category?.name" class="text-xs text-gray-400">
                                        {{ ot.test.category.name }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        <button
                            @click="collectSample(ot)"
                            :disabled="processing[ot.ulid]"
                            class="inline-flex items-center gap-1.5 rounded-md bg-emerald-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2 disabled:opacity-50"
                        >
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            {{ processing[ot.ulid] ? 'Collecting...' : 'Collect Sample' }}
                        </button>
                    </div>
                </div>
            </div>
        <!-- Collect All Confirmation -->
        <Modal :show="showCollectAllConfirm" maxWidth="sm" @close="showCollectAllConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Collect All Samples</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to collect all <strong>{{ pendingTests.length }}</strong> sample(s) at once?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showCollectAllConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performCollectAll" :disabled="processing['batch']">
                        Collect All
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
