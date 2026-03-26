<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import SecondaryButton from '@/Components/SecondaryButton.vue';
import { ref } from 'vue';

const props = defineProps({
    client: {
        type: Object,
        required: true,
    },
    tests: {
        type: Array,
        default: () => [],
    },
    packages: {
        type: Array,
        default: () => [],
    },
});

// Build the form with custom prices keyed by test id
const prices = {};
props.tests.forEach(test => {
    prices[test.id] = test.customPrice ?? '';
});

const packagePrices = {};
props.packages.forEach(pkg => {
    packagePrices[pkg.id] = pkg.customPrice ?? '';
});

const form = useForm({
    prices: prices,
    packagePrices: packagePrices,
});

const save = () => {
    // Transform prices into arrays for the controller
    const data = {
        prices: Object.entries(form.prices)
            .filter(([, price]) => price !== '' && price !== null)
            .map(([testId, price]) => ({
                test_id: testId,
                price: price,
            })),
        package_prices: Object.entries(form.packagePrices)
            .filter(([, price]) => price !== '' && price !== null)
            .map(([packageId, price]) => ({
                package_id: packageId,
                price: price,
            })),
    };

    router.put(route('admin.b2b-clients.updatePricing', props.client.ulid), data, {
        preserveScroll: true,
    });
};

const saved = ref(false);
</script>

<template>
    <Head :title="`B2B Pricing - ${client.name}`" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex items-center justify-between">
                <div>
                    <Link :href="route('admin.b2b-clients.index')" class="text-sm text-indigo-600 hover:text-indigo-800">
                        &larr; Back to B2B Clients
                    </Link>
                    <h2 class="mt-1 text-xl font-semibold leading-tight text-gray-800">
                        Custom Pricing: {{ client.name }}
                    </h2>
                </div>
                <PrimaryButton @click="save" :disabled="form.processing">
                    Save All Prices
                </PrimaryButton>
            </div>
        </template>

        <div class="mx-auto max-w-7xl">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Test Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Standard Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Custom Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="test in tests" :key="test.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ test.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ test.price }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <input
                                            v-model="form.prices[test.id]"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :placeholder="test.price"
                                            class="w-32 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <template v-if="form.prices[test.id] && Number(form.prices[test.id]) < Number(test.price)">
                                            <span class="text-green-600">
                                                -{{ (((Number(test.price) - Number(form.prices[test.id])) / Number(test.price)) * 100).toFixed(1) }}%
                                            </span>
                                        </template>
                                        <template v-else-if="form.prices[test.id] && Number(form.prices[test.id]) > Number(test.price)">
                                            <span class="text-red-600">
                                                +{{ (((Number(form.prices[test.id]) - Number(test.price)) / Number(test.price)) * 100).toFixed(1) }}%
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span class="text-gray-400">--</span>
                                        </template>
                                    </td>
                                </tr>
                                <tr v-if="!tests.length">
                                    <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                        No tests available.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Bottom save button for tests -->
                    <div v-if="tests.length" class="border-t px-6 py-4">
                        <div class="flex justify-end">
                            <PrimaryButton @click="save" :disabled="form.processing">
                                Save All Prices
                            </PrimaryButton>
                        </div>
                    </div>
                </div>

                <!-- Package Pricing Section -->
                <div v-if="packages.length" class="mt-6 overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="border-b border-gray-200 px-6 py-4">
                        <h3 class="text-lg font-semibold text-gray-900">Package Pricing</h3>
                        <p class="mt-0.5 text-sm text-gray-500">Set custom prices for test packages</p>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Package Name</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Standard Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Custom Price</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider text-gray-500">Discount</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="pkg in packages" :key="pkg.id">
                                    <td class="whitespace-nowrap px-6 py-4 text-sm font-medium text-gray-900">{{ pkg.name }}</td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">{{ pkg.price }}</td>
                                    <td class="whitespace-nowrap px-6 py-4">
                                        <input
                                            v-model="form.packagePrices[pkg.id]"
                                            type="number"
                                            step="0.01"
                                            min="0"
                                            :placeholder="pkg.price"
                                            class="w-32 rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                        />
                                    </td>
                                    <td class="whitespace-nowrap px-6 py-4 text-sm text-gray-500">
                                        <template v-if="form.packagePrices[pkg.id] && Number(form.packagePrices[pkg.id]) < Number(pkg.price)">
                                            <span class="text-green-600">
                                                -{{ (((Number(pkg.price) - Number(form.packagePrices[pkg.id])) / Number(pkg.price)) * 100).toFixed(1) }}%
                                            </span>
                                        </template>
                                        <template v-else-if="form.packagePrices[pkg.id] && Number(form.packagePrices[pkg.id]) > Number(pkg.price)">
                                            <span class="text-red-600">
                                                +{{ (((Number(form.packagePrices[pkg.id]) - Number(pkg.price)) / Number(pkg.price)) * 100).toFixed(1) }}%
                                            </span>
                                        </template>
                                        <template v-else>
                                            <span class="text-gray-400">--</span>
                                        </template>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <div class="border-t px-6 py-4">
                        <div class="flex justify-end">
                            <PrimaryButton @click="save" :disabled="form.processing">
                                Save All Prices
                            </PrimaryButton>
                        </div>
                    </div>
                </div>
            </div>
    </AuthenticatedLayout>
</template>
