import { defineConfig } from "vite";
import laravel from "laravel-vite-plugin";
import vue from "@vitejs/plugin-vue";

export default defineConfig({
    build: {
        rollupOptions: {
            output: {
                manualChunks(id) {
                    if (!id.includes("node_modules")) {
                        return;
                    }

                    if (id.includes("@fullcalendar")) {
                        return "vendor-fullcalendar";
                    }

                    if (id.includes("chart.js") || id.includes("vue-chartjs")) {
                        return "vendor-charts";
                    }

                    if (id.includes("@fortawesome")) {
                        return "vendor-fontawesome";
                    }

                    if (id.includes("sweetalert2")) {
                        return "vendor-sweetalert";
                    }

                    if (id.includes("@inertiajs") || id.includes("ziggy-js")) {
                        return "vendor-inertia";
                    }

                    if (id.includes("vue")) {
                        return "vendor-vue";
                    }
                },
            },
        },
    },
    plugins: [
        laravel({
            input: "resources/js/app.js",
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
});
