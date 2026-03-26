<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, useForm } from '@inertiajs/vue3';
import Modal from '@/Components/Modal.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref, computed } from 'vue';

const props = defineProps({
    permissions: Object,
    roles: Array,
    rolePermissions: Object,
});

const initialAssignments = {};
props.roles.forEach(role => {
    initialAssignments[role] = [...(props.rolePermissions[role] || [])];
});

const form = useForm({
    assignments: initialAssignments,
});

const activeRole = ref(props.roles[0] ?? 'admin');

const isChecked = (permId) => {
    return form.assignments[activeRole.value]?.includes(permId) ?? false;
};

const toggle = (permId) => {
    const arr = form.assignments[activeRole.value];
    const idx = arr.indexOf(permId);
    if (idx > -1) {
        arr.splice(idx, 1);
    } else {
        arr.push(permId);
    }
};

const selectAllInGroup = (perms) => {
    const arr = form.assignments[activeRole.value];
    perms.forEach(p => {
        if (!arr.includes(p.id)) arr.push(p.id);
    });
};

const clearAllInGroup = (perms) => {
    const ids = perms.map(p => p.id);
    form.assignments[activeRole.value] = form.assignments[activeRole.value].filter(id => !ids.includes(id));
};

const permCount = (role) => {
    return form.assignments[role]?.length ?? 0;
};

const totalPerms = computed(() => {
    let count = 0;
    Object.values(props.permissions).forEach(perms => count += perms.length);
    return count;
});

const showSaveConfirm = ref(false);

const confirmSave = () => {
    showSaveConfirm.value = true;
};

const performSave = () => {
    showSaveConfirm.value = false;
    form.put(route('admin.permissions.update'), {
        preserveScroll: true,
    });
};

const roleConfig = {
    admin: { color: 'purple', icon: 'M9 12.75 11.25 15 15 9.75m-3-7.036A11.959 11.959 0 0 1 3.598 6 11.99 11.99 0 0 0 3 9.749c0 5.592 3.824 10.29 9 11.623 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751h-.152c-3.196 0-6.1-1.248-8.25-3.285Z' },
    receptionist: { color: 'blue', icon: 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z' },
    technician: { color: 'amber', icon: 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104' },
    doctor: { color: 'green', icon: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
};

const groupIcons = {
    'General': 'M2.25 12l8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25',
    'Front Desk': 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z',
    'Lab': 'M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104',
    'Doctor': 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z',
    'Reports': 'M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z',
    'Administration': 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z',
};
</script>

<template>
    <Head title="Permissions" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">Role Permissions</h2>
                <PrimaryButton @click="confirmSave" :disabled="form.processing">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-5xl">
            <!-- Role Tabs -->
            <div class="mb-6 flex gap-3">
                <button
                    v-for="role in roles"
                    :key="role"
                    @click="activeRole = role"
                    class="flex items-center gap-2 rounded-lg border-2 px-4 py-3 text-sm font-medium transition-all"
                    :class="activeRole === role
                        ? `border-${roleConfig[role]?.color || 'gray'}-500 bg-${roleConfig[role]?.color || 'gray'}-50 text-${roleConfig[role]?.color || 'gray'}-700 shadow-sm`
                        : 'border-gray-200 bg-white text-gray-600 hover:border-gray-300 hover:bg-gray-50'"
                >
                    <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" :d="roleConfig[role]?.icon || ''" />
                    </svg>
                    <span class="capitalize">{{ role }}</span>
                    <span
                        class="ml-1 inline-flex h-5 min-w-[20px] items-center justify-center rounded-full text-xs font-bold"
                        :class="activeRole === role ? 'bg-white/80 text-gray-700' : 'bg-gray-100 text-gray-500'"
                    >
                        {{ permCount(role) }}
                    </span>
                </button>
            </div>

            <!-- Permission Groups -->
            <div class="space-y-4">
                <div
                    v-for="(perms, group) in permissions"
                    :key="group"
                    class="overflow-hidden rounded-lg bg-white shadow-sm"
                >
                    <!-- Group Header -->
                    <div class="flex items-center justify-between border-b border-gray-100 bg-gray-50/50 px-5 py-3">
                        <div class="flex items-center gap-2">
                            <svg class="h-4 w-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" :d="groupIcons[group] || ''" />
                            </svg>
                            <span class="text-sm font-semibold text-gray-700">{{ group }}</span>
                            <span class="text-xs text-gray-400">({{ perms.length }})</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <button
                                type="button"
                                @click="selectAllInGroup(perms)"
                                class="rounded px-2 py-0.5 text-xs font-medium text-indigo-600 hover:bg-indigo-50 transition-colors"
                            >
                                Enable all
                            </button>
                            <button
                                type="button"
                                @click="clearAllInGroup(perms)"
                                class="rounded px-2 py-0.5 text-xs font-medium text-red-500 hover:bg-red-50 transition-colors"
                            >
                                Disable all
                            </button>
                        </div>
                    </div>

                    <!-- Permission Items -->
                    <div class="divide-y divide-gray-50">
                        <label
                            v-for="perm in perms"
                            :key="perm.id"
                            class="flex cursor-pointer items-center justify-between px-5 py-3 transition-colors hover:bg-gray-50/50"
                        >
                            <div>
                                <span class="text-sm font-medium text-gray-900">{{ perm.name }}</span>
                                <p v-if="perm.description" class="mt-0.5 text-xs text-gray-400">{{ perm.description }}</p>
                            </div>
                            <!-- Toggle Switch -->
                            <button
                                type="button"
                                @click="toggle(perm.id)"
                                class="relative inline-flex h-6 w-11 shrink-0 cursor-pointer rounded-full border-2 border-transparent transition-colors duration-200 ease-in-out focus:outline-none focus:ring-2 focus:ring-indigo-600 focus:ring-offset-2"
                                :class="isChecked(perm.id) ? 'bg-indigo-600' : 'bg-gray-200'"
                                role="switch"
                                :aria-checked="isChecked(perm.id)"
                            >
                                <span
                                    class="pointer-events-none inline-block h-5 w-5 transform rounded-full bg-white shadow ring-0 transition duration-200 ease-in-out"
                                    :class="isChecked(perm.id) ? 'translate-x-5' : 'translate-x-0'"
                                />
                            </button>
                        </label>
                    </div>
                </div>
            </div>

            <!-- Bottom Save -->
            <div class="mt-6 flex justify-end">
                <PrimaryButton @click="confirmSave" :disabled="form.processing" class="px-8">
                    {{ form.processing ? 'Saving...' : 'Save Changes' }}
                </PrimaryButton>
            </div>
        </div>
        <!-- Save Permissions Confirmation -->
        <Modal :show="showSaveConfirm" maxWidth="sm" @close="showSaveConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Save Permission Changes</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to save permission changes for all roles? This will update access controls immediately.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showSaveConfirm = false">Cancel</SecondaryButton>
                    <PrimaryButton @click="performSave" :disabled="form.processing">
                        Save Changes
                    </PrimaryButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
