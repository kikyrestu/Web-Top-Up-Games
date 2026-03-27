import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
    ],

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', 'Plus Jakarta Sans', 'Figtree', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                dark: {
                    900: '#0B0F19',
                    800: '#151C2C',
                    700: '#1F293F',
                    600: '#2A3752',
                },
                brand: {
                    500: '#4F46E5',
                    400: '#6366F1',
                },
                accent: {
                    500: '#06B6D4',
                },
                up: {
                    darkest: '#0c0f17',
                    nav: '#1d2235',
                    body: '#111620',
                    card: '#242a40',
                    border: '#343b54',
                    textmuted: '#8a94ad',
                    yellow: '#f49e0b',
                    yellowhover: '#d98b08',
                },
            },
        },
    },

    plugins: [forms],
};
