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
