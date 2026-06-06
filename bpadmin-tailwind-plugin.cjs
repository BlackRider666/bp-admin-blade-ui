/**
 * BPAdmin Tailwind Plugin
 *
 * Usage in host project's tailwind.config.js:
 *   const bpadmin = require('./vendor/black-paradise/admin-blade-ui/bpadmin-tailwind-plugin.cjs')
 *   module.exports = {
 *     content: [...bpadmin.content, './resources/**\/*.blade.php'],
 *     plugins: [...bpadmin.plugins],
 *     theme: { extend: bpadmin.theme.extend },
 *   }
 *
 * The plugin registers BPAdmin CSS variables for both dark (default) and light themes.
 * Active theme is selected by the `data-theme` attribute on the <html> element.
 * To customize the theme, override these variables in your CSS (under :root or :root[data-theme="light"]).
 */
const plugin = require('tailwindcss/plugin')

module.exports = {
  content: [
    './vendor/black-paradise/admin-blade-ui/resources/views/**/*.blade.php',
  ],
  plugins: [
    plugin(function ({ addBase }) {
      addBase({
        ':root, :root[data-theme="dark"]': {
          '--bpadmin-primary':       '#30A488',
          '--bpadmin-primary-hover': '#278a74',
          '--bpadmin-primary-soft':  '#3EC5A3',
          '--bpadmin-primary-deep':  '#1E6F5C',
          '--bpadmin-app-bg':        '#0F1117',
          '--bpadmin-surface':       '#1A1C2A',
          '--bpadmin-surface-2':     '#21243A',
          '--bpadmin-elevated':      '#262A44',
          '--bpadmin-border':        '#272B3D',
          '--bpadmin-border-soft':   '#30344C',
          '--bpadmin-text':          '#D1CFDF',
          '--bpadmin-text-strong':   '#E4E2EF',
          '--bpadmin-text-weak':     '#A6A4BE',
          '--bpadmin-muted':         '#7C7A96',
          // gray scale (matches bp-gray-* tokens)
          '--bpadmin-gray-50':  '#181929',
          '--bpadmin-gray-100': '#1E2035',
          '--bpadmin-gray-200': '#272B3D',
          '--bpadmin-gray-300': '#383651',
          '--bpadmin-gray-400': '#7B7A96',
          '--bpadmin-gray-500': '#9E9CB4',
          '--bpadmin-gray-600': '#B8B6CC',
          '--bpadmin-gray-700': '#D1CFDF',
          '--bpadmin-gray-800': '#E4E2EF',
          '--bpadmin-gray-900': '#F1EFF9',
          // inputs
          '--bpadmin-input-bg':       '#10122A',
          '--bpadmin-input-bg-focus': '#121430',
        },
        ':root[data-theme="light"]': {
          '--bpadmin-primary':       '#30A488',
          '--bpadmin-primary-hover': '#278a74',
          '--bpadmin-primary-soft':  '#1E6F5C',
          '--bpadmin-primary-deep':  '#1E6F5C',
          '--bpadmin-app-bg':        '#F5F6FA',
          '--bpadmin-surface':       '#FFFFFF',
          '--bpadmin-surface-2':     '#F0F2F8',
          '--bpadmin-elevated':      '#FFFFFF',
          '--bpadmin-border':        '#E2E5EE',
          '--bpadmin-border-soft':   '#EDEFF5',
          '--bpadmin-text':          '#2A2D3D',
          '--bpadmin-text-strong':   '#13141C',
          '--bpadmin-text-weak':     '#6B6E82',
          '--bpadmin-muted':         '#6B6E82',
          // gray scale — inverted for light theme
          '--bpadmin-gray-50':  '#F8F9FC',
          '--bpadmin-gray-100': '#F0F2F8',
          '--bpadmin-gray-200': '#E2E5EE',
          '--bpadmin-gray-300': '#CBCFDC',
          '--bpadmin-gray-400': '#9499AC',
          '--bpadmin-gray-500': '#6B6E82',
          '--bpadmin-gray-600': '#4D5063',
          '--bpadmin-gray-700': '#363849',
          '--bpadmin-gray-800': '#1F2030',
          '--bpadmin-gray-900': '#13141C',
          // inputs
          '--bpadmin-input-bg':       '#FFFFFF',
          '--bpadmin-input-bg-focus': '#FAFBFE',
        },
      })
    }),
  ],
  theme: {
    extend: {
      colors: {
        'bp-primary': {
          DEFAULT: 'var(--bpadmin-primary)',
          hover:   'var(--bpadmin-primary-hover)',
          soft:    'var(--bpadmin-primary-soft)',
          deep:    'var(--bpadmin-primary-deep)',
        },
        'bp-surface':    'var(--bpadmin-surface)',
        'bp-surface-2':  'var(--bpadmin-surface-2)',
        'bp-elevated':   'var(--bpadmin-elevated)',
        'bp-app-bg':     'var(--bpadmin-app-bg)',
        'bp-border':     'var(--bpadmin-border)',
        'bp-border-soft':'var(--bpadmin-border-soft)',
        'bp-muted':      'var(--bpadmin-muted)',
        'bp-input-bg':   'var(--bpadmin-input-bg)',
        'bp-gray': {
          50:  'var(--bpadmin-gray-50)',
          100: 'var(--bpadmin-gray-100)',
          200: 'var(--bpadmin-gray-200)',
          300: 'var(--bpadmin-gray-300)',
          400: 'var(--bpadmin-gray-400)',
          500: 'var(--bpadmin-gray-500)',
          600: 'var(--bpadmin-gray-600)',
          700: 'var(--bpadmin-gray-700)',
          800: 'var(--bpadmin-gray-800)',
          900: 'var(--bpadmin-gray-900)',
        },
      },
      fontFamily: {
        sans:    ['Assistant', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Sora', 'Assistant', 'ui-sans-serif', 'sans-serif'],
        mono:    ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      backgroundSize: {
        'grid-16': '16px 16px',
        'grid-24': '24px 24px',
      },
      boxShadow: {
        'glow-primary': '0 0 0 1px rgba(48,164,136,0.35), 0 8px 24px -6px rgba(48,164,136,0.35)',
        'ring-primary': '0 0 0 3px rgba(48,164,136,0.18)',
        'card':        '0 1px 0 0 rgba(255,255,255,0.03) inset, 0 20px 40px -20px rgba(0,0,0,0.6)',
        'card-hover':  '0 1px 0 0 rgba(255,255,255,0.05) inset, 0 28px 60px -24px rgba(0,0,0,0.7)',
        'inner-top':   'inset 0 1px 0 0 rgba(255,255,255,0.04)',
      },
      backgroundImage: {
        'grid-dots':      'radial-gradient(rgba(124,122,150,0.12) 1px, transparent 1px)',
        'primary-sheen':  'linear-gradient(135deg, #30A488 0%, #3EC5A3 50%, #30A488 100%)',
        'surface-sheen':  'linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0) 60%)',
        'aura-primary':   'radial-gradient(60% 60% at 50% 0%, rgba(48,164,136,0.25) 0%, rgba(48,164,136,0) 70%)',
      },
      keyframes: {
        'fade-up': {
          '0%':   { opacity: '0', transform: 'translateY(8px)' },
          '100%': { opacity: '1', transform: 'translateY(0)'  },
        },
        'fade-in': {
          '0%':   { opacity: '0' },
          '100%': { opacity: '1' },
        },
        'drift': {
          '0%, 100%': { transform: 'translate3d(0,0,0) scale(1)' },
          '50%':      { transform: 'translate3d(30px,-20px,0) scale(1.05)' },
        },
        'sheen': {
          '0%':   { backgroundPosition: '-200% 0' },
          '100%': { backgroundPosition: '200% 0'  },
        },
      },
      animation: {
        'fade-up':      'fade-up 0.45s cubic-bezier(0.22, 1, 0.36, 1) both',
        'fade-in':      'fade-in 0.35s ease-out both',
        'drift-slow':   'drift 14s ease-in-out infinite',
        'drift-slower': 'drift 22s ease-in-out infinite',
      },
    },
  },
}
