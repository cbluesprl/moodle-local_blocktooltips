# Contributing

Thanks for your interest in improving **Block tooltips** (`local_blocktooltips`).

## Reporting bugs and requesting features

Please use the GitHub issue tracker and pick the relevant template:

<https://github.com/cbluesprl/moodle-local_blocktooltips/issues>

Include your Moodle version, PHP version, plugin version and steps to reproduce.

## Submitting changes

1. Fork the repository and create a branch from the default branch.
2. Make your change with clear, focused commits.
3. Open a Pull Request describing **what** changes and **why**.

## Coding standards

This plugin follows the [Moodle coding style](https://moodledev.io/general/development/policies/codingstyle).
Before opening a PR, make sure the official validators pass with no errors or warnings:

```
phpcs --standard=moodle local/blocktooltips/        # via local_codechecker
php local/moodlecheck/cli/moodlecheck.php --path=local/blocktooltips
```

If you change the JavaScript in `amd/src/`, rebuild the minified modules
(`grunt amd`) and commit the updated files in `amd/build/`.

## Language strings

- All user-facing text must use `get_string()` with strings defined in
  `lang/en/local_blocktooltips.php`.
- Keep language string keys in alphabetical order (enforced by the Moodle
  validators).
- Update the French pack (`lang/fr/`) when you add or change a string.

## License

By contributing, you agree that your contributions are licensed under the
GNU GPL v3 or later, like the rest of the plugin.