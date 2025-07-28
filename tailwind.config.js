import defaultTheme from 'tailwindcss/defaultTheme';
import forms        from '@tailwindcss/forms';
import plugin       from 'tailwindcss/plugin';

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
      colors: {
        primary:    '#1F985C',
        secondary:  '#CA9F29',
        tertiary:   '#EA8101',
        button:     {
          DEFAULT: '#58181F',
          hover:   '#7A212A', // un poco más claro para hover
          active:  '#3F0F10', // más oscuro al presionar
        },
      },
    },
  },
  plugins: [
    forms,
    // opcional: exponer variables CSS
    plugin(({ addBase, theme }) => {
      addBase({
        ':root': {
          '--color-primary':   theme('colors.primary'),
          '--color-secondary': theme('colors.secondary'),
          '--color-tertiary':  theme('colors.tertiary'),
          '--color-button':    theme('colors.button.DEFAULT'),
        },
      });
    }),
  ],
};
