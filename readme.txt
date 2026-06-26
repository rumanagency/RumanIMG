=== Ruman IMG ===
Contributors: rumanagency
Donate link: https://ruman.sa
Tags: gallery, images, photographer, imgur, image-parser
Requires at least: 5.8
Tested up to: 6.7
Requires PHP: 7.4
Stable tag: 1.0.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A Gutenberg block for photographers: paste embed codes from any image host and get a clean full-resolution gallery instantly. Includes one-click post duplication.

== Description ==

Photographers who build WordPress sites often shoot hundreds of images per session, upload them to external image hosts, and then paste embed codes into their posts. Two problems make this painful:

1. **Embed codes are inconsistent** — every host outputs different HTML, and many link to medium-sized images instead of the originals.
2. **No fast way to duplicate posts** — once a photographer builds a perfect post template, there's no built-in way to copy it for the next shoot.

**Ruman IMG** solves both problems.

= Image Parser Block =

Add the **Ruman IMG — Image Parser** block to any post, paste your embed codes or a plain list of image URLs, and the block instantly:

* Detects all images across any supported host
* Fixes URLs to point to the full-resolution original (not thumbnails or medium sizes)
* Shows a live thumbnail preview inside the editor
* Lets you **Paste into post** — inserts a clean HTML block directly below
* Lets you **Copy output code** — copies the normalised HTML to your clipboard

= Supported Image Hosts =

| Host | URL Fix |
|---|---|
| imgur.com | None needed |
| imgBB (ibb.co) | Resolved server-side |
| freeimage.host | Strips size token (`.md.`, `.th.`) |
| postimg.cc | None needed |
| imgbox.com | Converts thumbnail to original |

Works with both **HTML embed codes** (copy-paste from the host's share page) and **plain URL lists** (one URL per line).

= Post Duplicate =

A **Duplicate** link appears on every post in the list table and in the publish meta box. One click creates a full copy — including all block content and post meta — saved as a draft, and opens it ready to edit.

= Bilingual =

The plugin UI is available in **English** and **Arabic**. Choose your preferred language from the plugin settings page, independent of the site locale.

== Installation ==

= From WordPress.org (recommended) =

1. Go to **Plugins → Add New** in your WordPress admin
2. Search for **Ruman IMG**
3. Click **Install Now** then **Activate**

= Manual upload =

1. Download the latest `rumanimg-vX.X.X.zip` from the [GitHub releases page](https://github.com/rumanagency/RumanIMG/releases)
2. Go to **Plugins → Add New → Upload Plugin**
3. Choose the zip file and click **Install Now**
4. Activate the plugin

= From source (developers) =

1. Clone the repository into `wp-content/plugins/rumanimg/`
2. Run `npm install` then `npm run build`
3. Activate the plugin

== Frequently Asked Questions ==

= The block doesn't appear in the editor =

Make sure the Classic Editor plugin is **not** active — Ruman IMG requires the block editor (Gutenberg). Also confirm your WordPress version is 5.8 or higher.

= My images are showing at medium size, not the original =

Paste the embed code exactly as provided by the image host's share page. The plugin automatically strips size tokens for freeimage.host and converts imgbox thumbnails to originals. For imgBB and imgbox plain page URLs, the plugin fetches the original via a server-side request — this requires the WordPress site to be able to make outbound HTTP requests.

= Does it work with direct CDN image URLs? =

Yes. You can paste a list of direct image URLs (one per line) alongside HTML embed codes. Both formats are detected automatically.

= Can I add more image hosts? =

Yes. The `HOST_MAP` in `parser.js` and the AJAX resolver in `class-rumanimg.php` are designed to be extended. See the [GitHub repository](https://github.com/rumanagency/RumanIMG) for details.

= Is the post duplicate feature safe? =

Yes. Every duplicate request is protected by a WordPress nonce tied to the specific post ID, and requires the `edit_posts` capability.

== Screenshots ==

1. The Image Parser block inside the Gutenberg editor — paste area, live thumbnail preview, and action buttons.
2. The block output on the frontend — full-resolution images in a flexible wrap layout.
3. The "Duplicate" link in the post list table.
4. The plugin admin page showing post stats and quick links to image hosting sites.
5. The settings page with language selector and developer info.

== Changelog ==

= 1.0.1 =
* Added readme.txt for WordPress.org plugin directory submission
* Switched license from MIT to GPL-2.0-or-later

= 1.0.0 =
* Initial release
* Image Parser Gutenberg block with support for imgur, imgBB, freeimage.host, postimg.cc, imgbox
* HTML embed code and plain URL list parsing
* Full-resolution URL fixing (freeimage size tokens, imgbox thumbnails)
* Server-side AJAX resolver for imgBB and imgbox page URLs
* Paste-into-post and copy-output buttons
* Post duplicate feature with nonce protection
* Bilingual admin UI (Arabic + English) with language switcher
* Branded admin dashboard with stats and quick links

== Upgrade Notice ==

= 1.0.0 =
Initial release.
