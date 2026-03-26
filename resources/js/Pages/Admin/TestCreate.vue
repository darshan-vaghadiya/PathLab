<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';
import InputLabel from '@/Components/InputLabel.vue';
import TextInput from '@/Components/TextInput.vue';
import InputError from '@/Components/InputError.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';

const props = defineProps({
    categories: Array,
});

const form = useForm({
    test_category_id: '',
    name: '',
    short_name: '',
    report_type: 'parametric',
    price: '',
    method: '',
    sample_type: '',
    description: '',
});

const submit = () => {
    form.post(route('admin.tests.store'));
};
</script>

<template>
    <Head title="Create Test" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center gap-4">
                <Link
                    :href="route('admin.tests.index')"
                    class="inline-flex items-center text-sm text-gray-500 hover:text-gray-700"
                >
                    <svg class="mr-1 h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                    </svg>
                    Back to Tests
                </Link>
                <span class="text-gray-300">|</span>
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Create New Test
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-3xl">
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="border-b border-gray-200 px-6 py-4">
                    <h3 class="text-lg font-semibold text-gray-900">Test Details</h3>
                    <p class="mt-1 text-sm text-gray-500">Create a new test. You can add parameters and ranges after creation.</p>
                </div>

                <form @submit.prevent="submit" class="p-6 space-y-6">
                    <!-- Name row -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Test Name *" />
                            <TextInput v-model="form.name" class="mt-1 block w-full" placeholder="e.g. Complete Blood Count" />
                            <InputError :message="form.errors.name" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Short Name" />
                            <TextInput v-model="form.short_name" class="mt-1 block w-full" placeholder="e.g. CBC" />
                            <InputError :message="form.errors.short_name" class="mt-2" />
                        </div>
                    </div>

                    <!-- Category + Report Type -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                        <div>
                            <InputLabel value="Category *" />
                            <select
                                v-model="form.test_category_id"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="">-- Select Category --</option>
                                <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                            </select>
                            <InputError :message="form.errors.test_category_id" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Report Type *" />
                            <select
                                v-model="form.report_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="parametric">Parametric</option>
                                <option value="text">Text</option>
                                <option value="mixed">Mixed</option>
                            </select>
                            <InputError :message="form.errors.report_type" class="mt-2" />
                        </div>
                    </div>

                    <!-- Price + Sample Type -->
                    <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                        <div>
                            <InputLabel value="Price *" />
                            <TextInput v-model="form.price" type="number" step="0.01" min="0" class="mt-1 block w-full" placeholder="0.00" />
                            <InputError :message="form.errors.price" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Sample Type" />
                            <TextInput v-model="form.sample_type" class="mt-1 block w-full" placeholder="e.g. Blood, Urine" />
                            <InputError :message="form.errors.sample_type" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Method" />
                            <TextInput v-model="form.method" class="mt-1 block w-full" placeholder="e.g. ELISA, PCR" />
                            <InputError :message="form.errors.method" class="mt-2" />
                        </div>
                    </div>

                    <!-- Description -->
                    <div>
                        <InputLabel value="Description" />
                        <textarea
                            v-model="form.description"
                            rows="3"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            placeholder="Optional description for this test"
                        ></textarea>
                        <InputError :message="form.errors.description" class="mt-2" />
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 border-t border-gray-200 pt-6">
                        <Link :href="route('admin.tests.index')">
                            <SecondaryButton type="button">Cancel</SecondaryButton>
                        </Link>
                        <PrimaryButton type="submit" :disabled="form.processing">
                            Create Test
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
