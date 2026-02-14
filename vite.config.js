import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
            laravel({
                input: [
                    'resources/css/app.css',
                    'resources/js/app.js',
                    'resources/css/login.css',
                    'resources/css/dashboard.css',
                    'resources/css/admin_layout..css',
                    're'
                ],
                refresh: true, // Hot reload for dev
            }),
        tailwindcss(),
    ],
    build: {
        // Key fix: Laravel expects assets in public/build/assets
        outDir: 'public/build',
        assetsDir: 'assets',

        // Optional: minify JS/CSS for production
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true,
                drop_debugger: true
            }
        },

        // Optional: chunk size warnings
        chunkSizeWarningLimit: 1000,

        rollupOptions: {
            output: {
                manualChunks: {
                    vendor: ['axios'] // vendor splitting
                }
            }
        },

        // Optional: source maps
        sourcemap: false
    },
    optimizeDeps: {
        include: ['axios']
    },
    server: {
        // HMR for dev
        hmr: {
            host: 'localhost'
        }
    }
});
