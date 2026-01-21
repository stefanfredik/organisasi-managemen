import { defineConfig, loadEnv } from 'vite';
import laravel from 'laravel-vite-plugin';
import vue from '@vitejs/plugin-vue';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const port = Number(env.VITE_PORT) || 5178;
    const hmrHost = env.VITE_HMR_HOST || 'localhost';

    return {
        server: {
            host: true,
            port,
            watch: {
                usePolling: true,
                interval: 150,
            },
            hmr: {
                host: hmrHost,
            },
        },
        plugins: [
            laravel({
                input: 'resources/js/app.js',
                refresh: true,
            }),
            vue({
                template: {
                    transformAssetUrls: {
                        base: null,
                        includeAbsolute: false,
                    },
                },
            }),
        ],
    };
});
