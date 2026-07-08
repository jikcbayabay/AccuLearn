/** @type {import('tailwindcss').Config} */
export default {
  content: ['./index.html', './src/**/*.{js,jsx}'],
  theme: {
    extend: {
      colors: {
        brand: {
          blue: '#24598a',
          'blue-700': '#1d4870',
          'blue-50': '#eef3f9',
          green: '#72b579',
          'green-700': '#5a9461',
          'green-50': '#eef7ef',
        },
        mastery: {
          mastered: '#72b579',
          developing: '#d9a441',
          needs: '#c97064',
        },
        ink: {
          900: '#0f172a',
          700: '#1f2937',
          500: '#6b7280',
          300: '#cbd5e1',
          200: '#e5e7eb',
          100: '#f1f5f9',
          50: '#f7f8fa',
        },
      },
      fontFamily: {
        sans: ['Inter', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        mono: ['JetBrains Mono', 'ui-monospace', 'monospace'],
      },
      boxShadow: {
        card: '0 1px 2px rgba(15,23,42,.04), 0 4px 12px rgba(15,23,42,.04)',
        pop: '0 8px 24px rgba(15,23,42,.10), 0 2px 6px rgba(15,23,42,.06)',
      },
      borderRadius: { xl: '14px', '2xl': '18px' },
    },
  },
  plugins: [],
};
