/** @type {import('tailwindcss').Config} */
module.exports = {
  content: ['./resources/views/**/*.blade.php'],
  important: '#bpadmin-app',
  corePlugins: { preflight: false },
  theme: {
    extend: {
      colors: {
        'bp-primary':  {
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
        sans: ['Assistant', 'ui-sans-serif', 'system-ui', 'sans-serif'],
        display: ['Sora', 'Assistant', 'ui-sans-serif', 'sans-serif'],
        mono: ['"JetBrains Mono"', 'ui-monospace', 'SFMono-Regular', 'monospace'],
      },
      boxShadow: {
        'glow-primary': '0 0 0 1px rgba(48,164,136,0.35), 0 8px 24px -6px rgba(48,164,136,0.35)',
        'ring-primary': '0 0 0 3px rgba(48,164,136,0.18)',
        'card':        '0 1px 0 0 rgba(255,255,255,0.03) inset, 0 20px 40px -20px rgba(0,0,0,0.6)',
        'card-hover':  '0 1px 0 0 rgba(255,255,255,0.05) inset, 0 28px 60px -24px rgba(0,0,0,0.7)',
        'inner-top':   'inset 0 1px 0 0 rgba(255,255,255,0.04)',
      },
      backgroundImage: {
        'grid-dots': 'radial-gradient(rgba(124,122,150,0.12) 1px, transparent 1px)',
        'primary-sheen': 'linear-gradient(135deg, #30A488 0%, #3EC5A3 50%, #30A488 100%)',
        'surface-sheen': 'linear-gradient(180deg, rgba(255,255,255,0.04) 0%, rgba(255,255,255,0) 60%)',
        'aura-primary': 'radial-gradient(60% 60% at 50% 0%, rgba(48,164,136,0.25) 0%, rgba(48,164,136,0) 70%)',
      },
      backgroundSize: {
        'grid-16': '16px 16px',
        'grid-24': '24px 24px',
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
        'fade-up':  'fade-up 0.45s cubic-bezier(0.22, 1, 0.36, 1) both',
        'fade-in':  'fade-in 0.35s ease-out both',
        'drift-slow': 'drift 14s ease-in-out infinite',
        'drift-slower': 'drift 22s ease-in-out infinite',
      },
    },
  },
}
