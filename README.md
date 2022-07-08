Page Render Find/Replace
------------------------

This module applies replacements specified via TextformatterFindReplace to rendered page content. This may be preferably in cases where page content is generated from numerous fields, or generated dynamically, which can make utilizing the textformatter module on a per field basis inconvenient -- or even impossible.

Since all heavy lifting is done by TextformatterFindReplace (a module by Ryan Cramer), Page Render Find/Replace requires said module to be installed.

See https://github.com/ryancramerdesign/TextformatterFindReplace for more details.

## Usage

1) Install TextformatterFindReplace and PageRenderFindReplace
2) Configure replacements via TextformatterFindReplace config page
4) Configure applicable pages via PageRenderFindReplace config page
3) Optionally: enable logging via PageRenderFindReplace config page

The idea behind logging is to catch any lingering remains of old strings (URLs/domains during migration etc.) so that they can be (manually) replaced in page content. Using this module adds some overhead and alters page markup, so it's not something you should enable on a permanent basis.

## Installing

This module can be installed by downloading or cloning the PageRenderFindReplace directory into the /site/modules/ directory of your site. Alternatively you can install the module using Composer: `composer require teppokoivula/page-render-find-replace`.

## License

This project is licensed under the Mozilla Public License Version 2.0.
