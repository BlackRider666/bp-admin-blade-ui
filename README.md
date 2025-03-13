# Black-Paradise/AdminBladeUI

## Introduction

`Black-Paradise/AdminBladeUI` Blade UI for `Black-Paradise/LaravelAdmin`

## Installation

To install the package, run the following command:

```sh
composer require black-paradise/admin-blade-ui
```
## Example Vite config
```js
export default defineConfig({
    resolve: {
        alias: {
            'vue': 'vue/dist/vue.esm-bundler.js',
        },
    },
    plugins: [
        laravel({
            input: [
                'resources/js/app.js',
                'resources/js/vendor/bpadmin/main.js', // package file
                'resources/css/app.css',
            ],
            refresh: true,
        }),
        vue(),
    ],
    build: {
        rollupOptions: {
            input: {
                app: 'resources/js/app.js',                      
                admin: 'resources/js/vendor/bpadmin/main.js',     // JS for package
            },
        },
    },
});
```
## Roadmap
- Support for custom components.
- Additional customization options for pages.

## License
This package is open-source and licensed under the MIT license.

## Support

If you would like to support this package, you can do so on [Patreon](https://patreon.com/BlackParadise?utm_medium=unknown&utm_source=join_link&utm_campaign=creatorshare_creator&utm_content=copyLink).
