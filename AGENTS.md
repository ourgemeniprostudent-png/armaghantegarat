# Armaghan Tejarat Vatan — current working site

This repository contains the approved WordPress implementation transferred from the local project at theme version 1.7.0. Read `docs/HANDOFF-fa.md` and `README.md` first.

## Content and design

- Speak Persian with the owner. Public UI is Persian, RTL, using the bundled Peyda font; no decorative English labels and no hamza characters in public copy.
- The full company name is «ارمغان تجارت وطن». The business is a direct wholesale importer of agricultural/food products, including containerized maritime freight; it is not a café. Give coffee about half the product emphasis and the other groups (rice, nuts/dried fruit, spices, legumes) the other half.
- Black and gold dominate. Midnight navy and restrained red accents also belong to the brand.
- Header: transparent at the top of the video hero, black after scrolling; gold emblem always, white text. Preserve the fullscreen mobile navigation and borderless, normal-weight controls.
- Hero uses the current compressed video, a dark overlay and a startup loader; no video pause button.
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
