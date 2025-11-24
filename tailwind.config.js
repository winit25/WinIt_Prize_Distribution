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
                sans: ['Montserrat Alternates', 'Montserrat', ...defaultTheme.fontFamily.sans],
            },
            colors: {
                winit: {
                    // Primary brand colors from Winit dashboard
                    primary: '#4313F7',
                    'primary-dark': '#3510c9',
                    secondary: '#23A3D6',
                    accent: '#17F7B6',
                    'accent-alt': '#13F7B5',
                    pink: '#F71355',
                    lime: '#C7F713',
                    dark: '#010133',
                    'dark-alt': '#01011B',
                    // Neutral colors
                    gray: {
                        50: '#F7F7F9',
                        100: '#FAFAFB',
                        200: '#EFEEF2',
                        300: '#E7E6EC',
                        400: '#D0D5DD',
                        500: '#9899AD',
                        600: '#667085',
                        700: '#4C4D61',
                    },
                },
            },
        },
    },

    plugins: [forms],
};
