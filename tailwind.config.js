import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
    content: [
        './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
        './storage/framework/views/*.php',
        './resources/views/**/*.blade.php',
        './resources/js/**/*.vue',
    ],

    darkMode: 'class',

    theme: {
        extend: {
            fontFamily: {
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                hazak: {
                    black: '#0B0D10',
                    graphite: '#14181D',
                    surface: '#1C222A',
                    border: 'rgba(255, 255, 255, 0.08)',
                    blue: {
                        DEFAULT: '#1E6BFF',
                        hover: '#4A89FF',
                    },
                    critical: {
                        DEFAULT: '#FF3B3B',
                        hover: '#FF5C5C',
                    },
                },
            },
            transitionDuration: {
                150: '150ms',
                200: '200ms',
            },
        },
    },

    plugins: [forms],
};
