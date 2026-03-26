<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const props = defineProps({
    doctors: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const showModal = ref(false);
const editingDoctor = ref(null);

const form = useForm({
    name: '',
    phone: '',
    email: '',
    clinic_name: '',
    address: '',
    commission_type: 'percentage',
    commission_value: '',
});

const openAdd = () => {
    editingDoctor.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (doctor) => {
    editingDoctor.value = doctor;
    form.name = doctor.name;
    form.phone = doctor.phone ?? '';
    form.email = doctor.email ?? '';
    form.clinic_name = doctor.clinic_name ?? '';
    form.address = doctor.address ?? '';
    form.commission_type = doctor.commission_type ?? 'percentage';
    form.commission_value = doctor.commission_value ?? '';
    form.clearErrors();
    showModal.value = true;
};

const save = () => {
    if (editingDoctor.value) {
        form.put(route('admin.referring-doctors.update', editingDoctor.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.referring-doctors.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const showToggleConfirm = ref(false);
const togglingDoctor = ref(null);

const confirmToggle = (doctor) => {
    togglingDoctor.value = doctor;
    showToggleConfirm.value = true;
};

const performToggle = () => {
    showToggleConfirm.value = false;
    router.patch(route('admin.referring-doctors.toggleStatus', togglingDoctor.value.ulid), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingDoctor.value = null;
        },
    });
};

const showDeleteConfirm = ref(false);
const deletingDoctor = ref(null);
const deleteForm = useForm({});

const confirmDeleteDoctor = (doctor) => {
    deletingDoctor.value = doctor;
    showDeleteConfirm.value = true;
};

const performDelete = () => {
    deleteForm.delete(route('admin.referring-doctors.destroy', deletingDoctor.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingDoctor.value = null;
        },
    });
};
</script>

<template>
    <Head title="Referring Doctors" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Referring Doctors
                </h2>
                <PrimaryButton @click="openAdd">+ Add Doctor</PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Clinic</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Commission Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Commission Value</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="doctor in doctors.data" :key="doctor.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ doctor.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ doctor.phone }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ doctor.clinic_name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 capitalize">{{ doctor.commission_type }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        {{ doctor.commission_value }}{{ doctor.commission_type === 'percentage' ? '%' : '' }}
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="doctor.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ doctor.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Edit doctor"
                                                @click="openEdit(doctor)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 transition-colors"
                                                :class="doctor.is_active ? 'text-green-500 hover:text-green-700 hover:bg-green-50' : 'text-red-400 hover:text-red-600 hover:bg-red-50'"
                                                :title="doctor.is_active ? 'Active (click to deactivate)' : 'Inactive (click to activate)'"
                                                @click="confirmToggle(doctor)"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Delete doctor"
                                                @click="confirmDeleteDoctor(doctor)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!doctors.data.length">
                                    <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                        No referring doctors found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="doctors.links" />
                </div>
            </div>

        <!-- Add/Edit Doctor Modal -->
        <Modal :show="showModal" maxWidth="2xl" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingDoctor ? 'Edit Doctor' : 'Add Doctor' }}
                </h3>
                <form @submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Name" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" autofocus />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Phone" />
                            <TextInput v-model="form.phone" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Email" />
                            <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Clinic Name" />
                            <TextInput v-model="form.clinic_name" class="mt-1 block w-full" />
                            <InputError :message="form.errors.clinic_name" class="mt-1" />
                        </div>
                        <div class="col-span-2">
                            <InputLabel value="Address" />
                            <TextInput v-model="form.address" class="mt-1 block w-full" />
                            <InputError :message="form.errors.address" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Commission Type" />
                            <select
                                v-model="form.commission_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="percentage">Percentage</option>
                                <option value="fixed">Fixed</option>
                            </select>
                            <InputError :message="form.errors.commission_type" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Commission Value" />
                            <TextInput v-model="form.commission_value" type="number" step="0.01" class="mt-1 block w-full" />
                            <InputError :message="form.errors.commission_value" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingDoctor ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Referring Doctor</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingDoctor?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDelete" :disabled="deleteForm.processing">Delete</DangerButton>
                </div>
            </div>
        </Modal>

        <!-- Toggle Status Confirmation -->
        <Modal :show="showToggleConfirm" maxWidth="sm" @close="showToggleConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ togglingDoctor?.is_active ? 'Deactivate' : 'Activate' }} Doctor
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to {{ togglingDoctor?.is_active ? 'deactivate' : 'activate' }} <strong>{{ togglingDoctor?.name }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showToggleConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performToggle">
                        {{ togglingDoctor?.is_active ? 'Deactivate' : 'Activate' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
