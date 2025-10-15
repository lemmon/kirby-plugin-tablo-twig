# Repository Guidelines

## Project Structure & Module Organization
The plugin mirrors standard Kirby layout. `index.php` registers the plugin, CLI commands, and swaps Kirby’s template component. Core logic lives in `src/Template.php`, which boots Twig, locates template/snippet directories across plugins, and renders `.twig` or `.php` files. Keep Twig templates inside your site’s `site/templates` and `site/snippets` folders so the loader can auto-discover them. Any new PHP helpers should stay under `src/` with namespaces rooted at `Tablo\`.

## Build, Test, and Development Commands
There is no build step; ensure the plugin directory sits inside `site/plugins`. During development run your Kirby site locally (e.g. `php -S localhost:8000 kirby/router.php`). Clear cached Twig output whenever you change template structure with `php kirby twig:flush`, which purges `storage/cache/twig`. If you alter dependencies, refresh Kirby’s autoloader via `composer dump-autoload` from the project root.

## Coding Style & Naming Conventions
Follow PSR-12 for PHP files: four-space indentation, one class per file, and descriptive camelCase method names. Keep Twig namespacing consistent with `templates` and `snippets` aliases already configured by the loader. Use early returns for guard clauses and prefer Kirby helper functions (`kirby()`, `option()`) over global state. New components should be declared via `Kirby::plugin(...)` in `index.php` for discoverability.

## Testing Guidelines
Automated tests are not yet defined. When contributing, validate behavior by loading representative pages in a Kirby sandbox site and confirm both PHP and Twig templates render intact. Pay special attention to cache invalidation by reproducing `twig:flush` and ensuring compiled templates refresh. If you add runtime configuration, document manual test steps in the pull request so other maintainers can reproduce them.

## Commit & Pull Request Guidelines
The repository has no public Git history yet; adopt Conventional Commit headers (for example, `feat: add snippet loader alias`). Group related changes into a single commit and detail any Kirby/Twig impacts in the body. Pull requests should describe the motivation, summarize testing (commands run or pages inspected), and include before/after screenshots when UI output changes. Link to related Kirby issues or discussions to provide context for reviewers.

## Cache & Configuration Tips
Twig cache paths are derived from `kirby()->root('cache')`. When troubleshooting, inspect `storage/cache/twig` and ensure the directory is writable. Enable Kirby’s debug mode (`'debug' => true` in `config.php`) to surface Twig errors and leverage the registered `dump()` Twig function for scoped inspection within templates.

## Documentation Hygiene
Stick to ASCII punctuation in docs (avoid em dashes) so diffs stay predictable. Use emojis sparingly; a bit of personality is fine, but skip emoji-per-list formatting.
