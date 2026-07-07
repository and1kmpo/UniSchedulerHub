import { usePage } from "@inertiajs/vue3";

function valueFromPath(source, path) {
    return String(path)
        .split(".")
        .reduce((value, key) => (value && value[key] !== undefined ? value[key] : undefined), source);
}

export function useTranslations() {
    const page = usePage();

    const t = (key, fallback = null) => {
        const value = valueFromPath(page.props.i18n?.messages ?? {}, key);

        return value ?? fallback ?? key;
    };

    return {
        t,
        locale: page.props.i18n?.locale ?? "en",
        supportedLocales: page.props.i18n?.supported ?? [],
    };
}
