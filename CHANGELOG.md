# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.1.0] - 2026-06-24

### Added
- Support for Moodle 5.0 and 5.1 through the new `MOODLE_500_STABLE` and
  `MOODLE_501_STABLE` branches (each branch declares its own `$plugin->supported`).

### Changed
- Tooltip initialisation is now Bootstrap 4 and Bootstrap 5 compatible. The jQuery
  `$(el).tooltip()` call (which throws under the Bootstrap 5 build shipped with
  Moodle 5.0+) was removed; icons now carry both the `data-toggle` and
  `data-bs-toggle` attributes and rely on the theme's delegated initialisation,
  with the `title` attribute as a graceful fallback.
- Removed the jQuery dependency from both AMD modules.

## [1.0.0] - 2026-03-16

### Added
- Configurable tooltip descriptions per block, displayed as an accessible info
  icon in the "Add a block" modal.
- Read-only student visibility indicator (eye icon) shown on each block in course
  contexts while editing, computed server-side from a representative student.
- Admin settings page listing every installed block with a text area for its
  description.
- English and French language packs.
- Privacy API `null_provider` (no personal data stored).
- Hook callback on `before_footer_html_generation` to inject the AMD modules only
  for users with `moodle/block:edit` in editing mode.

[1.1.0]: https://github.com/cbluesprl/moodle-local_blocktooltips/releases/tag/v1.1.0
[1.0.0]: https://github.com/cbluesprl/moodle-local_blocktooltips/releases/tag/v1.0.0
