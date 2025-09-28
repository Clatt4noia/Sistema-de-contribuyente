import {
    defineConfig
} from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from "@tailwindcss/vite";
import fs from 'fs';
import path from 'path';

const fluxStylesPath = path.resolve(__dirname, 'vendor/livewire/flux/dist/flux.css');
const fluxStylesFallbackPath = path.resolve(__dirname, 'resources/css/vendor/flux.css');

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    resolve: {
        alias: {
            '@flux-styles': fs.existsSync(fluxStylesPath)
                ? fluxStylesPath
                : fluxStylesFallbackPath,
        },
    },
    server: {
        cors: true,
        host: '127.0.0.1',
        hmr: {
            host: '127.0.0.1'
        },
    },
});
