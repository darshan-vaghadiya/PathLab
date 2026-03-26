import '../css/app.css';
import 'vue-sonner/style.css';
import './bootstrap';

import { createInertiaApp, router } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { Toaster, toast } from 'vue-sonner';

const appName = import.meta.env.VITE_APP_NAME || 'PathLab';

// Global toast on Inertia page visits (flash messages)
router.on('finish', (event) => {
    const page = event.detail.visit?.completed ? null : null;
});

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue);

        // Make toast globally available
        app.config.globalProperties.$toast = toast;
        app.provide('toast', toast);

        // Register Toaster component globally
        app.component('Toaster', Toaster);

        return app.mount(el);
    },
    progress: {
        color: '#4f46e5',
    },
});
