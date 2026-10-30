# Upgrade guide

## 3.0 → 3.1 (core 1.1, laravel-admin 3.1)

The canonical, step-by-step guide (U-01..U-19, Known limits) lives in the `black-paradise/laravel-admin` package:
`bp-laravel-admin/UPGRADE.md`. Blade-UI specific items:

| Id | Change |
|----|--------|
| U-01 | Requires `laravel-admin ^3.1` and `core-admin ^1.1`. |
| U-05 | `BladeEntityPresenter::actionResult(ActionResult, EntityDefinitionContract)`; subclasses overriding it must adopt the new signature. |
| U-14 | Views read `$ui` (`EntityUiState`) instead of `$definition->isCreatable()`; `@js` escaping fix in `theme-toggle` / `translatable`. Republish: `php artisan vendor:publish --tag=bpadmin-views --force`. |
| U-15 | Repeater focus fix, remove-file checkbox and actions UI ship in the JS/CSS bundle. Republish: `php artisan vendor:publish --tag=bpadmin-assets --force` (publishes to `public/vendor/bpadmin`). |

Published copies in `resources/views/vendor/bpadmin` and `public/vendor/bpadmin` shadow the package; stale copies hide these fixes.
