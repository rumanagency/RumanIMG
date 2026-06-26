# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Plugin Identity

- **Name:** Ruman IMG
- **Slug:** rumanimg
- **Developer:** Saleh — Ruman Agency
- **Email:** saleh@ruman.sa
- **Website:** ruman.sa
- **Text Domain:** rumanimg

## What This Plugin Does

Photographers who build WordPress sites often post large batches of images (wedding coverage, event shoots, etc.) by uploading to external image hosts and pasting their embed codes into posts. The plugin solves two problems with that workflow:

1. **Embed codes are inconsistent** across services and often reference medium-sized images instead of originals.
2. **There is no fast way to duplicate a post** once a photographer has set up a post template with their preferred structure.

The plugin provides a custom Gutenberg block — the **Image Parser Block** — that accepts a raw paste of embed HTML from any supported image host, normalises it into a clean, consistent output format, and shows the full-size images in a gallery.

---

## Supported Image Hosts & URL Rules

The parser must handle these five services. The key question for each is whether the `src` in the embed is already the full-size image or needs a fix.

| Service | Page domain | Image CDN | URL fix needed? |
|---|---|---|---|
| **imgur** | `imgur.com` | `i.imgur.com` | None — src is already original |
| **ibb / imgbb** | `ibb.co` | `i.ibb.co` | None — src is already original (AJAX-resolved for plain page URLs) |
| **freeimage.host** | `freeimage.host` | `iili.io` | **Yes** — strip `.md.` (or `.th.`, `.sm.`) size token from filename |
| **postimg.cc** | `postimg.cc` | `i.postimg.cc` | None — src is already original |
| **imgbox** | `imgbox.com` | `thumbs2.imgbox.com` → `images2.imgbox.com` | **Yes** — swap hostname + `_t.`→`_o.` suffix (AJAX-resolved for plain page URLs) |

**freeimage.host fix (critical):**
```
https://iili.io/CAGYfNj.md.jpg  →  https://iili.io/CAGYfNj.jpg
```
Regex: replace `/\.(md|th|sm|lg)(\.[a-z]+)$/i` with `$2` in the `src` URL.

**Normalised output format** (one line per image, matching imgur's legacy style):
```html
<a href="{page-url}"><img src="{full-size-src}" title="source: {site-name}" /></a>
```
`{site-name}` is the human-readable host name: `imgur.com`, `ibb.co`, `freeimage.host`, `postimg.cc`.

---

## Architecture

### Entry point & bootstrap
- **`rumanimg.php`** — Plugin header, constants (`RUMANIMG_VERSION`, `RUMANIMG_PATH`, `RUMANIMG_URL`, `RUMANIMG_BASENAME`), PHP version guard, activation/deactivation hooks, instantiates `Rumanimg` and calls `run()`.
- **`includes/class-rumanimg.php`** — Wires all hooks: loads admin/public submodules, registers the Gutenberg block, sets locale.
- **`includes/class-rumanimg-activator.php`** / **`class-rumanimg-deactivator.php`** — Activation/deactivation side-effects (default options, flush rewrite rules).

### Gutenberg block — Image Parser
Lives in `src/blocks/image-parser/`. Built with `@wordpress/scripts`.

- **`block.json`** — Block metadata (name `rumanimg/image-parser`, category `media`, attributes).
- **`index.js`** — Calls `registerBlockType`.
- **`edit.js`** — Editor UI: textarea for paste input, auto-parse on change, preview panel showing images, copy-output button, InspectorControls sidebar with quick-links to all four image hosts.
- **`save.js`** — Returns `null` (dynamic block; PHP renders on the frontend).
- **`parser.js`** — Pure JS module with no dependencies. Parses the raw HTML string, extracts all `<a href>/<img src>` pairs, detects the host, applies URL fixes, returns an array of `{ pageUrl, imgUrl, siteName }` objects.
- **`render.php`** — PHP callback for `register_block_type`. Outputs the normalised `<a><img></a>` list for the frontend.

Build output lands in `build/blocks/image-parser/`.

### Post Duplicate
- **`includes/class-rumanimg-duplicate.php`** — Hooks into `post_row_actions` (list table) and `post_submitbox_misc_actions` (edit screen) to add a "Duplicate" link. Uses `wp_insert_post` + `get_post_meta` to copy the post and all its meta. Duplicated post is saved as `draft`.

### Admin
- **`admin/class-rumanimg-admin.php`** — Registers the admin menu page, settings page, and enqueues admin-only CSS/JS (only on this plugin's pages).
- **`admin/views/main.php`** / **`settings.php`** — Template files included by the admin class. No logic, only HTML.

### Public
- **`public/class-rumanimg-public.php`** — Enqueues the compiled `build/` CSS/JS for the frontend gallery styles.

### Assets
- `assets/images/icon.svg` — Plugin icon (green Ruman "R" mark, `#6dd708`).
- `assets/images/logo.svg` — Full horizontal logo (white wordmark + green icon), used in admin UI headers.

---

## Build System

```bash
# Install dependencies (once)
npm install

# Development (watch mode, hot rebuild)
npm run start

# Production build
npm run build
```

`@wordpress/scripts` handles webpack; no custom `webpack.config.js` needed unless block count grows. Built files output to `build/`.

---

## Key Conventions

- PHP 7.4+ minimum. Typed properties and return types throughout.
- All classes prefixed `Rumanimg_`, snake_case methods and hook callbacks.
- Enqueue handles: `rumanimg-{name}` (e.g., `rumanimg-editor`, `rumanimg-admin`).
- All user-facing strings: `__( '', 'rumanimg' )` / `esc_html__()`.
- Nonces on every form submission and AJAX call.
- No raw DB queries without `$wpdb->prepare()`.
- `parser.js` must remain a pure function module — no WP globals, testable in isolation.
