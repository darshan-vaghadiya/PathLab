<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    age: '',
    age_unit: 'years',
    gender: '',
    phone: '',
    email: '',
    address: '',
});

const submit = () => {
    form.post(route('patients.store'));
};
</script>

<template>
    <Head title="Add Patient" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link :href="route('patients.index')" class="text-gray-500 hover:text-gray-700">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                </Link>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Add Patient
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-2xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <form @submit.prevent="submit" class="p-6 space-y-6">
                        <!-- Name -->
                        <div>
                            <InputLabel for="name" value="Name *" />
                            <TextInput
                                id="name"
                                v-model="form.name"
                                type="text"
                                class="mt-1 block w-full"
                                required
                                autofocus
                            />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>

                        <!-- Age & Age Unit -->
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <InputLabel for="age" value="Age" />
                                <TextInput
                                    id="age"
                                    v-model="form.age"
                                    type="number"
                                    min="0"
                                    class="mt-1 block w-full"
                                />
                                <InputError :message="form.errors.age" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel for="age_unit" value="Age Unit" />
                                <select
                                    id="age_unit"
                                    v-model="form.age_unit"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="years">Years</option>
                                    <option value="months">Months</option>
                                    <option value="days">Days</option>
                                </select>
                                <InputError :message="form.errors.age_unit" class="mt-2" />
                            </div>
                        </div>

                        <!-- Gender -->
                        <div>
                            <InputLabel for="gender" value="Gender *" />
                            <select
                                id="gender"
                                v-model="form.gender"
                                required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="" disabled>Select gender</option>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                                <option value="other">Other</option>
                            </select>
                            <InputError :message="form.errors.gender" class="mt-2" />
                        </div>

                        <!-- Phone -->
                        <div>
                            <InputLabel for="phone" value="Phone" />
                            <TextInput
                                id="phone"
                                v-model="form.phone"
                                type="text"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.phone" class="mt-2" />
                        </div>

                        <!-- Email -->
                        <div>
                            <InputLabel for="email" value="Email" />
                            <TextInput
                                id="email"
                                v-model="form.email"
                                type="email"
                                class="mt-1 block w-full"
                            />
                            <InputError :message="form.errors.email" class="mt-2" />
                        </div>

                        <!-- Address -->
                        <div>
                            <InputLabel for="address" value="Address" />
                            <textarea
                                id="address"
                                v-model="form.address"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError :message="form.errors.address" class="mt-2" />
                        </div>

                        <!-- Submit -->
                        <div class="flex items-center justify-end gap-4">
                            <Link
                                :href="route('patients.index')"
                                class="text-sm text-gray-600 hover:text-gray-900"
                            >
                                Cancel
                            </Link>
                            <PrimaryButton :disabled="form.processing">
                                Save Patient
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
