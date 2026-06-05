# Changelog

All notable changes to this plugin are documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

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

[1.0.0]: https://github.com/cbluesprl/moodle-local_blocktooltips/releases/tag/v1.0.0
