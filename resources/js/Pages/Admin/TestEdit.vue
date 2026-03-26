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
import { ref, computed } from 'vue';

const props = defineProps({
    test: Object,
    categories: Array,
    allTests: Array,
});

// ── Test Details Form ────────────────────────────────────────
const showTestDetails = ref(true);

const testForm = useForm({
    test_category_id: props.test.test_category_id ?? '',
    name: props.test.name ?? '',
    short_name: props.test.short_name ?? '',
    report_type: props.test.report_type ?? 'parametric',
    price: props.test.price ?? '',
    method: props.test.method ?? '',
    sample_type: props.test.sample_type ?? '',
    description: props.test.description ?? '',
});

const saveTestDetails = () => {
    testForm.put(route('admin.tests.update', props.test.ulid), {
        preserveScroll: true,
    });
};

// ── Parameters ────────────────────────────────────────
const sortedParameters = computed(() => {
    if (!props.test.parameters) return [];
    return [...props.test.parameters].sort((a, b) => (a.sort_order ?? 0) - (b.sort_order ?? 0));
});

const showParameterModal = ref(false);
const editingParameter = ref(null);

const parameterForm = useForm({
    name: '',
    field_type: 'numeric',
    unit: '',
    options: '',
    sort_order: 0,
});

const openAddParameter = () => {
    editingParameter.value = null;
    parameterForm.reset();
    parameterForm.clearErrors();
    parameterForm.sort_order = (props.test.parameters?.length ?? 0) + 1;
    showParameterModal.value = true;
};

const openEditParameter = (parameter) => {
    editingParameter.value = parameter;
    parameterForm.name = parameter.name;
    parameterForm.field_type = parameter.field_type;
    parameterForm.unit = parameter.unit ?? '';
    parameterForm.options = Array.isArray(parameter.options) ? parameter.options.join(', ') : '';
    parameterForm.sort_order = parameter.sort_order ?? 0;
    parameterForm.clearErrors();
    showParameterModal.value = true;
};

const saveParameter = () => {
    const data = {
        ...parameterForm.data(),
        options: parameterForm.field_type === 'options'
            ? parameterForm.options.split(',').map(o => o.trim()).filter(Boolean)
            : null,
    };

    if (editingParameter.value) {
        router.put(route('admin.tests.updateParameter', editingParameter.value.ulid), data, {
            preserveScroll: true,
            onSuccess: () => {
                showParameterModal.value = false;
                parameterForm.reset();
            },
            onError: (errors) => {
                parameterForm.clearErrors();
                Object.keys(errors).forEach(k => parameterForm.setError(k, errors[k]));
            },
        });
    } else {
        router.post(route('admin.tests.storeParameter', props.test.ulid), data, {
            preserveScroll: true,
            onSuccess: () => {
                showParameterModal.value = false;
                parameterForm.reset();
            },
            onError: (errors) => {
                parameterForm.clearErrors();
                Object.keys(errors).forEach(k => parameterForm.setError(k, errors[k]));
            },
        });
    }
};

const showDeleteParamConfirm = ref(false);
const deletingParam = ref(null);
const deleteParamForm = useForm({});

const confirmDeleteParam = (parameter) => {
    deletingParam.value = parameter;
    showDeleteParamConfirm.value = true;
};

const performDeleteParam = () => {
    deleteParamForm.delete(route('admin.tests.deleteParameter', deletingParam.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteParamConfirm.value = false;
            deletingParam.value = null;
        },
    });
};

// ── Reorder Parameters ────────────────────────────────────────
const reorderParameter = (paramId, direction) => {
    router.post(route('admin.tests.reorderParameter', paramId), { direction }, {
        preserveScroll: true,
    });
};

// ── Copy Parameters ────────────────────────────────────────
const showCopyModal = ref(false);
const copyForm = useForm({
    source_test_id: '',
});

const openCopyModal = () => {
    copyForm.reset();
    copyForm.clearErrors();
    showCopyModal.value = true;
};

const submitCopyParameters = () => {
    copyForm.post(route('admin.tests.copyParameters', props.test.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showCopyModal.value = false;
            copyForm.reset();
        },
    });
};

// ── Ranges ────────────────────────────────────────
const showRangeModal = ref(false);
const editingRange = ref(null);
const rangeParameterId = ref(null);

const rangeForm = useForm({
    range_group: '',
    age_min: '',
    age_max: '',
    min_value: '',
    max_value: '',
    text_value: '',
});

const rangeGroupSuggestions = ['All', 'Male', 'Female', 'Child', 'Infant', 'Pregnant', 'Newborn'];

const openAddRange = (parameter) => {
    editingRange.value = null;
    rangeParameterId.value = parameter.ulid;
    rangeForm.reset();
    rangeForm.clearErrors();
    showRangeModal.value = true;
};

const openEditRange = (range, parameterId) => {
    editingRange.value = range;
    rangeParameterId.value = parameterId;
    rangeForm.range_group = range.range_group ?? '';
    rangeForm.age_min = range.age_min ?? '';
    rangeForm.age_max = range.age_max ?? '';
    rangeForm.min_value = range.min_value ?? '';
    rangeForm.max_value = range.max_value ?? '';
    rangeForm.text_value = range.text_value ?? '';
    rangeForm.clearErrors();
    showRangeModal.value = true;
};

const saveRange = () => {
    if (editingRange.value) {
        rangeForm.put(route('admin.tests.updateRange', editingRange.value.ulid), {
            preserveScroll: true,
            onSuccess: () => {
                showRangeModal.value = false;
                rangeForm.reset();
            },
        });
    } else {
        rangeForm.post(route('admin.tests.storeRange', rangeParameterId.value), {
            preserveScroll: true,
            onSuccess: () => {
                showRangeModal.value = false;
                rangeForm.reset();
            },
        });
    }
};

const showDeleteRangeConfirm = ref(false);
const deletingRange = ref(null);
const deleteRangeForm = useForm({});

const confirmDeleteRange = (range) => {
    deletingRange.value = range;
    showDeleteRangeConfirm.value = true;
};

const performDeleteRange = () => {
    deleteRangeForm.delete(route('admin.tests.deleteRange', deletingRange.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteRangeConfirm.value = false;
            deletingRange.value = null;
        },
    });
};

// ── Templates ────────────────────────────────────────
const templateForms = ref({});

const getTemplateForm = (paramId) => {
    if (!templateForms.value[paramId]) {
        templateForms.value[paramId] = { name: '', content: '', errors: {}, processing: false };
    }
    return templateForms.value[paramId];
};

const addTemplate = (parameter) => {
    const form = getTemplateForm(parameter.id);
    form.errors = {};
    form.processing = true;

    router.post(route('admin.tests.storeTemplate', parameter.ulid), {
        name: form.name,
        content: form.content,
    }, {
        preserveScroll: true,
        onSuccess: () => {
            form.name = '';
            form.content = '';
            form.errors = {};
            form.processing = false;
        },
        onError: (errors) => {
            form.errors = errors;
            form.processing = false;
        },
    });
};

const showDeleteTemplateConfirm = ref(false);
const deletingTemplate = ref(null);
const deleteTemplateForm = useForm({});

const confirmDeleteTemplate = (template) => {
    deletingTemplate.value = template;
    showDeleteTemplateConfirm.value = true;
};

const performDeleteTemplate = () => {
    deleteTemplateForm.delete(route('admin.tests.deleteTemplate', deletingTemplate.value.ulid), {
        preserveScroll: true,
        onSuccess: () => {
            showDeleteTemplateConfirm.value = false;
            deletingTemplate.value = null;
        },
    });
};

// ── Expand/Collapse ────────────────────────────────────────
const expandedParams = ref(new Set());

const toggleParam = (paramId) => {
    if (expandedParams.value.has(paramId)) {
        expandedParams.value.delete(paramId);
    } else {
        expandedParams.value.add(paramId);
    }
};

const isExpanded = (paramId) => expandedParams.value.has(paramId);

// ── Delete Test ────────────────────────────────────────
const showDeleteConfirm = ref(false);
const deleteForm = useForm({});

const confirmDeleteTest = () => {
    showDeleteConfirm.value = true;
};

const performDeleteTest = () => {
    deleteForm.delete(route('admin.tests.destroy', props.test.ulid));
};
</script>

<template>
    <Head :title="'Edit - ' + test.name" />

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
                    Edit: {{ test.name }}
                </h2>
            </div>
        </template>

        <div class="mx-auto max-w-7xl space-y-6">

            <!-- ═══ Test Details Card ═══ -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div
                    class="flex cursor-pointer items-center justify-between border-b border-gray-200 px-6 py-4 hover:bg-gray-50"
                    @click="showTestDetails = !showTestDetails"
                >
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">Test Details</h3>
                        <p class="mt-0.5 text-sm text-gray-500">Basic information about this test</p>
                    </div>
                    <svg
                        class="h-5 w-5 text-gray-400 transition-transform"
                        :class="{ 'rotate-180': showTestDetails }"
                        fill="none" viewBox="0 0 24 24" stroke="currentColor"
                    >
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                    </svg>
                </div>

                <div v-show="showTestDetails" class="p-6">
                    <form @submit.prevent="saveTestDetails" class="space-y-6">
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Test Name *" />
                                <TextInput v-model="testForm.name" class="mt-1 block w-full" />
                                <InputError :message="testForm.errors.name" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel value="Short Name" />
                                <TextInput v-model="testForm.short_name" class="mt-1 block w-full" />
                                <InputError :message="testForm.errors.short_name" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <InputLabel value="Category *" />
                                <select
                                    v-model="testForm.test_category_id"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="">-- Select --</option>
                                    <option v-for="cat in categories" :key="cat.id" :value="cat.id">{{ cat.name }}</option>
                                </select>
                                <InputError :message="testForm.errors.test_category_id" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel value="Report Type *" />
                                <select
                                    v-model="testForm.report_type"
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                                >
                                    <option value="parametric">Parametric</option>
                                    <option value="text">Text</option>
                                    <option value="mixed">Mixed</option>
                                </select>
                                <InputError :message="testForm.errors.report_type" class="mt-2" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-3">
                            <div>
                                <InputLabel value="Price *" />
                                <TextInput v-model="testForm.price" type="number" step="0.01" min="0" class="mt-1 block w-full" />
                                <InputError :message="testForm.errors.price" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel value="Sample Type" />
                                <TextInput v-model="testForm.sample_type" class="mt-1 block w-full" />
                                <InputError :message="testForm.errors.sample_type" class="mt-2" />
                            </div>
                            <div>
                                <InputLabel value="Method" />
                                <TextInput v-model="testForm.method" class="mt-1 block w-full" />
                                <InputError :message="testForm.errors.method" class="mt-2" />
                            </div>
                        </div>

                        <div>
                            <InputLabel value="Description" />
                            <textarea
                                v-model="testForm.description"
                                rows="3"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            ></textarea>
                            <InputError :message="testForm.errors.description" class="mt-2" />
                        </div>

                        <div class="flex items-center justify-between border-t border-gray-200 pt-6">
                            <button
                                type="button"
                                class="text-sm font-medium text-red-600 hover:text-red-800"
                                @click="confirmDeleteTest"
                            >
                                Delete this test
                            </button>
                            <PrimaryButton type="submit" :disabled="testForm.processing">
                                Save Changes
                            </PrimaryButton>
                        </div>
                    </form>
                </div>
            </div>

            <!-- ═══ Parameters Section ═══ -->
            <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                <div class="flex items-center justify-between border-b border-gray-200 px-6 py-4">
                    <div>
                        <h3 class="text-lg font-semibold text-gray-900">
                            Parameters
                            <span class="ml-1 text-sm font-normal text-gray-500">({{ sortedParameters.length }})</span>
                        </h3>
                        <p class="mt-0.5 text-sm text-gray-500">Define what this test measures</p>
                    </div>
                    <div class="flex items-center gap-2">
                        <SecondaryButton @click="openCopyModal">
                            Copy from another test
                        </SecondaryButton>
                        <PrimaryButton @click="openAddParameter">
                            Add Parameter
                        </PrimaryButton>
                    </div>
                </div>

                <div v-if="sortedParameters.length" class="divide-y divide-gray-200">
                    <div v-for="param in sortedParameters" :key="param.id">
                        <!-- Parameter Header -->
                        <div
                            class="flex cursor-pointer items-center justify-between px-6 py-4 hover:bg-gray-50 transition-colors"
                            @click="toggleParam(param.id)"
                        >
                            <div class="flex items-center gap-4">
                                <svg
                                    class="h-4 w-4 text-gray-400 transition-transform"
                                    :class="{ 'rotate-90': isExpanded(param.id) }"
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                >
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                <div class="flex items-center gap-3">
                                    <span class="font-medium text-gray-900">{{ param.name }}</span>
                                    <span
                                        class="inline-flex rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="{
                                            'bg-blue-100 text-blue-700': param.field_type === 'numeric',
                                            'bg-green-100 text-green-700': param.field_type === 'text',
                                            'bg-orange-100 text-orange-700': param.field_type === 'options',
                                        }"
                                    >
                                        {{ param.field_type }}
                                    </span>
                                    <span v-if="param.unit" class="text-sm text-gray-500">({{ param.unit }})</span>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-gray-400">Order: {{ param.sort_order ?? 0 }}</span>
                                <span class="text-xs text-gray-400">{{ param.ranges?.length ?? 0 }} range(s)</span>
                                <div class="flex items-center gap-1">
                                    <button
                                        class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-gray-400"
                                        :disabled="sortedParameters.indexOf(param) === 0"
                                        title="Move up"
                                        @click.stop="reorderParameter(param.ulid, 'up')"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7" />
                                        </svg>
                                    </button>
                                    <button
                                        class="rounded p-1 text-gray-400 hover:bg-gray-200 hover:text-gray-600 disabled:opacity-30 disabled:cursor-not-allowed disabled:hover:bg-transparent disabled:hover:text-gray-400"
                                        :disabled="sortedParameters.indexOf(param) === sortedParameters.length - 1"
                                        title="Move down"
                                        @click.stop="reorderParameter(param.ulid, 'down')"
                                    >
                                        <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                                        </svg>
                                    </button>
                                </div>
                                <button
                                    class="text-sm font-medium text-indigo-600 hover:text-indigo-800"
                                    @click.stop="openEditParameter(param)"
                                >
                                    Edit
                                </button>
                                <button
                                    class="text-sm font-medium text-red-600 hover:text-red-800"
                                    @click.stop="confirmDeleteParam(param)"
                                >
                                    Delete
                                </button>
                            </div>
                        </div>

                        <!-- Expanded Content -->
                        <div v-if="isExpanded(param.id)" class="border-t border-gray-100 bg-gray-50/50 px-6 py-4 space-y-4">

                            <!-- Ranges (only show for numeric/options params) -->
                            <div v-if="param.field_type === 'numeric' || param.field_type === 'options'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Normal Ranges</span>
                                    <button
                                        class="text-xs font-medium text-indigo-600 hover:text-indigo-800"
                                        @click="openAddRange(param)"
                                    >
                                        + Add Range
                                    </button>
                                </div>
                                <div v-if="param.ranges && param.ranges.length" class="overflow-x-auto rounded-md border border-gray-200">
                                    <table class="min-w-full text-sm">
                                        <thead class="bg-gray-100/80">
                                            <tr>
                                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Group</th>
                                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Age</th>
                                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Min - Max</th>
                                                <th class="px-3 py-1.5 text-left text-xs font-medium text-gray-500">Text</th>
                                                <th class="px-3 py-1.5 w-20"></th>
                                            </tr>
                                        </thead>
                                        <tbody class="bg-white divide-y divide-gray-100">
                                            <tr v-for="range in param.ranges" :key="range.id">
                                                <td class="px-3 py-1.5">
                                                    <span v-if="range.range_group" class="inline-flex rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700">{{ range.range_group }}</span>
                                                    <span v-else class="text-gray-300">-</span>
                                                </td>
                                                <td class="px-3 py-1.5 text-gray-600 text-xs">
                                                    <template v-if="range.age_min != null || range.age_max != null">{{ range.age_min ?? '0' }}-{{ range.age_max ?? '∞' }}y</template>
                                                    <span v-else class="text-gray-300">-</span>
                                                </td>
                                                <td class="px-3 py-1.5 text-gray-600 text-xs">
                                                    <template v-if="range.min_value != null || range.max_value != null">{{ range.min_value ?? '' }} - {{ range.max_value ?? '' }}</template>
                                                    <span v-else class="text-gray-300">-</span>
                                                </td>
                                                <td class="px-3 py-1.5 text-gray-600 text-xs">{{ range.text_value || '-' }}</td>
                                                <td class="px-3 py-1.5 text-right">
                                                    <button class="text-xs text-indigo-600 hover:text-indigo-800 mr-2" @click="openEditRange(range, param.ulid)">Edit</button>
                                                    <button class="text-xs text-red-500 hover:text-red-700" @click="confirmDeleteRange(range)">Del</button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <p v-else class="text-xs text-gray-400 italic">No ranges yet.</p>
                            </div>

                            <!-- Templates (only for text params) -->
                            <div v-if="param.field_type === 'text'">
                                <div class="flex items-center justify-between mb-2">
                                    <span class="text-xs font-semibold uppercase tracking-wide text-gray-500">Quick-Fill Templates</span>
                                </div>
                                <div v-if="param.templates && param.templates.length" class="flex flex-wrap gap-2 mb-3">
                                    <div
                                        v-for="tpl in param.templates"
                                        :key="tpl.id"
                                        class="inline-flex items-center gap-1.5 rounded-md border border-gray-200 bg-white px-2.5 py-1.5 text-xs"
                                    >
                                        <span class="font-medium text-gray-800">{{ tpl.name }}</span>
                                        <span class="text-gray-400">-</span>
                                        <span class="text-gray-500 max-w-[200px] truncate">{{ tpl.content }}</span>
                                        <button class="ml-1 text-red-400 hover:text-red-600" @click="confirmDeleteTemplate(tpl)" title="Delete">
                                            <svg class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </div>
                                </div>
                                <form @submit.prevent="addTemplate(param)" class="flex items-center gap-2">
                                    <input
                                        v-model="getTemplateForm(param.id).name"
                                        type="text"
                                        class="w-32 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                                        placeholder="Name"
                                    />
                                    <input
                                        v-model="getTemplateForm(param.id).content"
                                        type="text"
                                        class="flex-1 rounded-md border-gray-300 text-xs shadow-sm focus:border-indigo-500 focus:ring-indigo-500 py-1.5"
                                        placeholder="Template content..."
                                    />
                                    <button
                                        type="submit"
                                        :disabled="getTemplateForm(param.id).processing"
                                        class="rounded-md bg-gray-800 px-3 py-1.5 text-xs font-medium text-white hover:bg-gray-700 disabled:opacity-50"
                                    >
                                        Add
                                    </button>
                                </form>
                                <div v-if="getTemplateForm(param.id).errors?.name || getTemplateForm(param.id).errors?.content" class="mt-1 text-xs text-red-500">
                                    {{ getTemplateForm(param.id).errors?.name || getTemplateForm(param.id).errors?.content }}
                                </div>
                            </div>

                            <!-- Info for text params with no ranges section -->
                            <p v-if="param.field_type === 'text' && (!param.templates || !param.templates.length)" class="text-xs text-gray-400 italic">
                                Text parameters don't use numeric ranges. Add templates above for quick-fill options.
                            </p>
                        </div>
                    </div>
                </div>

                <div v-else class="px-6 py-12 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 0 1 6 3.75h2.25A2.25 2.25 0 0 1 10.5 6v2.25a2.25 2.25 0 0 1-2.25 2.25H6a2.25 2.25 0 0 1-2.25-2.25V6ZM3.75 15.75A2.25 2.25 0 0 1 6 13.5h2.25a2.25 2.25 0 0 1 2.25 2.25V18a2.25 2.25 0 0 1-2.25 2.25H6A2.25 2.25 0 0 1 3.75 18v-2.25ZM13.5 6a2.25 2.25 0 0 1 2.25-2.25H18A2.25 2.25 0 0 1 20.25 6v2.25A2.25 2.25 0 0 1 18 10.5h-2.25a2.25 2.25 0 0 1-2.25-2.25V6ZM13.5 15.75a2.25 2.25 0 0 1 2.25-2.25H18a2.25 2.25 0 0 1 2.25 2.25V18A2.25 2.25 0 0 1 18 20.25h-2.25A2.25 2.25 0 0 1 13.5 18v-2.25Z" />
                    </svg>
                    <h3 class="mt-2 text-sm font-semibold text-gray-900">No parameters</h3>
                    <p class="mt-1 text-sm text-gray-500">Add parameters to define what this test measures.</p>
                    <div class="mt-6">
                        <PrimaryButton @click="openAddParameter">Add Parameter</PrimaryButton>
                    </div>
                </div>
            </div>
        </div>

        <!-- ═══ Parameter Modal ═══ -->
        <Modal :show="showParameterModal" maxWidth="lg" @close="showParameterModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ editingParameter ? 'Edit Parameter' : 'Add Parameter' }}
                </h3>
                <form @submit.prevent="saveParameter" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Parameter Name *" />
                        <TextInput v-model="parameterForm.name" class="mt-1 block w-full" autofocus placeholder="e.g. Hemoglobin" />
                        <InputError :message="parameterForm.errors.name" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Field Type *" />
                            <select
                                v-model="parameterForm.field_type"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                            >
                                <option value="numeric">Numeric</option>
                                <option value="text">Text</option>
                                <option value="options">Options</option>
                            </select>
                            <InputError :message="parameterForm.errors.field_type" class="mt-2" />
                        </div>
                        <div v-if="parameterForm.field_type === 'numeric'">
                            <InputLabel value="Unit" />
                            <TextInput v-model="parameterForm.unit" class="mt-1 block w-full" placeholder="e.g. mg/dL, g/dL" />
                            <InputError :message="parameterForm.errors.unit" class="mt-2" />
                        </div>
                    </div>
                    <div v-if="parameterForm.field_type === 'options'">
                        <InputLabel value="Options (comma-separated)" />
                        <TextInput v-model="parameterForm.options" class="mt-1 block w-full" placeholder="e.g. Positive, Negative, Trace" />
                        <InputError :message="parameterForm.errors.options" class="mt-2" />
                    </div>
                    <div>
                        <InputLabel value="Sort Order" />
                        <TextInput v-model="parameterForm.sort_order" type="number" class="mt-1 block w-24" />
                        <InputError :message="parameterForm.errors.sort_order" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton @click="showParameterModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="parameterForm.processing">
                            {{ editingParameter ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ═══ Range Modal ═══ -->
        <Modal :show="showRangeModal" maxWidth="lg" @close="showRangeModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">
                    {{ editingRange ? 'Edit Range' : 'Add Range' }}
                </h3>
                <form @submit.prevent="saveRange" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Range Group" />
                        <TextInput v-model="rangeForm.range_group" class="mt-1 block w-full" placeholder="e.g. Male, Female, All" />
                        <div class="mt-2 flex flex-wrap gap-1.5">
                            <button
                                v-for="suggestion in rangeGroupSuggestions"
                                :key="suggestion"
                                type="button"
                                class="rounded-full px-2.5 py-1 text-xs font-medium transition-colors"
                                :class="rangeForm.range_group === suggestion
                                    ? 'bg-indigo-600 text-white'
                                    : 'bg-gray-100 text-gray-600 hover:bg-gray-200'"
                                @click="rangeForm.range_group = suggestion"
                            >
                                {{ suggestion }}
                            </button>
                        </div>
                        <InputError :message="rangeForm.errors.range_group" class="mt-2" />
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Age Min" />
                            <TextInput v-model="rangeForm.age_min" type="number" class="mt-1 block w-full" placeholder="Optional" />
                            <InputError :message="rangeForm.errors.age_min" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Age Max" />
                            <TextInput v-model="rangeForm.age_max" type="number" class="mt-1 block w-full" placeholder="Optional" />
                            <InputError :message="rangeForm.errors.age_max" class="mt-2" />
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <InputLabel value="Min Value" />
                            <TextInput v-model="rangeForm.min_value" type="number" step="any" class="mt-1 block w-full" />
                            <InputError :message="rangeForm.errors.min_value" class="mt-2" />
                        </div>
                        <div>
                            <InputLabel value="Max Value" />
                            <TextInput v-model="rangeForm.max_value" type="number" step="any" class="mt-1 block w-full" />
                            <InputError :message="rangeForm.errors.max_value" class="mt-2" />
                        </div>
                    </div>
                    <div>
                        <InputLabel value="Text Value (for non-numeric ranges)" />
                        <TextInput v-model="rangeForm.text_value" class="mt-1 block w-full" placeholder="e.g. Non-Reactive, Negative" />
                        <InputError :message="rangeForm.errors.text_value" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton @click="showRangeModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="rangeForm.processing">
                            {{ editingRange ? 'Update' : 'Create' }}
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ═══ Copy Parameters Modal ═══ -->
        <Modal :show="showCopyModal" maxWidth="md" @close="showCopyModal = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Copy Parameters from Another Test</h3>
                <p class="mt-1 text-sm text-gray-500">Select a test to copy all its parameters and ranges into this test.</p>
                <form @submit.prevent="submitCopyParameters" class="mt-6 space-y-4">
                    <div>
                        <InputLabel value="Source Test *" />
                        <select
                            v-model="copyForm.source_test_id"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        >
                            <option value="">-- Select a test --</option>
                            <option v-for="t in allTests" :key="t.id" :value="t.id">
                                {{ t.name }} ({{ t.parameters_count }} params)
                            </option>
                        </select>
                        <InputError :message="copyForm.errors.source_test_id" class="mt-2" />
                    </div>
                    <div class="flex justify-end gap-3 pt-2">
                        <SecondaryButton @click="showCopyModal = false">Cancel</SecondaryButton>
                        <PrimaryButton type="submit" :disabled="copyForm.processing || !copyForm.source_test_id">
                            Copy Parameters
                        </PrimaryButton>
                    </div>
                </form>
            </div>
        </Modal>

        <!-- ═══ Delete Parameter Confirmation ═══ -->
        <Modal :show="showDeleteParamConfirm" maxWidth="sm" @close="showDeleteParamConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Parameter</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ deletingParam?.name }}</strong> and all its ranges? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteParamConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDeleteParam" :disabled="deleteParamForm.processing">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- ═══ Delete Range Confirmation ═══ -->
        <Modal :show="showDeleteRangeConfirm" maxWidth="sm" @close="showDeleteRangeConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Range</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete this range<template v-if="deletingRange?.range_group"> (<strong>{{ deletingRange.range_group }}</strong>)</template>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteRangeConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDeleteRange" :disabled="deleteRangeForm.processing">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- ═══ Delete Template Confirmation ═══ -->
        <Modal :show="showDeleteTemplateConfirm" maxWidth="sm" @close="showDeleteTemplateConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Template</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete the template <strong>{{ deletingTemplate?.name }}</strong>? This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteTemplateConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDeleteTemplate" :disabled="deleteTemplateForm.processing">
                        Delete
                    </DangerButton>
                </div>
            </div>
        </Modal>

        <!-- ═══ Delete Test Confirmation ═══ -->
        <Modal :show="showDeleteConfirm" maxWidth="sm" @close="showDeleteConfirm = false">
            <div class="p-6">
                <h3 class="text-lg font-semibold text-gray-900">Delete Test</h3>
                <p class="mt-2 text-sm text-gray-600">
                    Are you sure you want to delete <strong>{{ test.name }}</strong>? This will also delete all its parameters and ranges. This action cannot be undone.
                </p>
                <div class="mt-6 flex justify-end gap-3">
                    <SecondaryButton @click="showDeleteConfirm = false">Cancel</SecondaryButton>
                    <DangerButton @click="performDeleteTest" :disabled="deleteForm.processing">
                        Delete Test
                    </DangerButton>
                </div>
            </div>
        </Modal>
    </AuthenticatedLayout>
</template>
