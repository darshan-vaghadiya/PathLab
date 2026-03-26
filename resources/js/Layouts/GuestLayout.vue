<script setup>
</script>

<template>
    <div class="flex min-h-screen">
        <!-- Left: Branding -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-indigo-700 via-indigo-800 to-indigo-950 relative overflow-hidden">
            <!-- Pattern overlay -->
            <div class="absolute inset-0 opacity-10">
                <svg class="h-full w-full" xmlns="http://www.w3.org/2000/svg">
                    <defs>
                        <pattern id="grid" width="40" height="40" patternUnits="userSpaceOnUse">
                            <path d="M 40 0 L 0 0 0 40" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>

            <div class="relative flex flex-col justify-between p-12 text-white w-full">
                <!-- Logo -->
                <div>
                    <div class="flex items-center gap-3">
                        <div class="flex h-12 w-12 items-center justify-center rounded-xl bg-white/20 backdrop-blur-sm text-white font-bold text-xl">
                            P
                        </div>
                        <div>
                            <h1 class="text-2xl font-bold tracking-tight">PathLab</h1>
                            <p class="text-sm text-indigo-200">Laboratory Management System</p>
                        </div>
                    </div>
                </div>

                <!-- Features -->
                <div class="space-y-8">
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 0 1-.659 1.591L5.532 13.97a8.25 8.25 0 0 0-2.282 4.709 1.5 1.5 0 0 0 1.483 1.696h14.534a1.5 1.5 0 0 0 1.483-1.696 8.25 8.25 0 0 0-2.282-4.709l-3.559-3.561A2.25 2.25 0 0 1 14.25 8.818V3.104" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Sample Tracking</h3>
                            <p class="mt-1 text-sm text-indigo-200">Track samples from collection to report delivery</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 0 0 2.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 0 0-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 0 0 .75-.75 2.25 2.25 0 0 0-.1-.664m-5.8 0A2.251 2.251 0 0 1 13.5 2.25H15a2.25 2.25 0 0 1 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Automated Reports</h3>
                            <p class="mt-1 text-sm text-indigo-200">Generate professional PDF reports with one click</p>
                        </div>
                    </div>
                    <div class="flex items-start gap-4">
                        <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-lg bg-white/10">
                            <svg class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                            </svg>
                        </div>
                        <div>
                            <h3 class="font-semibold">Multi-Role Access</h3>
                            <p class="mt-1 text-sm text-indigo-200">Receptionist, Technician, Doctor - each with their own workflow</p>
                        </div>
                    </div>
                </div>

                <!-- Footer -->
                <p class="text-xs text-indigo-300">Pathology Lab Management System</p>
            </div>
        </div>

        <!-- Right: Auth Form -->
        <div class="flex w-full flex-col justify-center px-6 py-12 lg:w-1/2">
            <div class="mx-auto w-full max-w-sm">
                <!-- Mobile logo -->
                <div class="mb-8 lg:hidden">
                    <div class="flex items-center gap-3">
                        <div class="flex h-10 w-10 items-center justify-center rounded-xl bg-indigo-600 text-white font-bold text-lg">
                            P
                        </div>
                        <div>
                            <h1 class="text-xl font-bold text-gray-900">PathLab</h1>
                            <p class="text-xs text-gray-500">Laboratory Management System</p>
                        </div>
                    </div>
                </div>

                <slot />
            </div>
        </div>
    </div>
</template>
