import '../css/app.css';
import './bootstrap';

import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import { createApp, h, watch } from 'vue';
import { ZiggyVue } from '../../vendor/tightenco/ziggy';
import { applyTheme } from './composables/useTheme';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob('./Pages/**/*.vue'),
        ),
    setup({ el, App, props, plugin }) {
        // Apply theme on initial load
        const theme = props.initialPage?.props?.appSettings?.theme;
        if (theme) applyTheme(theme);

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .mixin({
                methods: {
                    hasPermission(permission) {
                        const user = this.$page.props.auth?.user;
                        if (!user) return false;
                        if (user.role === 'admin') return true;
                        return user.permissions?.includes(permission);
                    }
                }
            });

        app.mount(el);

        // Watch for theme changes after navigation
        const vm = app.config.globalProperties;
        if (vm.$page) {
            watch(() => vm.$page.props.appSettings?.theme, (newTheme) => {
                if (newTheme) applyTheme(newTheme);
            });
        }

        return app;
    },
    progress: {
        color: '#4B5563',
    },
});
