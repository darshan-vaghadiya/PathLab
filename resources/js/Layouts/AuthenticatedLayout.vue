<script setup>
import { ref, computed } from 'vue';
import { Link, usePage } from '@inertiajs/vue3';
import { Toaster } from 'vue-sonner';
import { useFlashToast } from '@/Composables/useToast.js';

useFlashToast();

const showingSidebar = ref(true);
const showingMobileMenu = ref(false);

const page = usePage();
const user = computed(() => page.props.auth.user);
const role = computed(() => user.value.role);
const permissions = computed(() => page.props.auth.permissions ?? []);
const can = (perm) => permissions.value.includes(perm);

// Sidebar menu groups
const menuGroups = computed(() => {
    const groups = [];

    // Dashboard
    groups.push({
        label: null,
        items: [
            { label: 'Dashboard', route: 'dashboard', icon: 'home', current: 'dashboard' },
        ],
    });

    // Front Desk
    const frontDeskItems = [];
    if (can('manage_patients')) frontDeskItems.push({ label: 'Patients', route: 'patients.index', icon: 'users', current: 'patients.*' });
    if (can('create_orders') || can('view_orders')) frontDeskItems.push({ label: 'Orders', route: 'orders.index', icon: 'clipboard', current: 'orders.*' });
    if (frontDeskItems.length) groups.push({ label: 'Front Desk', items: frontDeskItems });

    // Lab
    const labItems = [];
    if (can('collect_samples')) labItems.push({ label: 'Pending Samples', route: 'lab.pendingSamples', icon: 'flask', current: 'lab.pendingSamples' });
    if (can('enter_results')) labItems.push({ label: 'Pending Results', route: 'lab.pendingResults', icon: 'microscope', current: 'lab.pendingResults' });
    if (labItems.length) groups.push({ label: 'Lab', items: labItems });

    // Doctor & Reports
    const doctorItems = [];
    if (can('approve_reports')) doctorItems.push({ label: 'Pending Approvals', route: 'doctor.pendingApprovals', icon: 'check-circle', current: 'doctor.*' });
    if (can('view_reports')) doctorItems.push({ label: 'Reports', route: 'reports.index', icon: 'chart', current: 'reports.*' });
    if (doctorItems.length) groups.push({ label: 'Doctor', items: doctorItems });

    // Test Management
    const testItems = [];
    if (can('manage_test_categories')) testItems.push({ label: 'Categories', route: 'admin.testCategories.index', icon: 'folder', current: 'admin.testCategories.*' });
    if (can('manage_tests')) testItems.push({ label: 'Tests', route: 'admin.tests.index', icon: 'beaker', current: 'admin.tests.*' });
    if (can('manage_packages')) testItems.push({ label: 'Packages', route: 'admin.packages.index', icon: 'package', current: 'admin.packages.*' });
    if (testItems.length) groups.push({ label: 'Test Management', items: testItems });

    // Partners
    const partnerItems = [];
    if (can('manage_referring_doctors')) partnerItems.push({ label: 'Referring Doctors', route: 'admin.referring-doctors.index', icon: 'stethoscope', current: 'admin.referring-doctors.*' });
    if (can('manage_b2b_clients')) partnerItems.push({ label: 'B2B Clients', route: 'admin.b2b-clients.index', icon: 'building', current: 'admin.b2b-clients.*' });
    if (can('view_commission_report')) partnerItems.push({ label: 'Commission Report', route: 'admin.referring-doctors.commissionReport', icon: 'currency', current: 'admin.referring-doctors.commissionReport' });
    if (partnerItems.length) groups.push({ label: 'Partners', items: partnerItems });

    // Settings
    const settingsItems = [];
    if (can('manage_users')) settingsItems.push({ label: 'Staff', route: 'admin.users.index', icon: 'shield', current: 'admin.users.*' });
    if (can('manage_permissions')) settingsItems.push({ label: 'Permissions', route: 'admin.permissions.index', icon: 'shield', current: 'admin.permissions.*' });
    if (can('manage_permissions')) settingsItems.push({ label: 'Lab Settings', route: 'admin.settings.index', icon: 'cog', current: 'admin.settings.*' });
    if (settingsItems.length) groups.push({ label: 'Settings', items: settingsItems });

    return groups;
});
</script>

<template>
    <div class="min-h-screen bg-gray-50">
        <Toaster position="top-right" :duration="4000" richColors closeButton />

        <!-- Mobile menu overlay -->
        <div v-show="showingMobileMenu" class="fixed inset-0 z-30 bg-gray-900/50 lg:hidden"
            @click="showingMobileMenu = false"></div>

        <!-- Sidebar -->
        <aside :class="[
            'fixed inset-y-0 left-0 z-40 flex w-64 flex-col bg-white border-r border-gray-200 transition-transform duration-200 ease-in-out lg:translate-x-0',
            showingMobileMenu ? 'translate-x-0' : '-translate-x-full'
        ]">
            <!-- Logo -->
            <div class="flex h-16 items-center gap-2 border-b border-gray-200 px-6">
                <div
                    class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-white font-bold text-sm">
                    P
                </div>
                <Link :href="route('dashboard')" class="text-lg font-bold text-gray-900">
                    PathLab
                </Link>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto scrollbar-hide px-3 py-4 space-y-4"
                style="-ms-overflow-style: none; scrollbar-width: none;">
                <div v-for="(group, gi) in menuGroups" :key="gi">
                    <p v-if="group.label"
                        class="mb-1 px-3 text-xs font-semibold uppercase tracking-wider text-gray-400">
                        {{ group.label }}
                    </p>
                    <ul class="space-y-1">
                        <li v-for="item in group.items" :key="item.route">
                            <Link :href="route(item.route)" :class="[
                                'group flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-medium transition-colors',
                                route().current(item.current)
                                    ? 'bg-indigo-50 text-indigo-700'
                                    : 'text-gray-700 hover:bg-gray-100 hover:text-gray-900'
                            ]">
                                <!-- Icons -->
                                <svg v-if="item.icon === 'home'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m2.25 12 8.954-8.955c.44-.439 1.152-.439 1.591 0L21.75 12M4.5 9.75v10.125c0 .621.504 1.125 1.125 1.125H9.75v-4.875c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21h4.125c.621 0 1.125-.504 1.125-1.125V9.75M8.25 21h8.25" />
                                </svg>
                                <svg v-else-if="item.icon === 'users'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'clipboard'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25ZM6.75 12h.008v.008H6.75V12Zm0 3h.008v.008H6.75V15Zm0 3h.008v.008H6.75V18Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'flask'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104m-4.5 0H14.25M9.75 3.104a.375.375 0 0 1 .375-.375h3.75a.375.375 0 0 1 .375.375" />
                                </svg>
                                <svg v-else-if="item.icon === 'microscope'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-2.247m0 0A8.966 8.966 0 0 1 3 12c0-1.264.26-2.467.732-3.559" />
                                </svg>
                                <svg v-else-if="item.icon === 'check-circle'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'chart'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 0 1 3 19.875v-6.75ZM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V8.625ZM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 0 1-1.125-1.125V4.125Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'folder'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M2.25 12.75V12A2.25 2.25 0 0 1 4.5 9.75h15A2.25 2.25 0 0 1 21.75 12v.75m-8.69-6.44-2.12-2.12a1.5 1.5 0 0 0-1.061-.44H4.5A2.25 2.25 0 0 0 2.25 6v12a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9a2.25 2.25 0 0 0-2.25-2.25h-5.379a1.5 1.5 0 0 1-1.06-.44Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'beaker'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M14.25 6.087c0-.355.186-.676.401-.959.221-.29.349-.634.349-1.003 0-1.036-1.007-1.875-2.25-1.875S10.5 3.089 10.5 4.125c0 .369.128.713.349 1.003.215.283.401.604.401.959v0a.64.64 0 0 1-.657.643 48.491 48.491 0 0 1-4.163-.3c.186 1.613.63 3.168 1.304 4.62a23.71 23.71 0 0 0 4.476 6.633c.168.18.349.346.541.498a23.76 23.76 0 0 0 4.476-6.633 20.56 20.56 0 0 0 1.304-4.62 48.479 48.479 0 0 1-4.163.3.64.64 0 0 1-.657-.643Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'package'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="m21 7.5-9-5.25L3 7.5m18 0-9 5.25m9-5.25v9l-9 5.25M3 7.5l9 5.25M3 7.5v9l9 5.25m0-9v9" />
                                </svg>
                                <svg v-else-if="item.icon === 'stethoscope'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'building'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M3.75 21h16.5M4.5 3h15M5.25 3v18m13.5-18v18M9 6.75h1.5m-1.5 3h1.5m-1.5 3h1.5m3-6H15m-1.5 3H15m-1.5 3H15M9 21v-3.375c0-.621.504-1.125 1.125-1.125h3.75c.621 0 1.125.504 1.125 1.125V21" />
                                </svg>
                                <svg v-else-if="item.icon === 'shield'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'cog'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 0 1 1.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 0 1-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 0 1-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.506-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 0 1 .12-1.45l.773-.773a1.125 1.125 0 0 1 1.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894Z" />
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                                </svg>
                                <svg v-else-if="item.icon === 'currency'" class="h-5 w-5 shrink-0" fill="none"
                                    viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round"
                                        d="M15 8.25H9m6 3H9m3 6-3-3h1.5a3 3 0 1 0 0-6M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                                </svg>
                                <span>{{ item.label }}</span>
                            </Link>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- User section at bottom -->
            <div class="border-t border-gray-200 px-3 py-3">
                <div class="flex items-center gap-3 px-3">
                    <Link :href="route('profile.edit')"
                        class="flex h-8 w-8 items-center justify-center rounded-full bg-indigo-100 text-indigo-600 font-semibold text-xs hover:bg-indigo-200 transition-colors shrink-0">
                        {{ user.name.charAt(0).toUpperCase() }}
                    </Link>
                    <Link :href="route('profile.edit')" class="flex-1 truncate">
                        <p class="text-sm font-medium text-gray-900 truncate">{{ user.name }}</p>
                        <p class="text-xs text-gray-500 capitalize">{{ role }}</p>
                    </Link>
                    <Link :href="route('logout')" method="post" as="button"
                        class="flex h-9 w-9 items-center justify-center rounded-md bg-red-500 text-white hover:bg-red-600 transition-colors shrink-0"
                        title="Logout">
                        <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                d="M15.75 9V5.25A2.25 2.25 0 0 0 13.5 3h-6a2.25 2.25 0 0 0-2.25 2.25v13.5A2.25 2.25 0 0 0 7.5 21h6a2.25 2.25 0 0 0 2.25-2.25V15m3 0 3-3m0 0-3-3m3 3H9" />
                        </svg>
                    </Link>
                </div>
            </div>
        </aside>

        <!-- Main content -->
        <div class="lg:pl-64">
            <!-- Top bar -->
            <header
                class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-gray-200 bg-white px-4 sm:px-6">
                <!-- Mobile menu button -->
                <button @click="showingMobileMenu = !showingMobileMenu"
                    class="inline-flex items-center justify-center rounded-md p-2 text-gray-400 hover:bg-gray-100 hover:text-gray-500 lg:hidden">
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
                    </svg>
                </button>

                <!-- Page heading -->
                <div class="flex-1">
                    <slot name="header" />
                </div>
            </header>

            <!-- Page content -->
            <main class="p-4 sm:p-6 lg:p-8">
                <slot />
            </main>
        </div>
    </div>
</template>
