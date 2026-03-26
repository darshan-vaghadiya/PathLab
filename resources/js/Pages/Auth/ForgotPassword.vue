<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    status: String,
});

const form = useForm({
    email: '',
});

const submit = () => {
    form.post(route('password.email'));
};
</script>

<template>
    <GuestLayout>
        <Head title="Forgot Password" />

        <div>
            <h2 class="text-2xl font-bold tracking-tight text-gray-900">Forgot password?</h2>
            <p class="mt-1 text-sm text-gray-500">
                Enter your email and we'll send you a reset link.
            </p>
        </div>

        <div v-if="status" class="mt-4 rounded-lg bg-green-50 border border-green-200 px-4 py-3 text-sm font-medium text-green-700">
            {{ status }}
        </div>

        <form @submit.prevent="submit" class="mt-8 space-y-5">
            <div>
                <label for="email" class="block text-sm font-medium text-gray-700">Email address</label>
                <div class="relative mt-1">
                    <div class="pointer-events-none absolute inset-y-0 left-0 flex items-center pl-3">
                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" />
                        </svg>
                    </div>
                    <input
                        id="email"
                        type="email"
                        v-model="form.email"
                        required
                        autofocus
                        autocomplete="username"
                        class="block w-full rounded-lg border-gray-300 pl-10 py-2.5 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        placeholder="you@pathlab.com"
                    />
                </div>
                <InputError class="mt-1" :message="form.errors.email" />
            </div>

            <button
                type="submit"
                :disabled="form.processing"
                class="flex w-full justify-center rounded-lg bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 disabled:opacity-50 transition-colors"
            >
                {{ form.processing ? 'Sending...' : 'Send Reset Link' }}
            </button>

            <div class="text-center">
                <Link :href="route('login')" class="text-sm font-medium text-indigo-600 hover:text-indigo-500">
                    Back to login
                </Link>
            </div>
        </form>
    </GuestLayout>
</template>
