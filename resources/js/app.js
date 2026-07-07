import "./bootstrap";
import "../css/app.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import '../css/calendar.css';

const appName = import.meta.env.VITE_APP_NAME || "TARRAYA";

createInertiaApp({
    title: (title) => title ? `${title} - ${appName}` : appName,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob([
                "./Pages/**/*.vue",
                "!./Pages/**/Partials/**/*.vue",
            ])
        ),
    setup({ el, App, props, plugin }) {
        const app = createApp({ render: () => h(App, props) });

        app.use(plugin).use(ZiggyVue);

        app.mount(el);
    },
    progress: {
        color: "#2563EB",
    },
});
