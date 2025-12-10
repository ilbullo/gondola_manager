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
                sans: ['Figtree', ...defaultTheme.fontFamily.sans],
            },
        },
    },

    plugins: [forms],

    safelist: [
    // Singole classi
    'bg-red-500',
    'bg-green-500',
    'bg-blue-500',
    'bg-yellow-500',
    
    // Pattern: ad esempio tutti i bg-* e text-*
    { pattern: /^bg-(amber|red|green|blue|yellow|orange|gray|purple|pink|indigo|emerald|teal)-\d{3}$/ },
    { pattern: /^text-(amber|red|green|blue|yellow|orange|gray|purple|pink|indigo|emerald|teal)-\d{3}$/ },
    { pattern: /^ring-(amber|red|green|blue|yellow|orange|gray|purple|pink|indigo|emerald|teal)-\d{3}$/ },
    
    // Classi che cambiano in base a condizioni dinamiche
    'hidden',
    'block',
  ],
};
