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
                // Link / DESIGN.md official tokens
                primary: {
                    DEFAULT: '#00C767',
                    foreground: '#011E0F',
                },
                secondary: {
                    DEFAULT: '#171717',
                    foreground: '#FFFFFF',
                },
                tertiary: '#525252',
                neutral: '#E5E5E5',
                surface: '#FFFFFF',
                'on-surface': '#171717',
                'accent-strong': '#011E0F',
                'muted-surface': '#F7F7F7',
                error: '#D92D20',

                // Backward-compatible brand mappings updated to #00C767
                brand: {
                    DEFAULT: '#00C767',
                    50: '#E6FBF0',
                    100: '#C7F7DE',
                    200: '#94F0C0',
                    500: '#00C767',
                    600: '#00B05B',
                    700: '#008E49',
                    dark: '#011E0F',
                },
            },
            borderRadius: {
                DEFAULT: '10px',
                sm: '8px',
                md: '10px',
                lg: '18px',
                xl: '28px',
            },
            fontFamily: {
                sans: ['Inter', 'Outfit', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],
};
