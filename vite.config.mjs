import {defineConfig} from 'vite';
import laravel from 'laravel-vite-plugin';
import path from "path";

export default defineConfig({
    build: {
        commonjsOptions: {
            include: ["tailwind.config.js", "node_modules/**"],
        },
    },
    optimizeDeps: {
        include: ["tailwind-config"],
    },
    plugins: [
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
            ],
            refresh: true,
        }),
    ],
    resolve: {
        alias: {
            "tailwind-config": path.resolve(__dirname, "./tailwind.config.js"),
        },
    },
});
