<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    labSettings: Object,
    testsByCategory: Object,
    packages: Array,
});

const mobileOpen = ref(false);
const activeCategory = ref('');
const reportToken = ref('');

const categories = computed(() => Object.keys(props.testsByCategory || {}));
const activeTests = computed(() => props.testsByCategory?.[activeCategory.value] || []);
const totalTests = computed(() => Object.values(props.testsByCategory || {}).reduce((s, t) => s + t.length, 0));

onMounted(() => {
    if (categories.value.length) activeCategory.value = categories.value[0];
});

const fmt = (p) => '₹' + Number(p).toLocaleString('en-IN');
const goReport = () => { const t = reportToken.value.trim(); if (t) window.location.href = '/report/lookup?q=' + encodeURIComponent(t); };
</script>

<template>
    <Head :title="labSettings.lab_name || 'PathLab'" />
    <div class="min-h-screen bg-white">

        <!-- NAVBAR -->
        <header class="sticky top-0 z-50 border-b border-gray-100 bg-white/95 backdrop-blur">
            <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-4 sm:px-6 lg:px-8">
                <a href="/" class="flex items-center gap-2.5">
                    <div class="flex h-9 w-9 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">P</div>
                    <span class="text-lg font-bold text-gray-900">{{ labSettings.lab_name || 'PathLab' }}</span>
                </a>
                <div class="hidden items-center gap-1 md:flex">
                    <a v-for="l in [{t:'Services',h:'#services'},{t:'Tests',h:'#tests'},{t:'Packages',h:'#packages'},{t:'Report',h:'#report'},{t:'Contact',h:'#contact'}]" :key="l.h" :href="l.h" class="rounded-md px-3 py-2 text-sm font-medium text-gray-600 transition hover:bg-gray-50 hover:text-gray-900">{{ l.t }}</a>
                    <Link :href="route('login')" class="ml-3 rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-700">Staff Login</Link>
                </div>
                <button @click="mobileOpen = !mobileOpen" class="rounded-lg p-2 text-gray-500 hover:bg-gray-100 md:hidden">
                    <svg v-if="!mobileOpen" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" /></svg>
                    <svg v-else class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
                </button>
            </div>
            <div v-if="mobileOpen" class="border-t border-gray-100 bg-white px-4 pb-4 pt-2 md:hidden">
                <a v-for="l in [{t:'Services',h:'#services'},{t:'Tests',h:'#tests'},{t:'Packages',h:'#packages'},{t:'Report',h:'#report'},{t:'Contact',h:'#contact'}]" :key="l.h" :href="l.h" @click="mobileOpen=false" class="block rounded-md px-3 py-2.5 text-sm font-medium text-gray-700 hover:bg-gray-50">{{ l.t }}</a>
                <Link :href="route('login')" class="mt-3 block rounded-lg bg-indigo-600 px-4 py-2.5 text-center text-sm font-semibold text-white">Staff Login</Link>
            </div>
        </header>

        <!-- HERO -->
        <section class="relative overflow-hidden bg-gradient-to-br from-indigo-700 via-indigo-800 to-blue-900">
            <div class="absolute inset-0 opacity-[0.07]" style="background-image: radial-gradient(circle at 1px 1px, white 1px, transparent 0); background-size: 28px 28px;"></div>
            <div class="absolute -right-40 -top-40 h-96 w-96 rounded-full bg-indigo-500/20 blur-3xl"></div>
            <div class="absolute -bottom-40 -left-40 h-96 w-96 rounded-full bg-blue-500/20 blur-3xl"></div>

            <div class="relative mx-auto max-w-7xl px-4 py-20 sm:px-6 sm:py-28 lg:flex lg:items-center lg:gap-16 lg:px-8 lg:py-36">
                <div class="max-w-2xl lg:flex-1">
                    <div class="inline-flex items-center gap-2 rounded-full border border-white/20 bg-white/10 px-4 py-1.5 text-xs font-semibold text-indigo-100 backdrop-blur-sm">
                        <span class="h-1.5 w-1.5 rounded-full bg-emerald-400"></span>
                        Trusted by 1000+ patients
                    </div>
                    <h1 class="mt-6 text-4xl font-extrabold tracking-tight text-white sm:text-5xl lg:text-6xl">
                        Accurate Diagnostics,
                        <span class="text-indigo-300">Reliable Care.</span>
                    </h1>
                    <p class="mt-6 max-w-lg text-lg leading-relaxed text-indigo-200">
                        Advanced pathology testing with quick turnaround times. Get your reports digitally — anytime, anywhere.
                    </p>
                    <div class="mt-8 flex flex-wrap gap-3">
                        <a href="#tests" class="inline-flex items-center gap-2 rounded-lg bg-white px-6 py-3 text-sm font-semibold text-indigo-700 shadow-lg transition hover:bg-indigo-50">
                            View Our Tests
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" /></svg>
                        </a>
                        <a href="#report" class="inline-flex items-center gap-2 rounded-lg border-2 border-white/25 bg-white/10 px-6 py-3 text-sm font-semibold text-white backdrop-blur-sm transition hover:bg-white/20">
                            Check Your Report
                        </a>
                    </div>
                </div>

                <!-- Hero stats -->
                <div class="mt-12 grid grid-cols-2 gap-4 lg:mt-0 lg:w-80">
                    <div class="rounded-xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-3xl font-bold text-white">{{ totalTests }}+</p>
                        <p class="mt-1 text-sm text-indigo-200">Lab Tests</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-3xl font-bold text-white">{{ categories.length }}</p>
                        <p class="mt-1 text-sm text-indigo-200">Categories</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-3xl font-bold text-white">24hr</p>
                        <p class="mt-1 text-sm text-indigo-200">Report Time</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/10 p-5 backdrop-blur-sm">
                        <p class="text-3xl font-bold text-white">100%</p>
                        <p class="mt-1 text-sm text-indigo-200">Digital</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- QUICK REPORT CHECKER (inline bar) -->
        <section class="border-b border-gray-100 bg-indigo-50">
            <div class="mx-auto flex max-w-7xl flex-col items-center gap-3 px-4 py-4 sm:flex-row sm:px-6 lg:px-8">
                <div class="flex items-center gap-2 text-sm font-medium text-indigo-800">
                    <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
                    Check Your Report:
                </div>
                <div class="flex flex-1 gap-2 sm:max-w-md">
                    <input v-model="reportToken" type="text" placeholder="Enter report number (e.g. RPT-20260319-0001)" @keyup.enter="goReport" class="flex-1 rounded-lg border-indigo-200 bg-white px-4 py-2 text-sm text-gray-900 placeholder-gray-400 focus:border-indigo-400 focus:ring-indigo-400" />
                    <button @click="goReport" :disabled="!reportToken.trim()" class="rounded-lg bg-indigo-600 px-5 py-2 text-sm font-semibold text-white transition hover:bg-indigo-700 disabled:opacity-40">View</button>
                </div>
            </div>
        </section>

        <!-- WHY CHOOSE US -->
        <section id="services" class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Our Services</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Why Choose Us</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-gray-500">Quality diagnostics backed by modern technology and experienced professionals.</p>
                </div>

                <div class="mt-14 grid gap-8 sm:grid-cols-2 lg:grid-cols-4">
                    <div v-for="(item, i) in [
                        { title: 'Accurate Results', desc: 'State-of-the-art analyzers with strict quality control for reliable, precise results every time.', bg: 'bg-blue-50', icon_color: 'text-blue-600', icon: 'M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
                        { title: 'Quick Reports', desc: 'Most reports ready within 24 hours. Urgent and critical tests available with same-day results.', bg: 'bg-amber-50', icon_color: 'text-amber-600', icon: 'M12 6v6h4.5m4.5 0a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z' },
                        { title: 'Expert Pathologists', desc: 'All reports verified and approved by experienced MD Pathologists with years of clinical experience.', bg: 'bg-emerald-50', icon_color: 'text-emerald-600', icon: 'M15.75 6a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0ZM4.501 20.118a7.5 7.5 0 0 1 14.998 0A17.933 17.933 0 0 1 12 21.75c-2.676 0-5.216-.584-7.499-1.632Z' },
                        { title: 'Digital Reports', desc: 'Access your reports online anytime. Download PDF, share via link, or receive on WhatsApp/SMS.', bg: 'bg-purple-50', icon_color: 'text-purple-600', icon: 'M10.5 1.5H8.25A2.25 2.25 0 0 0 6 3.75v16.5a2.25 2.25 0 0 0 2.25 2.25h7.5A2.25 2.25 0 0 0 18 20.25V3.75a2.25 2.25 0 0 0-2.25-2.25H13.5m-3 0V3h3V1.5m-3 0h3' },
                    ]" :key="i" class="group rounded-2xl border border-gray-100 bg-white p-6 shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div :class="[item.bg, item.icon_color]" class="flex h-12 w-12 items-center justify-center rounded-xl">
                            <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" :d="item.icon" /></svg>
                        </div>
                        <h3 class="mt-5 text-lg font-bold text-gray-900">{{ item.title }}</h3>
                        <p class="mt-2 text-sm leading-relaxed text-gray-500">{{ item.desc }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- TESTS -->
        <section id="tests" class="bg-gray-50 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Diagnostics</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Our Test Menu</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-gray-500">Browse our comprehensive range of diagnostic tests.</p>
                </div>

                <div class="mt-10 flex flex-wrap justify-center gap-2">
                    <button
                        v-for="cat in categories" :key="cat"
                        @click="activeCategory = cat"
                        :class="activeCategory === cat
                            ? 'bg-indigo-600 text-white shadow-md'
                            : 'bg-white text-gray-600 border border-gray-200 hover:border-gray-300 hover:text-gray-800'"
                        class="rounded-full px-5 py-2.5 text-sm font-medium transition-all"
                    >{{ cat }}</button>
                </div>

                <div class="mt-8 overflow-hidden rounded-xl border border-gray-200 bg-white shadow-sm">
                    <table class="w-full text-left text-sm">
                        <thead class="border-b border-gray-100 bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 font-semibold text-gray-700">Test Name</th>
                                <th class="px-6 py-3 font-semibold text-gray-700 max-sm:hidden">Sample Type</th>
                                <th class="px-6 py-3 text-right font-semibold text-gray-700">Price</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-50">
                            <tr v-for="test in activeTests" :key="test.name" class="hover:bg-gray-50">
                                <td class="px-6 py-4">
                                    <p class="font-medium text-gray-900">{{ test.name }}</p>
                                    <p v-if="test.short_name" class="text-xs text-gray-400">{{ test.short_name }}</p>
                                </td>
                                <td class="px-6 py-4 text-gray-500 max-sm:hidden">{{ test.sample_type || '-' }}</td>
                                <td class="px-6 py-4 text-right">
                                    <span class="inline-block rounded-md bg-indigo-50 px-3 py-1 text-sm font-bold text-indigo-700">{{ fmt(test.price) }}</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </section>

        <!-- PACKAGES -->
        <section v-if="packages && packages.length" id="packages" class="py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Value Packs</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Health Packages</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-gray-500">Save more with our comprehensive health screening packages.</p>
                </div>

                <div class="mx-auto mt-12 grid max-w-4xl gap-8 sm:grid-cols-2">
                    <div v-for="pkg in packages" :key="pkg.name" class="group relative overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm transition-all hover:-translate-y-1 hover:shadow-xl">
                        <div class="bg-gradient-to-r from-indigo-600 to-blue-600 px-6 py-5">
                            <div class="flex items-center justify-between">
                                <h3 class="text-xl font-bold text-white">{{ pkg.name }}</h3>
                                <span class="rounded-full bg-white/20 px-3 py-1 text-xs font-semibold text-white">{{ pkg.test_count }} tests</span>
                            </div>
                        </div>
                        <div class="p-6">
                            <p class="text-sm leading-relaxed text-gray-500">{{ pkg.description }}</p>
                            <div class="mt-6 flex items-baseline gap-1 border-t border-gray-100 pt-5">
                                <span class="text-4xl font-extrabold text-gray-900">{{ fmt(pkg.price) }}</span>
                                <span class="text-sm text-gray-400">/ package</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- CHECK REPORT (full section) -->
        <section id="report" class="bg-gradient-to-br from-indigo-600 via-indigo-700 to-blue-800 py-16 sm:py-24">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-2xl text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-indigo-200">For Patients</span>
                    <h2 class="mt-2 text-3xl font-bold text-white sm:text-4xl">Access Your Report</h2>
                    <p class="mt-4 text-lg text-indigo-200">Enter the report number from your receipt or SMS to view and download your lab report.</p>

                    <div class="mt-8 flex flex-col gap-3 sm:flex-row sm:justify-center">
                        <input
                            v-model="reportToken" type="text" placeholder="Paste your report token here..."
                            @keyup.enter="goReport"
                            class="w-full rounded-xl border-0 px-5 py-4 text-sm text-gray-900 shadow-xl placeholder-gray-400 focus:ring-2 focus:ring-white/50 sm:max-w-sm"
                        />
                        <button @click="goReport" :disabled="!reportToken.trim()" class="rounded-xl bg-white px-8 py-4 text-sm font-bold text-indigo-700 shadow-xl transition hover:bg-indigo-50 disabled:opacity-40">
                            View Report
                        </button>
                    </div>
                    <p class="mt-4 text-xs text-indigo-300">Your report number looks like: RPT-20260319-0001 (printed on your receipt)</p>
                </div>
            </div>
        </section>

        <!-- CONTACT -->
        <section id="contact" class="py-16 sm:py-24" v-if="labSettings.lab_phone || labSettings.lab_email || labSettings.lab_address">
            <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
                <div class="text-center">
                    <span class="text-sm font-semibold uppercase tracking-wider text-indigo-600">Reach Us</span>
                    <h2 class="mt-2 text-3xl font-bold text-gray-900 sm:text-4xl">Get In Touch</h2>
                    <p class="mx-auto mt-4 max-w-2xl text-gray-500">Visit us or reach out for any queries about our services and tests.</p>
                </div>

                <div class="mx-auto mt-12 grid max-w-4xl gap-6 sm:grid-cols-3">
                    <a v-if="labSettings.lab_phone" :href="'tel:' + labSettings.lab_phone" class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-emerald-50 transition group-hover:bg-emerald-100">
                            <svg class="h-6 w-6 text-emerald-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 0 0 2.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 0 1-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 0 0-1.091-.852H4.5A2.25 2.25 0 0 0 2.25 4.5v2.25Z" /></svg>
                        </div>
                        <p class="mt-4 text-sm font-bold text-gray-900">Phone</p>
                        <p class="mt-1 text-sm text-indigo-600">{{ labSettings.lab_phone }}</p>
                    </a>
                    <a v-if="labSettings.lab_email" :href="'mailto:' + labSettings.lab_email" class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-blue-50 transition group-hover:bg-blue-100">
                            <svg class="h-6 w-6 text-blue-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 0 1-2.25 2.25h-15a2.25 2.25 0 0 1-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0 0 19.5 4.5h-15a2.25 2.25 0 0 0-2.25 2.25m19.5 0v.243a2.25 2.25 0 0 1-1.07 1.916l-7.5 4.615a2.25 2.25 0 0 1-2.36 0L3.32 8.91a2.25 2.25 0 0 1-1.07-1.916V6.75" /></svg>
                        </div>
                        <p class="mt-4 text-sm font-bold text-gray-900">Email</p>
                        <p class="mt-1 text-sm text-indigo-600">{{ labSettings.lab_email }}</p>
                    </a>
                    <div v-if="labSettings.lab_address" class="group flex flex-col items-center rounded-2xl border border-gray-100 bg-white p-8 text-center shadow-sm transition-all hover:-translate-y-1 hover:shadow-lg">
                        <div class="flex h-14 w-14 items-center justify-center rounded-full bg-amber-50 transition group-hover:bg-amber-100">
                            <svg class="h-6 w-6 text-amber-600" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15 10.5a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 10.5c0 7.142-7.5 11.25-7.5 11.25S4.5 17.642 4.5 10.5a7.5 7.5 0 1 1 15 0Z" /></svg>
                        </div>
                        <p class="mt-4 text-sm font-bold text-gray-900">Address</p>
                        <p class="mt-1 text-sm text-gray-500">{{ labSettings.lab_address }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- FOOTER -->
        <footer class="border-t border-gray-200 bg-gray-900">
            <div class="mx-auto max-w-7xl px-4 py-10 sm:px-6 lg:px-8">
                <div class="flex flex-col items-center justify-between gap-6 sm:flex-row">
                    <div class="flex items-center gap-2.5">
                        <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-indigo-600 text-sm font-bold text-white">P</div>
                        <span class="text-sm font-bold text-white">{{ labSettings.lab_name || 'PathLab' }}</span>
                    </div>
                    <div class="flex gap-6">
                        <a href="#services" class="text-sm text-gray-400 hover:text-white">Services</a>
                        <a href="#tests" class="text-sm text-gray-400 hover:text-white">Tests</a>
                        <a href="#packages" class="text-sm text-gray-400 hover:text-white">Packages</a>
                        <a href="#contact" class="text-sm text-gray-400 hover:text-white">Contact</a>
                    </div>
                    <p class="text-xs text-gray-500">&copy; {{ new Date().getFullYear() }} {{ labSettings.lab_name || 'PathLab' }}</p>
                </div>
            </div>
        </footer>
    </div>
</template>

<style>
html { scroll-behavior: smooth; }
</style>
