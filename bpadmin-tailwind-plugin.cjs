/**
 * BPAdmin Tailwind Plugin
 *
 * Usage in host project's tailwind.config.js:
 *   const bpadmin = require('./vendor/black-paradise/laravel-admin-blade-ui-next/bpadmin-tailwind-plugin.cjs')
 *   module.exports = {
 *     content: [...bpadmin.content, './resources/**\/*.blade.php'],
 *     plugins: [...bpadmin.plugins],
 *   }
 *
 * The plugin registers BPAdmin CSS variables for both dark (default) and light themes.
 * Active theme is selected by the `data-theme` attribute on the <html> element.
 * To customize the theme, override these variables in your CSS (under :root or :root[data-theme="light"]).
 */
const plugin = require('tailwindcss/plugin')

module.exports = {
  content: [
    './vendor/black-paradise/laravel-admin-blade-ui-next/resources/views/**/*.blade.php',
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
        },
      })
    }),
  ],
}
