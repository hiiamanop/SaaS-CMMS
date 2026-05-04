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
            colors: {
                brand: {
                    DEFAULT: '#10b981', // emerald-500
                    50: '#ecfdf5', // emerald-50
                    600: '#059669', // emerald-600
                    dark: '#064e3b', // emerald-900
                }
            },
            fontFamily: {
                sans: ['Outfit', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
