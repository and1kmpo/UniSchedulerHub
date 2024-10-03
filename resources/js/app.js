import "./bootstrap";
import "../css/app.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";
import Multiselect from "vue-multiselect";
import "vue-multiselect/dist/vue-multiselect.css";
import ElementPlus from "element-plus";
import "element-plus/dist/index.css";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

createInertiaApp({
    title: (title) => `${title} - ${appName}`,
    resolve: (name) =>
        resolvePageComponent(
            `./Pages/${name}.vue`,
            import.meta.glob("./Pages/**/*.vue")
        ),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(ZiggyVue)
            .use(ElementPlus)
            .component("multiselect", Multiselect)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
