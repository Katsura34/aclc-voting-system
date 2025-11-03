import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    build: {
        // Optimize build output
        minify: 'terser',
        terserOptions: {
            compress: {
                drop_console: true, // Remove console.log in production
                drop_debugger: true
            }
        },
        // Reduce chunk size warnings threshold
        chunkSizeWarningLimit: 1000,
        rollupOptions: {
            output: {
                // Manual chunking for better caching
                manualChunks: {
                    vendor: ['axios']
                }
            }
        },
        // Enable source maps for debugging (disable in production if needed)
        sourcemap: false
    },
    // Optimize dependencies
    optimizeDeps: {
        include: ['axios']
    },
    server: {
        // Hot Module Replacement configuration
        hmr: {
            host: 'localhost'
        }
    }
});
