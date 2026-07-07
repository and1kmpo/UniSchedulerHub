import defaultTheme from "tailwindcss/defaultTheme";
import forms from "@tailwindcss/forms";
import typography from "@tailwindcss/typography";

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        "./vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php",
        "./vendor/laravel/jetstream/**/*.blade.php",
        "./storage/framework/views/*.php",
        "./resources/views/**/*.blade.php",
        "./resources/js/**/*.vue",
    ],

    theme: {
        extend: {
            colors: {
                brand: {
                    50: "#EFF6FF",
                    100: "#DBEAFE",
                    200: "#BFDBFE",
                    300: "#93C5FD",
                    400: "#60A5FA",
                    500: "#3B82F6",
                    600: "#2563EB",
                    700: "#1D4ED8",
                    800: "#1E40AF",
                    900: "#1E3A8A",
                    950: "#172554",
                    DEFAULT: "#2563EB",
                },
                indigo: {
                    50: "#EFF6FF",
                    100: "#DBEAFE",
                    200: "#BFDBFE",
                    300: "#93C5FD",
                    400: "#60A5FA",
                    500: "#3B82F6",
                    600: "#2563EB",
                    700: "#1D4ED8",
                    800: "#1E40AF",
                    900: "#1E3A8A",
                    950: "#172554",
                },
                "brand-dark": "#1D4ED8",
                ink: "#0F172A",
                "dark-bg": "#09090B",
                surface: "#FFFFFF",
                "surface-dark": "#18181B",
                "border-light": "#E2E8F0",
                "border-dark": "#27272A",
                accent: "#06B6D4",
                success: "#10B981",
                warning: "#F59E0B",
                danger: "#EF4444",
            },
            fontFamily: {
                sans: ["Geist", "Inter", ...defaultTheme.fontFamily.sans],
                mono: ["Geist Mono", "JetBrains Mono", ...defaultTheme.fontFamily.mono],
            },
        },
    },

    darkMode: "class",

    plugins: [forms, typography],
};
