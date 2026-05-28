/** @type {import('tailwindcss').Config} */
export default {
  content: [
    "./resources/**/*.blade.php",
    "./resources/**/*.js",
    "./resources/**/*.vue",
  ],
  theme: {
    extend: {
      colors: {
        teal: {
          DEFAULT: '#009688',
          dark:    '#00695C',
          light:   '#E0F2F1',
          50:      '#E0F2F1',
          500:     '#009688',
          700:     '#00695C',
        },
      },
      fontFamily: {
        sans: ['Inter', 'Poppins', 'system-ui', 'sans-serif'],
      },
    },
  },
  plugins: [],
}
