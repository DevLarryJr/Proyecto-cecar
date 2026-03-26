/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ["./*.html"],
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

