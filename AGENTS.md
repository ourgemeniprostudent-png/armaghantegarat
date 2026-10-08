# Armaghan Tejarat Vatan — current working site

This repository contains the approved WordPress implementation transferred from the local project at theme version 1.7.0. Read `docs/HANDOFF-fa.md` and `README.md` first.

## Content and design

- Speak Persian with the owner. Public UI is Persian, RTL, using the bundled Peyda font; no decorative English labels and no hamza characters in public copy.
- The full company name is «ارمغان تجارت وطن». The business is a direct wholesale importer of agricultural/food products, including containerized maritime freight; it is not a café. Give coffee about half the product emphasis and the other groups (rice, nuts/dried fruit, spices, legumes) the other half.
- Black and gold dominate. Midnight navy and restrained red accents also belong to the brand.
- From 1.12, the owner requires spatial, expansive interior compositions, not repeated rounded photo/text cards. Use Midnight Navy #0D1B2A, Black #0B0B0B, Gold #C39A5B and the owner-approved burgundy; no green/teal UI. Light surfaces derive from gold. Natural photo colors remain natural. Contact uses an original coded 3D golden cargo sculpture; About/Cooperation use full backgrounds, Journal uses an editorial layout, forms use light workspaces. Never invent rankings, capacity or facility ownership. Native palette, motion intensity and feature controls remain editable.
- Header: transparent at the top of the video hero, black after scrolling; gold emblem always, white text. Preserve the fullscreen mobile navigation and borderless, normal-weight controls.
- Hero uses the unchanged current video and a dark overlay. From 1.10.2 the owner removed the startup loader: show the page immediately and let native video stream progressively. Never fetch the entire video into a Blob before playback. No video pause button.
- Product gallery has five groups and manual finite scroll steps, no autoplay. Do not replace it with a generic grid.
- Cooperation gallery has four people-free realistic images, a large central card, tilted smaller side cards, manual scrolling/dragging, and an image dialog. The gallery background is flat dark, not a blurred photo.
- The standalone panoramic ingredients image was removed from the homepage. Do not re-add it.
- Lower home sections: about, oversized brand typography, illustrated journal covers, and direct contact. Duplicate inquiry invitations and empty media teaser were removed. Keep that balance.
- Use existing approved assets. New imagery must not include people and should look realistic. Do not invent company statistics, certifications, origins, stock availability, or actual facility ownership.

## Architecture and runtime

- `theme/vatan-authority/`: editable theme. `plugin/vatan-core/`: editable plugin and real persistent inquiry flow.
- Preserve WordPress multisite: Persian main site; future English under `/en/`, currently hidden/pending.
- `.runtime/` is generated, ignored state: WordPress, SQLite, credentials, logs. Never add it to Git. Public seed content is in `content/public-site.json`; no original users, passwords or inquiry records are transferred.
- `vendor/` pins the tested WordPress 7.1.3, WP-CLI 2.12.0, SQLite integration 3.0.2, and Persian translations. Retain upstream licenses. Avoid unsolicited dependency upgrades.
- Start with `bash scripts/cloud-setup.sh` on a fresh Linux environment, then `bash scripts/start.sh`. If prepared, run `bash scripts/setup.sh` instead. Use the repository start skill for Cloud startup.
- Keep the existing workspace URL on repeated setup. URL changes in multisite require a deliberate migration; setup never destroys existing data.
- Network email notifications are disabled in the seed. Do not enable real sends during testing.

## Verification

- PHP lint changed PHP files; `node --check` changed JS; `python3 scripts/smoke.py` with the development server running.
- Test the routes and behavior affected by a change. For visual edits, review the result at desktop and small mobile widths using available tools; report unavailable browser validation accurately.
- Commit source changes and export intentionally changed public content; saved Cloud state is not a replacement for Git. Avoid modifying the approved design while simply setting up the environment.

## Section editing from 1.8

- Read `docs/EDITING-fa.md`. Every page, article and product category exposes native section editing for text, images and videos. Maintain this standard for future work.
- Public defaults live in `plugin/vatan-core/content/sections-fa.json`; saved post/term metadata takes precedence. Never reset it on setup or upgrade. Keep the plugin deployable in a normal WordPress plugins directory.
- Static previews come from `scripts/export-preview.py`; only anonymous public HTML and referenced media belong on gh-pages. Never export form tokens, credentials, database files or private inquiries.

## Interior art direction from 1.9

- Interior-only assets in assets/art-direction.css/js and inc/art-direction.php carry the new design; keep approved home assets separate.
- Preserve ivory reading surfaces for journal/articles, native section fields, meaningful reduced-motion and no-JS behavior.
- Route diagrams are conceptual, not actual shipping routes or office coordinates. Use transferred company/contact facts; original reference documents are absent from this checkout.
- Native article bodies and public seed content must stay aligned when intentionally editing copy. Preserve revisions and existing administrator edits on upgrade; never reset the database.

## Interior authority edition 1.12

- Current renderers: `inc/authority-edition.php`, `assets/authority-edition.css/js`. Build CSS using `scripts/build-assets.py`; do not edit generated bundles. The old contact-only stylesheet is not loaded.
- The cargo scene projects world-space geometry to Canvas and approaches along the viewing depth axis; it settles after entrance. No external model/library is loaded. Preserve static SVG fallback, reduced-motion behavior, pause offscreen/hidden and capped mobile frame rate.
- Hero backgrounds use native `background_image` fields (empty falls back to section image); preserve editor metadata and video controls.

- From 1.12.1 the outlined first word («تجارت») of the home manifesto is an explicit gold exception, using the native gold palette setting. Preserve this exception when changing burgundy styles.

## Approved About page 1.13

- The owner approved the standalone realistic About prototype and asked to integrate it into WordPress. `inc/about-editorial.php` renders eight native editable sections; `assets/about-editorial.css` is scoped to `.at-about`. Preserve the full viewport photographic opening with overlaid Persian copy, broad photographic stories, gold-tinted reading surfaces, product accordions, principles, process and office contact.
- `assets/about/` contains the three owner-approved newly generated realistic illustrations dedicated to About. Two include anonymous workers as part of the approved composition; this is the explicit exception to the earlier no-people rule. They are not actual staff or owned facilities, and the editable image disclosure must remain accurate. New pages should have their own relevant imagery, rather than reusing the About pictures or the old ship/scale scenes.
- Shared header: transparent at the top on all public pages, black after scroll or while the mobile menu is open. The existing home video logic controls the same header state for every page. `assets/shared-header.css` is appended last to all three bundles. The native `transparent_header` setting switches the interior header behavior; the approved home remains unchanged.
- Future Contact, Journal and other redesigns are reviewed as separate standalone prototypes before integration. This release does not redesign those pages.

## Approved Contact / Journal / Inquiry 1.14

- Owner approved the V2 standalone prototypes for integration. `inc/approved-interiors.php` renders their scoped `inc/approved/*.html` section templates through the native catalog; `assets/approved-interiors.css/js` and `approved-validation.js` provide the reviewed layout and enhancement. These templates contain escaped, allowlisted tokens, never executable editorial HTML.
- Contact and Inquiry keep warm workspaces with boxed, legible inputs. Inquiry is product/contact/review with explicit product name and quantity/unit. The native lead handler persists unit, delivery time, packaging and contact topic; retain nonce, consent, honeypot, idempotency and restored-error data. Product-detail forms remain supported. No-JS clients see all stages and can submit normally.
- Journal and article reading surfaces are explicitly pure white at the owner's request, overriding earlier ivory preferences. All eight real posts have dedicated realistic editable covers; retain real article URLs, native bodies, topics, filtering and feature controls. Do not redesign Home journal cards.
- `assets/approved/` holds page-specific Contact/Journal/Inquiry scenes and eight editorial illustrations. They are conceptual, not actual owned facilities or product-availability evidence. Keep the office-image disclosure accurate. Original photo files are retained outside the release; video bytes remain unchanged.
- Public GitHub forms remain view-only; export strips credentials/tokens and cannot create tracking codes. Hide the mobile floating CTA while an enhanced form is in view so it does not cover its controls. Global header/footer/menu editing remains native.


## Shared corners from 1.17.1

- The owner requires one 8px corner radius throughout the public interface: cards, images, forms, buttons, covers, players and dialogs. `assets/corner-radius.css` applies the shared `--ui-radius` in every bundle before shared-header.css. The native theme setting `vatan_ui_radius` defaults to 8 and remains editable. New interface surfaces must follow this token instead of introducing a different radius. Decorative vector geometry retains its actual shape.


## Display modes from 1.18

- The owner requires dark by default, with separate light and monochrome options. `inc/appearance.php` and `assets/appearance.js/css` provide a direct sliding dark/light switch and separate monochrome button (inside the mobile navigation), with per-origin local preference. Never replace the slider with a popup selection menu. Never follow the OS theme automatically or invert the whole page. Preserve photo hero overlays, brand composition, motion, native editing and the 8px radius in every mode.
- New interface surfaces must support the `--display-paper/surface/ink/muted/accent/line` tokens, readable small copy, controls and focus states. Monochrome tones leaf content and photo overlays without filtering fixed-position container ancestors. Keep file bytes and image/video quality unchanged. The global identity section edits the four display labels; the native appearance_switch feature controls visibility.
