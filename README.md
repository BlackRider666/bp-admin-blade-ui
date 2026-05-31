# BPAdmin Blade UI (v3)

Blade + Tailwind CSS + Alpine.js UI layer for [BPAdmin](../bp-laravel-admin).

## Installation

This package is installed automatically when you require `black-paradise/laravel-admin` with Blade UI.

After installation, publish the compiled CSS/JS assets:

```bash
php artisan vendor:publish --tag=bpadmin-assets
```

This copies `public/bpadmin/bpadmin.css` and `public/bpadmin/bpadmin.js` to your project's `public/vendor/bpadmin/` directory.

## Tailwind CSS Integration (optional)

If your project uses Tailwind CSS, you can integrate BPAdmin's views into your Tailwind build instead of relying solely on the pre-compiled CSS.

Add to your `tailwind.config.js`:

```js
const bpadmin = require('./vendor/black-paradise/laravel-admin-blade-ui-next/bpadmin-tailwind-plugin.cjs')

module.exports = {
    content: [
        ...bpadmin.content,          // scans BPAdmin Blade views
        './resources/**/*.blade.php',
        // ... your other paths
    ],
    plugins: [
        ...bpadmin.plugins,          // registers --bpadmin-* CSS variables in :root
        // ... your other plugins
    ],
}
```

The plugin registers CSS custom properties in `:root` that control BPAdmin's color scheme:

| Variable | Default | Description |
|----------|---------|-------------|
| `--bpadmin-primary` | `#30A488` | Primary accent color |
| `--bpadmin-primary-hover` | `#278a74` | Primary hover state |
| `--bpadmin-surface` | `#1A1C2A` | Card/panel background |
| `--bpadmin-app-bg` | `#0F1117` | Page background |
| `--bpadmin-border` | `#272B3D` | Border color |
| `--bpadmin-muted` | `#7C7A96` | Muted text color |

To customize the theme, override these variables in your CSS:

```css
:root {
    --bpadmin-primary: #6366f1;       /* indigo */
    --bpadmin-primary-hover: #4f46e5;
}
```

## CSS Isolation

BPAdmin styles are fully isolated from your project:

- All Tailwind utilities are scoped to `#bpadmin-app` (the admin panel root element)
- CSS preflight (reset) is disabled — your project's global styles are not affected
- Custom color tokens use the `bp-` prefix (`bg-bp-primary`, `bg-bp-surface`, etc.) to avoid name collisions

## Building Assets (package development)

```bash
npm install
npm run build:all    # compile bpadmin.css + bpadmin.js
npm run dev          # watch CSS changes
npm run dev:js       # watch JS changes
```

## Alpine.js

The package bundles Alpine.js. If your project already uses Alpine, BPAdmin's bundle will detect `window.Alpine` and skip initialization to avoid conflicts.
