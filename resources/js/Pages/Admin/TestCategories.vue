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
    categories: {
        type: Array,
        default: () => [],
    },
});

const showModal = ref(false);
const editingCategory = ref(null);
const showDeleteConfirm = ref(false);
const deletingCategory = ref(null);

const form = useForm({
    name: '',
    description: '',
});

const deleteForm = useForm({});

const openAdd = () => {
    editingCategory.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (category) => {
    editingCategory.value = category;
    form.name = category.name;
    form.description = category.description ?? '';
    form.clearErrors();
    showModal.value = true;
};

const save = () => {
    if (editingCategory.value) {
        form.put(route('admin.testCategories.update', editingCategory.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.testCategories.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const confirmDelete = (category) => {
    deletingCategory.value = category;
    showDeleteConfirm.value = true;
};

const performDelete = () => {
    deleteForm.delete(route('admin.testCategories.destroy', deletingCategory.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingCategory.value = null;
        },
    });
};
</script>

<template>
    <Head title="Test Categories" />

    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Test Categories
            </h2>
        </template>

        <div class="mx-auto max-w-4xl">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">All Categories</h3>
                        <p class="mt-1 text-sm text-gray-500">Manage test categories used to organize your tests.</p>
                    </div>
                    <PrimaryButton @click="openAdd">
                        Add Category
                    </PrimaryButton>
                </div>

                <div v-if="categories.length" class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Description</th>
                                <th class="px-6 py-3 text-center text-xs font-medium uppercase tracking-wider text-gray-500">Tests</th>
                                <th class="px-6 py-3 text-right text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            <tr v-for="category in categories" :key="category.id" class="hover:bg-gray-50">
                                <td class="whitespace-nowrap px-6 py-4 font-medium text-gray-900">
                                    {{ category.name }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-500">
                                    {{ category.description || '---' }}
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-center">
                                    <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-800">
                                        {{ category.tests_count }}
                                    </span>
                                </td>
                                <td class="whitespace-nowrap px-6 py-4 text-right">
                                    <div class="flex items-center justify-end gap-1">
                                        <button
                                            class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                            title="Edit category"
                                            @click="openEdit(category)"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                        </button>
                                        <button
                                            v-if="category.tests_count === 0"
                                            class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                            title="Delete category"
                                            @click="confirmDelete(category)"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </button>
                                        <span
                                            v-else
                                            class="rounded p-1.5 text-gray-300 cursor-not-allowed"
                                            title="Cannot delete - has tests"
                                        >
                                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                        </span>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No categories</h3>
                    <p class="mt-1 text-sm text-gray-500">Get started by creating a new test category.</p>
                    <div class="mt-6">
                        <PrimaryButton @click="openAdd">
                            Add Category
                        </PrimaryButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add/Edit Category Modal -->
        <Modal :show="showModal" maxWidth="md" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ editingCategory ? 'Edit Category' : 'Add Category' }}
                </h3>
                <form @submit.prevent="save" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Category Name" />
                        <TextInput v-model="form.name" class="mt-1 block w-full" autofocus placeholder="e.g. Hematology" />
                        <InputError :message="form.errors.name" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Description (optional)" />
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Brief description of this category"
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingCategory ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation Modal -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Category</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingCategory?.name }}</strong>? This action cannot be undone.
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
