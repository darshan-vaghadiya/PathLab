<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import DangerButton from '@/Components/DangerButton.vue';
import Pagination from '@/Components/Pagination.vue';
import { ref } from 'vue';

const currentUserId = usePage().props.auth.user.id;

const props = defineProps({
    users: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const showModal = ref(false);
const editingUser = ref(null);

const roles = [
    { value: 'admin', label: 'Admin' },
    { value: 'receptionist', label: 'Receptionist' },
    { value: 'technician', label: 'Technician' },
    { value: 'doctor', label: 'Doctor' },
];

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
    role: 'receptionist',
    phone: '',
});

const openAdd = () => {
    editingUser.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (user) => {
    editingUser.value = user;
    form.name = user.name;
    form.email = user.email;
    form.password = '';
    form.password_confirmation = '';
    form.role = user.role ?? 'receptionist';
    form.phone = user.phone ?? '';
    form.clearErrors();
    showModal.value = true;
};

const save = () => {
    if (editingUser.value) {
        form.put(route('admin.users.update', editingUser.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.users.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const showToggleConfirm = ref(false);
const togglingUser = ref(null);

const confirmToggle = (user) => {
    togglingUser.value = user;
    showToggleConfirm.value = true;
};

const performToggle = () => {
    showToggleConfirm.value = false;
    router.patch(route('admin.users.toggleStatus', togglingUser.value.ulid), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingUser.value = null;
        },
    });
};

const showDeleteConfirm = ref(false);
const deletingUser = ref(null);
const deleteForm = useForm({});

const confirmDeleteUser = (user) => {
    deletingUser.value = user;
    showDeleteConfirm.value = true;
};

const performDelete = () => {
    deleteForm.delete(route('admin.users.destroy', deletingUser.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingUser.value = null;
        },
    });
};
</script>

<template>
    <Head title="Staff Management" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Staff Management
                </h2>
                <PrimaryButton @click="openAdd">+ Add User</PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Role</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="user in users.data" :key="user.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ user.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ user.email }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500 capitalize">{{ user.role }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ user.phone }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="user.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ user.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Edit user"
                                                @click="openEdit(user)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 transition-colors"
                                                :class="user.is_active ? 'text-green-500 hover:text-green-700 hover:bg-green-50' : 'text-red-400 hover:text-red-600 hover:bg-red-50'"
                                                :title="user.is_active ? 'Active (click to deactivate)' : 'Inactive (click to activate)'"
                                                @click="confirmToggle(user)"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:text-gray-400 disabled:hover:bg-transparent"
                                                title="Delete user"
                                                :disabled="user.id === currentUserId"
                                                @click="confirmDeleteUser(user)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!users.data.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No users found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="users.links" />
                </div>
            </div>

        <!-- Add/Edit User Modal -->
        <Modal :show="showModal" maxWidth="2xl" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingUser ? 'Edit User' : 'Add User' }}
                </h3>
                <form @submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Name" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" autofocus />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Email" />
                            <TextInput v-model="form.email" type="email" class="mt-1 block w-full" />
                            <InputError :message="form.errors.email" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel :value="editingUser ? 'Password (leave blank to keep current)' : 'Password'" />
                            <TextInput v-model="form.password" type="password" class="mt-1 block w-full" />
                            <InputError :message="form.errors.password" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Confirm Password" />
                            <TextInput v-model="form.password_confirmation" type="password" class="mt-1 block w-full" />
                        </div>
                        <div>
                            <InputLabel value="Role" />
                            <select
                                v-model="form.role"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option v-for="role in roles" :key="role.value" :value="role.value">
                                    {{ role.label }}
                                </option>
                            </select>
                            <InputError :message="form.errors.role" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Phone" />
                            <TextInput v-model="form.phone" class="mt-1 block w-full" />
                            <InputError :message="form.errors.phone" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingUser ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete User</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingUser?.name }}</strong>? This action cannot be undone.
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
                    {{ togglingUser?.is_active ? 'Deactivate' : 'Activate' }} User
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to {{ togglingUser?.is_active ? 'deactivate' : 'activate' }} <strong>{{ togglingUser?.name }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showToggleConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performToggle">
                        {{ togglingUser?.is_active ? 'Deactivate' : 'Activate' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
