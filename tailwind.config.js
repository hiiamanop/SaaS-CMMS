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
                    DEFAULT: '#697FAE',
                    foreground: '#FFFFFF',
                },
                secondary: {
                    DEFAULT: '#171717',
                    foreground: '#FFFFFF',
                },
                tertiary: '#525252',
                neutral: '#E5E5E5',
                surface: '#FFFFFF',
                'on-surface': '#171717',
                'accent-strong': '#FFFFFF',
                'muted-surface': '#F7F7F7',
                error: '#D92D20',

                // Backward-compatible brand mappings updated to #697FAE
                brand: {
                    DEFAULT: '#697FAE',
                    50: '#F2F5FA',
                    100: '#E5EAF4',
                    200: '#C7D4E8',
                    500: '#697FAE',
                    600: '#586D9B',
                    700: '#475980',
                    dark: '#1E293B',
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
