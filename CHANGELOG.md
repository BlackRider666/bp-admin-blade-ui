# Changelog

All notable changes to `black-paradise/admin-blade-ui` are documented here.
Format: [Keep a Changelog](https://keepachangelog.com/en/1.1.0/). Breaking entries cite the id in `bp-laravel-admin/UPGRADE.md`.

## [3.1.0] - 2026-10-30

### Added
- `EntityUiState` (`$ui`) view-model; row / bulk / entity actions UI with confirm and input modals.
- Remove-file checkbox for optional file fields (`{field}__clear`).

### Changed
- **BREAKING (U-01)** Requires `laravel-admin ^3.1` and `core-admin ^1.1`.
- **BREAKING (U-05)** `BladeEntityPresenter::actionResult(ActionResult, EntityDefinitionContract)`.
- **BREAKING (U-14)** Views read `$ui` instead of `$definition->isCreatable()`; republish views.
- **BREAKING (U-15)** Rebuilt JS/CSS bundle; republish assets (`vendor:publish --tag=bpadmin-assets --force`).

### Fixed
- Repeater first field focus deferred with `$nextTick` (belongs-to dropdown no longer opens closed).
- `@js` escaping in `theme-toggle` and `translatable` views (U-14).
- Unresolvable action class error raised while rendering names the entity definition.
- `bp_locale()` resolves the locale provider from the container on every call (Octane-safe; no function-static cache).
- `translatable` input view no longer duck-types its field with `method_exists`.
