# AGENTS.md

Guidance for AI coding agents working in this repository. Read `README.md` for the user-facing overview; this file covers how the code is put together and how changes are made.

## What this is

`ucsc-primary-sites` is a WordPress plugin that adds site-specific behavior to the three primary UC Santa Cruz sites: **www**, **news**, and **events**. One codebase is installed on all three sites. Each site decides at runtime which feature modules to boot, based on its `home_url()`.

- Requires WordPress 6.4+ and PHP 8.0+ (typed properties, return types, and `?Type` are used throughout).
- ACF Pro is needed only by the news `Media_Coverage` module.
- Sites are hosted on Pantheon. URL patterns match production domains and Pantheon multidev/test URLs (`*-www-ucsc.pantheonsite.io`, etc.).
- Local development happens inside a Local (by Flywheel) site, with this plugin under `wp-content/plugins/`.

## Architecture

```
ucsc-primary-sites.php        Bootstrap: constants, Composer autoload, update checker,
                              PSR-4-ish autoloader, plugins_loaded boot
includes/
  class-module.php            abstract Module: get_name(), boot(), can_load()
  class-site-registry.php     Site groups → URL patterns + module classes; wildcard matching
  class-site-loader.php       Resolves current URL, instantiates and boots matched modules
  class-admin-settings.php    Settings > UCSC Primary Sites: per-group auto/on/off overrides
sites/
  registry.php                The one place groups and their modules are declared
  shared/modules/             Modules that load on all three sites (e.g. Robots_Txt)
  www/modules/, www/css/
  news/modules/, news/blocks/, news/scripts/
  events/modules/
uninstall.php                 Deletes the ucsc_primary_sites_overrides option only (leaves CPT content)
```

Boot flow, on `plugins_loaded`:

1. A `Site_Registry` is created and passed to the `ucsc_primary_sites_register` action. `sites/registry.php` hooks in and calls `add_group( $slug, $label, $patterns, $modules )` for each group.
2. In admin, `Admin_Settings` is booted. It is always available, so overrides can be set on any site.
3. `Site_Loader::load()` gets the URL from the `ucsc_primary_sites_current_url` filter (default `home_url()`). It collects modules from every matching group and dedupes them. For each module it checks `can_load()`, then calls `boot()`. When done it fires `ucsc_primary_sites_loaded`.

Matching rules (`Site_Registry::url_matches_pattern`): the scheme and trailing slashes are ignored, and matching is case-insensitive. `*` matches zero or more non-slash characters. `**` is greedy and also matches slashes. The admin override in the `ucsc_primary_sites_overrides` option wins over URL matching: `on` forces the group on and `off` forces it off. When a group has no override, URL matching decides ("auto").

### Autoloading and naming

The autoloader in the main plugin file maps namespace segments to directories. Segments are lowercased and `_` becomes `-`. Class names become `class-<name>.php`. It looks in `includes/` first, then in the plugin root.

| Class | File |
|---|---|
| `UCSC\PrimarySites\Admin_Settings` | `includes/class-admin-settings.php` |
| `UCSC\PrimarySites\Sites\News\Modules\Media_Coverage` | `sites/news/modules/class-media-coverage.php` |
| `UCSC\PrimarySites\Sites\Shared\Modules\Robots_Txt` | `sites/shared/modules/class-robots-txt.php` |

If the file name, directory, and namespace do not line up exactly, the class will not load. When `WP_DEBUG` is on, `Site_Loader` logs `Module class not found` in that case. Composer's autoloader (`vendor/autoload.php`) is used only for third-party packages.

## Adding or changing a module

1. Create `sites/<group>/modules/class-<name>.php` with namespace `UCSC\PrimarySites\Sites\<Group>\Modules`. Extend `UCSC\PrimarySites\Module`.
2. Implement `get_name()` and `boot()`. Register all hooks inside `boot()`, and do no work in the constructor. Override `can_load()` to skip the module when a dependency is missing. For example, `Media_Coverage` checks `function_exists( 'acf_add_local_field_group' )`.
3. Add the class to the group's module list in `sites/registry.php`, with a `use` import. If two groups have classes with the same short name, alias them (`Dashboard_Widgets as News_Dashboard_Widgets`).
4. If the behavior should apply on all three sites, put it in `sites/shared/` and the `shared` group. Do not copy it into each group.
5. Update the "Site groups" table in `README.md`. If the module has notable behavior, add a section about it.

Conventions seen in existing modules:

- Use `array( $this, 'method' )` callbacks and public handler methods. Keep one feature per module class.
- Long modules are split into sections with `// ─── Section ───` comment rules.
- Several modules say in their docblocks where they were migrated from (`Migrated from ucsc-news-functionality/...`). Keep these notes.
- The text domain is `ucsc-primary-sites`.
- Load assets with `UCSC_PRIMARY_SITES_URL` / `UCSC_PRIMARY_SITES_DIR`, and use `UCSC_PRIMARY_SITES_VERSION` as the cache-busting version.
- Editor JS (for example `sites/news/scripts/variations.js`) is plain, unbuilt JS that is enqueued directly with `wp.*` globals as dependencies. `build/` is empty and no source currently goes through `wp-scripts build`.
- ACF blocks are declared with `block.json` under `sites/<group>/blocks/<block>/` and registered from their module.
- Admin code checks the nonce and `current_user_can( 'manage_options' )`, and sanitizes all input. Follow the same pattern.

## Coding standards and tooling

- PHP follows WordPress Coding Standards: tabs, spaces inside parentheses, `array()` instead of `[]`, Yoda conditions, and a docblock on every method.
  - `composer install`, then `composer lint` / `composer lint-fix` (phpcs / phpcbf with WPCS 2.x). The repo has no `phpcs.xml` ruleset, so pass `--standard=WordPress` if phpcs does not pick it up.
- JS/CSS: `npm install`, then `npm run lint:js`, `npm run lint:css`, `npm run format` (`@wordpress/scripts`).
- The repo has no automated tests. To check a change, activate the plugin in the local WP site. Use the settings page to force a group **On**, or filter `ucsc_primary_sites_current_url`, so the module boots locally. Then check the behavior (for example, visit `/robots.txt`).
- `vendor/`, `node_modules/`, `composer.lock`, and `*.zip` are gitignored. `package-lock.json` is committed because CI needs it.

## Commits, branches, and releases

- Commit messages use Conventional Commits with a gitmoji, and optionally a scope. Examples: `feat(all): ✨ Add robots.txt ...`, `fix: 🐛 Restrict domains to Pantheon`, `chore: ...`, `docs: ...`, `build: ...`. Put `Fixes #N` at the end when the commit closes an issue. `commit-and-tag-version` builds `CHANGELOG.md` from these messages, so the type matters.
- Branches are named `knice/issue<N>` after the GitHub issue (`ucsc/ucsc-primary-sites`). Changes reach `main` through a PR.
- Releases: `npm run release` (`commit-and-tag-version`) bumps the version in `package.json`, `package-lock.json`, and the `Version:` header in `ucsc-primary-sites.php` (through `wp-plugin-version-updater.js`). It also updates `CHANGELOG.md` and creates a `chore(release): X.Y.Z` commit and a `vX.Y.Z` tag. Pushing the tag runs `.github/workflows/release.yml`, which calls the shared `ucsc/actions` release workflow. That workflow runs `composer install --no-dev`, builds the zip, and attaches it to a GitHub Release.
  - Do not edit version numbers or `CHANGELOG.md` by hand. Do not tag a release unless asked.
  - The version updater regex accepts only single-digit `X.Y.Z` components. Fix it before the version reaches 1.10.0 or 2.0.10.
- Installed sites update from GitHub Releases through `yahnis-elsts/plugin-update-checker` (`main` branch, release assets enabled). The optional `UCSC_PRIMARY_SITES_GITHUB_TOKEN` constant authenticates these requests. Because the release zip must contain `vendor/`, keep `require_once vendor/autoload.php` working.
- The `files` list in `package.json` controls what `wp-scripts plugin-zip` packages. When you add a new top-level directory, add it to that list.
