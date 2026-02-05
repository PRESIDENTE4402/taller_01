import defaultTheme from 'tailwindcss/defaultTheme';
import forms from '@tailwindcss/forms';

/** @type {import('tailwindcss').Config} */
export default {
  content: [
    './vendor/laravel/framework/src/Illuminate/Pagination/resources/views/*.blade.php',
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
  ],
  theme: {
    extend: {
      fontFamily: {
        sans: ['Prompt', 'sans-serif'],
      },
      colors: {
        bmw: {
          black: '#0a0a0a',
          dark: '#121212',
          gray: '#1E1E1E',
          blue: '#003399',
          lightblue: '#00A3DA',
          red: '#DF0012',
        }
      },
      boxShadow: {
        'glow': '0 0 15px rgba(0, 163, 218, 0.3)',
      }
    },
  },
  plugins: [require("daisyui")],
  daisyui: {
    themes: ["light", "dark"],
  },
}
