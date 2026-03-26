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
    clients: {
        type: Object,
        default: () => ({ data: [], links: [] }),
    },
});

const showModal = ref(false);
const editingClient = ref(null);

const form = useForm({
    name: '',
    contact_person: '',
    phone: '',
    email: '',
    address: '',
    payment_terms: '',
});

const openAdd = () => {
    editingClient.value = null;
    form.reset();
    form.clearErrors();
    showModal.value = true;
};

const openEdit = (client) => {
    editingClient.value = client;
    form.name = client.name;
    form.contact_person = client.contact_person ?? '';
    form.phone = client.phone ?? '';
    form.email = client.email ?? '';
    form.address = client.address ?? '';
    form.payment_terms = client.payment_terms ?? '';
    form.clearErrors();
    showModal.value = true;
};

const save = () => {
    if (editingClient.value) {
        form.put(route('admin.b2b-clients.update', editingClient.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('admin.b2b-clients.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const showToggleConfirm = ref(false);
const togglingClient = ref(null);

const confirmToggle = (client) => {
    togglingClient.value = client;
    showToggleConfirm.value = true;
};

const performToggle = () => {
    showToggleConfirm.value = false;
    router.patch(route('admin.b2b-clients.toggleStatus', togglingClient.value.ulid), {}, {
        preserveScroll: true,
        onFinish: () => {
            togglingClient.value = null;
        },
    });
};

const showDeleteConfirm = ref(false);
const deletingClient = ref(null);
const deleteForm = useForm({});

const confirmDeleteClient = (client) => {
    deletingClient.value = client;
    showDeleteConfirm.value = true;
};

const performDelete = () => {
    deleteForm.delete(route('admin.b2b-clients.destroy', deletingClient.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteConfirm.value = false;
            deletingClient.value = null;
        },
    });
};
</script>

<template>
    <Head title="B2B Clients" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    B2B Clients
                </h2>
                <PrimaryButton @click="openAdd">+ Add Client</PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Contact Person</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Phone</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Payment Terms</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="client in clients.data" :key="client.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ client.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ client.contact_person }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ client.phone }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ client.payment_terms }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <span
                                            class="inline-flex rounded-full px-2 text-xs font-semibold leading-5"
                                            :class="client.is_active ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'"
                                        >
                                            {{ client.is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm">
                                        <div class="flex items-center gap-1">
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-indigo-600 hover:bg-indigo-50 transition-colors"
                                                title="Edit client"
                                                @click="openEdit(client)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m16.862 4.487 1.687-1.688a1.875 1.875 0 1 1 2.652 2.652L10.582 16.07a4.5 4.5 0 0 1-1.897 1.13L6 18l.8-2.685a4.5 4.5 0 0 1 1.13-1.897l8.932-8.931Zm0 0L19.5 7.125M18 14v4.75A2.25 2.25 0 0 1 15.75 21H5.25A2.25 2.25 0 0 1 3 18.75V8.25A2.25 2.25 0 0 1 5.25 6H10" /></svg>
                                            </button>
                                            <Link
                                                :href="route('admin.b2b-clients.pricing', client.ulid)"
                                                class="rounded p-1.5 text-gray-400 hover:text-blue-600 hover:bg-blue-50 transition-colors"
                                                title="Manage pricing"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 0 1 1.37.49l1.296 2.247a1.125 1.125 0 0 1-.26 1.431l-1.003.827c-.293.241-.438.613-.43.992a7.723 7.723 0 0 1 0 .255c-.008.378.137.75.43.991l1.004.827c.424.35.534.955.26 1.43l-1.298 2.247a1.125 1.125 0 0 1-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.47 6.47 0 0 1-.22.128c-.331.183-.581.495-.644.869l-.213 1.281c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.019-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 0 1-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 0 1-1.369-.49l-1.297-2.247a1.125 1.125 0 0 1 .26-1.431l1.004-.827c.292-.24.437-.613.43-.991a6.932 6.932 0 0 1 0-.255c.007-.38-.138-.751-.43-.992l-1.004-.827a1.125 1.125 0 0 1-.26-1.43l1.297-2.247a1.125 1.125 0 0 1 1.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.086.22-.128.332-.183.582-.495.644-.869l.214-1.28Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                                            </Link>
                                            <button
                                                class="rounded p-1.5 transition-colors"
                                                :class="client.is_active ? 'text-green-500 hover:text-green-700 hover:bg-green-50' : 'text-red-400 hover:text-red-600 hover:bg-red-50'"
                                                :title="client.is_active ? 'Active (click to deactivate)' : 'Inactive (click to activate)'"
                                                @click="confirmToggle(client)"
                                            >
                                                <svg class="h-5 w-5" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="6" /></svg>
                                            </button>
                                            <button
                                                class="rounded p-1.5 text-gray-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                                title="Delete client"
                                                @click="confirmDeleteClient(client)"
                                            >
                                                <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m14.74 9-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 0 1-2.244 2.077H8.084a2.25 2.25 0 0 1-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 0 0-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 0 1 3.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 0 0-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 0 0-7.5 0" /></svg>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <tr v-if="!clients.data.length">
                                    <td colspan="6" class="px-6 py-8 text-center text-gray-500">
                                        No B2B clients found.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <Pagination :links="clients.links" />
                </div>
            </div>

        <!-- Add/Edit Client Modal -->
        <Modal :show="showModal" maxWidth="2xl" @close="showModal = false">
            <div class="p-6">
                <h3 class="text-lg font-medium text-gray-900">
                    {{ editingClient ? 'Edit Client' : 'Add Client' }}
                </h3>
                <form @submit.prevent="save" class="mt-4 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Company Name" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" autofocus />
                            <InputError :message="form.errors.name" class="mt-1" />
                        </div>
                        <div>
                            <InputLabel value="Contact Person" />
                            <TextInput v-model="form.contact_person" class="mt-1 block w-full" />
                            <InputError :message="form.errors.contact_person" class="mt-1" />
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
                        <div class="col-span-2">
                            <InputLabel value="Address" />
                            <TextInput v-model="form.address" class="mt-1 block w-full" />
                            <InputError :message="form.errors.address" class="mt-1" />
                        </div>
                        <div class="col-span-2">
                            <InputLabel value="Payment Terms" />
                            <TextInput v-model="form.payment_terms" class="mt-1 block w-full" placeholder="e.g., Net 30, Prepaid, Monthly" />
                            <InputError :message="form.errors.payment_terms" class="mt-1" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3">
                        <SecondaryButton @click="showModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            {{ editingClient ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- Delete Confirmation -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete B2B Client</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingClient?.name }}</strong>? This action cannot be undone.
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
                    {{ togglingClient?.is_active ? 'Deactivate' : 'Activate' }} Client
                </h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to {{ togglingClient?.is_active ? 'deactivate' : 'activate' }} <strong>{{ togglingClient?.name }}</strong>?
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showToggleConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performToggle">
                        {{ togglingClient?.is_active ? 'Deactivate' : 'Activate' }}
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
