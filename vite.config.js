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
    // server: {
    //     host: '0.0.0.0', // Allows access from other devices
    //     port: 5173,      // Default Vite port (change if needed)
    //     strictPort: true,
    //     hmr: {
    //         host: '172.30.200.58', // Use your local IP
    //     }
    // }
});
