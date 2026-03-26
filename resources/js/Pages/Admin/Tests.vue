<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, router, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref, watch } from 'vue';

const props = defineProps({
    tests: Object,
    categories: Array,
    filters: Object,
});

const categoryFilter = ref(props.filters?.category ?? '');
const searchFilter = ref(props.filters?.search ?? '');
let searchTimeout = null;

const applyFilters = () => {
    router.get(route('admin.tests.index'), {
        category: categoryFilter.value || undefined,
        search: searchFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(categoryFilter, () => {
    applyFilters();
});

watch(searchFilter, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        applyFilters();
    }, 300);
});

const showToggleConfirm = ref(false);
const togglingTest = ref(null);
const toggleForm = useForm({});

const confirmToggle = (test) => {
    togglingTest.value = test;
    showToggleConfirm.value = true;
};

const performToggle = () => {
    toggleForm.patch(route('admin.tests.toggleStatus', togglingTest.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showToggleConfirm.value = false;
            togglingTest.value = null;
        },
    });
};

const showDeleteConfirm = ref(false);
const deletingTest = ref(null);
const deleteForm = useForm({});

const confirmDelete = (test) => {
    deletingTest.value = test;
    showDeleteConfirm.value = true;
};

const performDelete = () => {
    deleteForm.delete(route('admin.tests.destroy', deletingTest.value.ulid), {
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingTest.value = null;
        },
    });
};

const reportTypeBadge = (type) => {
    const map = {
        parametric: 'bg-blue-100 text-blue-800',
        text: 'bg-purple-100 text-purple-800',
        mixed: 'bg-amber-100 text-amber-800',
    };
    return map[type] || 'bg-gray-100 text-gray-800';
};
</script>

<template>
    <Head title="Tests" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Tests
            </h2>
        </template>

        <div class="mx-auto max-w-7xl">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <!-- Toolbar -->
                <div class="flex flex-col gap-4 border-b border-gray-200 px-6 py-4 sm:flex-row sm:items-center sm:justify-between">
                    <div class="flex flex-1 items-center gap-4">
                        <!-- Search -->
                        <div class="relative flex-1 max-w-xs">
                            <input
                                v-model="searchFilter"
                                type="text"
                                placeholder="Search tests..."
                                class="block w-full rounded-md border-gray-300 pl-10 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            />
                            <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                                <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-5.197-5.197m0 0A7.5 7.5 0 1 0 5.196 5.196a7.5 7.5 0 0 0 10.607 10.607Z" />
                                </svg>
                            </div>
                        </div>

                        <!-- Category filter -->
                        <select
                            v-model="categoryFilter"
                            class="rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">All Categories</option>
                            <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                        </select>
                    </div>

                    <Link :href="route('admin.tests.create')">
                        <PrimaryButton>
                            Add New Test
                        </PrimaryButton>
                    </Link>
                </div>

                <!-- Table -->
                <div v-if="tests.data.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Short Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Category</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Report Type</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Sample</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Params</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="test in tests.data" :key="test.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                    {{ test.name }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                                    {{ test.short_name || '---' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                                    {{ test.category?.name || '---' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium capitalize"
                                        :class="reportTypeBadge(test.report_type)"
                                    >
                                        {{ test.report_type || 'parametric' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right text-gray-700">
                                    {{ test.price ? '\u20B9' + test.price : '---' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-gray-500">
                                    {{ test.sample_type || '---' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-semibold"
                                        :class="test.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                    >
                                        {{ test.is_active ? 'Active' : 'Inactive' }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center text-gray-500">
                                    {{ test.parameters_count }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <Link
                                            :href="route('admin.tests.edit', test.ulid)"
                                            class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                            title="Edit test"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </Link>
                                        <button
                                            class="rounded p-1.5 transition-colors"
                                            :class="test.is_active ? 'text-green-500 hover:text-green-700 hover:bg-green-50' : 'text-red-400 hover:text-red-600 hover:bg-red-50'"
                                            :title="test.is_active ? 'Active (click to deactivate)' : 'Inactive (click to activate)'"
                                            @click="confirmToggle(test)"
                                        >
                                            <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6" /></svg>
                                        </button>
                                        <button
                                            class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete test"
                                            @click="confirmDelete(test)"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Empty state -->
                <div v-else class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875S10.5 3.089 10.5 4.125c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.491 48.491 0 0 1-4.163-.3c.186 1.613.63 3.168 1.304 4.62a23.71 23.71 0 0 0 4.476 6.633c.168.18.349.346.541.498a23.76 23.76 0 0 0 4.476-6.633 20.56 20.56 0 0 0 1.304-4.62 48.479 48.479 0 0 1-4.163.3.64.64 0 0 1-.657-.643Z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No tests found</h3>
                    <p class="mt-1 text-sm text-gray-500">
                        {{ filters?.category || filters?.search ? 'Try adjusting your filters.' : 'Get started by creating a new test.' }}
                    </p>
                    <div v-if="!filters?.category && !filters?.search" class="mt-6">
                        <Link :href="route('admin.tests.create')">
                            <PrimaryButton>Add New Test</PrimaryButton>
                        </Link>
                    </div>
                </div>

                <Pagination :links="tests.links" />
            </div>
        </div>
        <!-- Toggle Status Confirmation -->
        <Modal :show="showToggleConfirm" maxWidth="sm" @close="showToggleConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ togglingTest?.is_active ? 'Deactivate' : 'Activate' }} Test
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to {{ togglingTest?.is_active ? 'deactivate' : 'activate' }} <strong>{{ togglingTest?.name }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showToggleConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performToggle" :disabled="toggleForm.processing">
                        {{ togglingTest?.is_active ? 'Deactivate' : 'Activate' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>

        <!-- Delete Test Confirmation -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Test</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingTest?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDelete" :disabled="deleteForm.processing">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
