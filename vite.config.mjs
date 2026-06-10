import { defineConfig, loadEnv } from "vite";
import laravel from "laravel-vite-plugin";
import tailwindcss from "@tailwindcss/vite";
import vue from "@vitejs/plugin-vue2";
import { URL } from "node:url";

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');
    const appUrl = new URL(env.APP_URL || 'http://localhost');

    return {
        plugins: [
            laravel({
                publicDirectory: 'html',
                input: ['resources/js/app.js'],
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
            tailwindcss(),
        ],
        resolve: {
            alias: {
                vue: 'vue/dist/vue.esm.js',
            },
        },
        server: {
            host: appUrl.hostname,
            port: appUrl.port || 5173,
            https: appUrl.protocol === 'https:',
            cors: {
                origin: appUrl.origin,
                credentials: true,
            },
            hmr: {
                host: appUrl.hostname,
                protocol: appUrl.protocol.replace(':', ''),
                port: appUrl.port || 5173,
            },
        },
    };
});
