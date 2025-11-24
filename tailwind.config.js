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
    
    // Pattern: ad esempio tutti i bg-* e text-*
    { pattern: /^bg-(red|green|blue|yellow|gray|purple|pink|indigo)-\d{3}$/ },
    { pattern: /^text-(red|green|blue|yellow|gray|purple|pink|indigo)-\d{3}$/ },
    
    // Classi che cambiano in base a condizioni dinamiche
    'hidden',
    'block',
  ],
};
