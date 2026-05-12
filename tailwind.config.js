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
                sans: ['Inter', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                'uber-black': '#000000',
                'uber-white': '#ffffff',
                'hover-gray': '#e2e2e2',
                'hover-light': '#f3f3f3',
                'chip-gray': '#efefef',
                'body-gray': '#4b4b4b',
                'muted-gray': '#afafaf',
            },
            boxShadow: {
                'uber-card': '0 4px 16px rgba(0, 0, 0, 0.12)',
                'uber-float': '0 2px 8px rgba(0, 0, 0, 0.16)',
                'uber-press': 'inset 0 0 0 999px rgba(0, 0, 0, 0.08)',
            },
            keyframes: {
                fadeIn: {
                    '0%': { opacity: '0' },
                    '100%': { opacity: '1' },
                },
                fadeInUp: {
                    '0%': { opacity: '0', transform: 'translateY(20px)' },
                    '100%': { opacity: '1', transform: 'translateY(0)' },
                }
            },
            animation: {
                'fade-in': 'fadeIn 0.6s ease-out forwards',
                'fade-in-up': 'fadeInUp 0.6s ease-out forwards',
            }
        },
    },

    plugins: [forms],
};
