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
| Shared | all three site patterns               | Robots Generator                           |

The **Shared** group exists for functionality that should apply uniformly across all UCSC primary sites. Add a module to its list in `sites/registry.php` when the behavior is not site-specific.

### Robots Generator (Shared)

Appends UCSC-specific rules to WordPress's generated `robots.txt` via the `robots_txt` filter. Two lists are maintained as class constants in `sites/shared/modules/class-robots-txt.php`:

- `LIMITED_AGENTS` — AI training crawlers (e.g. `GPTBot`, `ClaudeBot`, `CCBot`). Each receives a `Crawl-delay` directive set by the `CRAWL_DELAY` constant.
- `BANNED_AGENTS` — aggressive SEO scrapers and known abusive crawlers (e.g. `AhrefsBot`, `SemrushBot`, `MJ12bot`). Each receives `Disallow: /`.

The module deliberately excludes AI *search* bots (e.g. `OAI-SearchBot`, `PerplexityBot`, `ChatGPT-User`), which provide user-initiated retrieval rather than training data. If a site is set to discourage indexing in **Settings > Reading**, this module leaves WP's default output unchanged.

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

