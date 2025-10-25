# Tablo Twig Template

Internal Kirby plugin that swaps Kirby’s template component to render Twig files alongside PHP. It implements Twig support exactly the way Tablo themes expect, borrowing ideas from the now-abandoned Twig integration by amteich and its maintained fork by wearejust, while remaining a Tablo-specific solution rather than a competitor. If you need a plug-and-play Twig plugin for general Kirby projects, use the Twig Templates plugin from wearejust instead: https://plugins.getkirby.com/wearejust/twig.

## Install Notes
```sh
# 1. Add the plugin as a submodule
git submodule add https://github.com/lemmon/kirby-plugin-tablo-twig site/plugins/tablo-twig

# 2. Install Twig runtime for template rendering
composer require twig/twig

# 3. Optional: install Kirby CLI helpers
composer require getkirby/cli
```
