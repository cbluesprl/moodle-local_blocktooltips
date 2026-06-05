# Block tooltips (local_blocktooltips)

A Moodle local plugin that helps course editors work with blocks in two ways:

1. **Tooltips in the "Add a block" modal** — shows a configurable info icon next to
   each block, so editors immediately understand what a block does before adding it.
2. **Student visibility indicator** — displays a read-only eye icon on every block
   while editing a course, telling the editor whether that block is currently
   *visible* or *hidden* for students.

No personal data is stored. Both features are only active for users who can edit
blocks (`moodle/block:edit`), and only while editing mode is turned on.

## Features

### Block tooltips
- One configurable description per installed block plugin.
- An accessible info icon (`role="img"`, `aria-label`, keyboard focusable) is added
  next to each block entry in the **Add a block** popup.
- Descriptions are plain text (`PARAM_TEXT`) and rendered as Bootstrap tooltips.
- Blocks without a configured description are left untouched.

### Student visibility indicator
- A read-only eye icon is shown in each block's control area on course pages.
  - 👁️ green eye → visible for students.
  - 🚫 red crossed-out eye → hidden for students.
- Visibility is computed server-side by reproducing Moodle's rendering-time logic
  for a representative student (capability checks, page-format match,
  `block_positions` flag, and empty-content detection).
- Restricted to **course contexts** — on `/my`, dashboards or profiles each user
  has their own blocks, so a single "what a student sees" answer is not meaningful.

## Screenshots

Admin settings — one description field per installed block:

![Settings page](docs/screenshots/01-settings.png)

Tooltip shown next to a block in the "Add a block" popup:

![Tooltip in the Add a block popup](docs/screenshots/02-tooltip.png)

Student visibility indicator on course blocks (visible vs hidden):

![Student visibility eye icon](docs/screenshots/03-visibility.png)

## Requirements

- Moodle 4.5 (build `2024100700`) or later.
- Supported branches: Moodle 4.5 → 5.0 (`[405, 500]`).
- Tested with PHP 8.2.

## Installation

1. Copy the plugin into the `local/blocktooltips` directory of your Moodle site:

   ```
   <moodleroot>/local/blocktooltips
   ```

2. Log in as an administrator and go to **Site administration** to trigger the
   upgrade, or run the CLI upgrade:

   ```
   php admin/cli/upgrade.php
   ```

3. Purge caches if needed (`php admin/cli/purge_caches.php`).

## Configuration

Go to **Site administration → Plugins → Local plugins → Block tooltips**.

The settings page lists every installed block (sorted alphabetically by its
translated name) with a text area. Enter a short description for each block you
want to document. Leave a field empty to show no tooltip for that block.

## Usage

### Tooltips
1. Configure descriptions as explained above.
2. On any page, turn **Edit mode** on.
3. Open the **Add a block** popup — an info icon appears next to every block that
   has a configured description. Hover or focus it to read the tooltip.

### Student visibility
1. Open a **course** and turn **Edit mode** on.
2. Each block displays an eye icon in its control area indicating whether the
   block is currently visible or hidden for students.

## Privacy

This plugin does not store any personal data. It implements the Moodle Privacy API
`null_provider`.

## Coding standards

The plugin passes the official Moodle validators with no errors or warnings:

```
phpcs --standard=moodle local/blocktooltips/        # via local_codechecker
php local/moodlecheck/cli/moodlecheck.php --path=local/blocktooltips
```

## License

This program is free software: you can redistribute it and/or modify it under the
terms of the GNU General Public License v3 (or later) as published by the Free
Software Foundation.

See <http://www.gnu.org/licenses/>.

## Author

CBlue SRL — <support@cblue.be>
