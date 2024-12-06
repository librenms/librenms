module.exports = {
  prefix: 'tw-',
  content: [
      './storage/framework/views/*.php',
      './resources/**/*.blade.php',
      './resources/**/*.js',
      './resources/**/*.vue',
      './html/js/boot.js',
  ],
  darkMode: 'class',
  theme: {
    extend: {
      colors: {
        dark: {
          'gray-100': '#4f565d',
          'gray-200': '#3e444c',
          'gray-300': '#353a41',
          'gray-400': '#2e3338',
          'gray-500': '#272b30',
          'white-100': '#f9fafb',
          'white-200': '#c8c8c8',
          'white-300': '#bebfbf',
          'white-400': '#acb6bf',
        }
      },
      borderWidth: {
        '0.5': '0.5px',
      },
      boxShadow: {
        'inner-glow': 'inset 0 0 11px rgba(0, 0, 0, 0.1)',
      }
    },
    screens: {
      'sm': '576px',
      'md': '768px',
      'lg': '992px',
      'xl': '1200px',
    }
  },
  plugins: [],
}
