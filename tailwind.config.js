/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.html", "./*.php", "./php/**/*.php"],
  safelist: [
    'bg-gray-200', 'bg-gray-400', 'text-gray-400', 'text-brand-main', 'text-brand-gray',
    'bg-white', 'border-4', 'border-brand-main', 'shadow-sm', 'bg-brand-main', 
    'text-white', 'shadow-lg', 'shadow-brand-soft', 'animate-pulse'
  ],
  theme: {
    extend: {
      colors: {
        brand: {
          main: 'var(--green-main)',
          soft: 'var(--green-soft)',
          light: 'var(--green-light)',
          hover: 'var(--green-hover)',
          bg: 'var(--bg-main)',
          dark: 'var(--text-dark)',
          gray: 'var(--text-gray)',
        }
      }
    },
  },
  plugins: [],
}

