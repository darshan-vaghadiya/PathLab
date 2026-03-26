<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import { ref } from 'vue';

const props = defineProps({
    packages: {
        type: Array,
        default: () => [],
    },
    allTests: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const showDeleteModal = ref(false);
const editingPackage = ref(null);
const deletingPackage = ref(null);

const form = useForm({
    name: '',
    price: '',
    test_ids: [],
});

const openAdd = () => {
    editingPackage.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (pkg) => {
    editingPackage.value = pkg;
    form.name = pkg.name;
    form.price = pkg.price;
    form.test_ids = pkg.tests ? pkg.tests.map(t => t.id) : [];
    form.clearErrors();
    showModal.value = true;
};

const save = () => {
    if (editingPackage.value) {
        form.put(route('admin.packages.update', editingPackage.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.packages.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = (pkg) => {
    deletingPackage.value = pkg;
    showDeleteModal.value = true;
};

const deletePackage = () => {
    useForm({}).delete(route('admin.packages.destroy', deletingPackage.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteModal.value = false;
            deletingPackage.value = null;
        },
    });
};

const toggleTest = (testId) => {
    const index = form.test_ids.indexOf(testId);
    if (index > -1) {
        form.test_ids.splice(index, 1);
    } else {
        form.test_ids.push(testId);
    }
};
</script>

<template>
    <Head title="Package Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Package Management
                </h2>
                <PrimaryButton @click="openAdd">+ Add Package</PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Package Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Tests Included</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="pkg in packages" :key="pkg.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ pkg.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ pkg.price }}</td>
                                    <td class="px-6 py-4 text-sm text-gray-500">
                                        <div class="flex flex-wrap gap-1">
                                            <span
                                                v-for="test in pkg.tests"
                                                :key="test.id"
                                                class="inline-flex rounded-full bg-blue-100 px-2 py-0.5 text-xs font-medium text-blue-800"
                                            >
                                                {{ test.name }}
                                            </span>
                                            <span v-if="!pkg.tests?.length" class="text-gray-400">No tests</span>
                                        </div>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Edit package"
                                                @click="openEdit(pkg)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Delete package"
                                                @click="confirmDelete(pkg)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!packages.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No packages found. Create one to get started.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        <!-- Add/Edit Package Modal -->
        <Modal :show="showModal" maxWidth="2xl" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingPackage ? 'Edit Package' : 'Add Package' }}
                </h3>
                <form @submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Package Name" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" autofocus />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Price" />
                            <TextInput v-model="form.price" type="number" step="0.01" class="mt-1 block w-full" />
                            <InputError :message="form.errors.price" class="mt-1" />
                        </div>
                    </div>

                    <div>
                        <InputLabel value="Select Tests" />
                        <InputError :message="form.errors.test_ids" class="mt-1" />
                        <div class="mt-2 max-h-60 overflow-y-auto rounded-md border border-gray-200 p-3">
                            <label
                                v-for="test in allTests"
                                :key="test.id"
                                class="flex cursor-pointer items-center gap-2 rounded px-2 py-1.5 hover:bg-gray-50"
                            >
                                <input
                                    type="checkbox"
                                    :checked="form.test_ids.includes(test.id)"
                                    @change="toggleTest(test.id)"
                                    class="rounded border-gray-300 text-indigo-600 shadow-sm focus:ring-indigo-500"
                                />
                                <span class="text-sm text-gray-700">{{ test.name }}</span>
                                <span class="text-xs text-gray-400">- {{ test.price }}</span>
                            </label>
                            <p v-if="!allTests.length" class="py-2 text-center text-sm text-gray-400">No tests available.</p>
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingPackage ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteModal" maxWidth="md" @close="showDeleteModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">Delete Package</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingPackage?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteModal = false">Cancel</SecondaryButton>
                    <DangerButton @click="deletePackage">Delete Package</DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
