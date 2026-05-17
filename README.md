# Tablo Twig Template

Internal Kirby plugin for [Tablo Themes](https://tablo.supply/) that swaps Kirby's template component to render Twig files alongside PHP. It implements Twig support exactly the way Tablo themes expect, borrowing ideas from the now-abandoned Twig integration by amteich and its maintained fork by wearejust, while remaining a Tablo-specific solution rather than a competitor. If you need a plug-and-play Twig plugin for general Kirby projects, use the Twig Templates plugin from wearejust instead: https://plugins.getkirby.com/wearejust/twig.

## Install Notes

```sh
# 1. Add the plugin as a submodule
git submodule add https://github.com/lemmon/kirby-plugin-tablo-twig site/plugins/tablo-twig

# 2. Install Twig runtime for template rendering
composer require twig/twig

# 3. Optional: install Kirby CLI helpers
composer require getkirby/cli
```

## Optional: Twig JSX component syntax

The plugin can register [twig-jsx](https://github.com/lemmon/twig-jsx) so templates may use JSX-like tags (for example `<Button />`) that compile to `{% include %}` / `{% embed %}`. Install it in the project root:

```sh
composer require lemmon/twig-jsx
```

Then enable it in site config:

```php
return [
    'tablo.twig.jsx.enabled' => true,
];
```

Place component templates under `site/snippets/components/` (for example `Button.twig`). Call-site expressions use JSX-style braces, for example `<Button href={page.url} />`; inside components, every prop arrives in the `props` bag.
