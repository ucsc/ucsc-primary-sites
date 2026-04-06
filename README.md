# UCSC Primary Sites

WordPress plugin that loads site-specific functionality for the primary UC Santa Cruz websites: **www**, **news**, and **events**.

## How it works

The plugin uses a registry/loader/module architecture:

1. **Site Registry** -- maps named site groups (e.g. `www`, `news`, `events`) to URL patterns and module classes.
2. **Site Loader** -- resolves the current site URL against the registry and boots matching modules.
3. **Modules** -- self-contained feature classes that register hooks, post types, blocks, and other WordPress functionality.

Sites are matched automatically by URL pattern (e.g. `https://news.ucsc.edu`). An admin settings page under **Settings > UCSC Primary Sites** allows overriding any group to always-on, always-off, or auto (URL-based).

## Site groups

| Group  | Patterns                              | Modules                                    |
|--------|---------------------------------------|--------------------------------------------|
| WWW    | `www.ucsc.edu`, `*www-ucsc*`          | Front Page, Dashboard Widgets              |
| News   | `news.ucsc.edu`, `*news-ucsc*`        | Media Coverage, Dashboard Widgets, Block Variations |
| Events | `events.ucsc.edu`, `*events-ucsc*`    | Block Bindings, Users Events Column        |

## Adding a new module

1. Create a class in the appropriate `sites/<group>/modules/` directory that extends `UCSC\PrimarySites\Module`.
2. Implement `get_name()` and `boot()`. Optionally override `can_load()` to check dependencies.
3. Add the class to the group's module list in `sites/registry.php`.

## Requirements

- WordPress 6.4+
- PHP 8.0+
- [ACF Pro](https://www.advancedcustomfields.com/pro/) (required by the Media Coverage module in `news`; other modules load without it)

## License

This plugin is licensed under the [GPL-2.0](LICENSE).
