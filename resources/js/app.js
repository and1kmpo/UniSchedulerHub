import "./bootstrap";
import "../css/app.css";
import "@fortawesome/fontawesome-free/css/all.min.css";
import { createApp, h } from "vue";
import { createInertiaApp } from "@inertiajs/vue3";
import { resolvePageComponent } from "laravel-vite-plugin/inertia-helpers";
import { ZiggyVue } from "../../vendor/tightenco/ziggy/dist/vue.m";

// Importa Vuetify y su tema
import { createVuetify } from "vuetify";
/* import "vuetify/styles"; // Importa los estilos de Vuetify */
import * as components from "vuetify/components";
import * as directives from "vuetify/directives";

const appName = import.meta.env.VITE_APP_NAME || "Laravel";

// Configura Vuetify
const vuetify = createVuetify({
    components,
    directives,
});

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
            .use(vuetify)
            .mount(el);
    },
    progress: {
        color: "#4B5563",
    },
});
