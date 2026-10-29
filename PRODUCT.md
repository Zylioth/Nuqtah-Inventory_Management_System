# Product

<!-- impeccable:product-schema 1 -->

## Platform

web

## Users

Primary: students and teaching staff at Institut Tahfiz Al-Quran Sultan Haji Hassanal Bolkiah (ITQSHHB) who need to borrow institute IT equipment — laptops, projectors, cables, storage, peripherals. They arrive between classes, on shared campus machines and phones, with a specific item in mind and little patience for paperwork.

Secondary: the institute IT team, who approve requests, hand over and receive equipment, tag assets, and produce audit reports. They live in the admin area, not on the landing page.

## Product Purpose

Nuqtah replaces paper borrowing forms with a digital IT inventory and loan workflow. A borrower signs in, browses live stock, adds items to a cart-like basket with a pickup time, presents a request ID at the desk, and returns by a tracked deadline. The IT team keeps every asset, tag, request, and movement in one database. Success is a request submitted in seconds and an inventory record that matches the shelf.

## Positioning

It is the institute's own borrow-and-return desk made digital: a shop-like catalog bound to a real asset register with per-item tags, handover, return, and audit reporting. A generic asset tracker cannot do the borrower-facing cart; a generic e-commerce template cannot produce an audited handover trail.

## Operating Context

- Runs on campus intranet (XAMPP: Apache, PHP, MySQL) at `C:\xampp\htdocs\Nuqtah_IT`.
- PHP sessions with role-based access: public landing, borrower pages, admin pages.
- Borrowers browse inventory, use a cart, submit a request, then collect at the IT desk and return by a due date.
- Admins manage assets, asset tags, users, requests, handovers, returns, logs, and reports.
- Bilingual context: English interface, Bruneian institution, Malay names and place names.

## Capabilities and Constraints

- Guest landing page; login and registration; password reset and change through email.
- Inventory browsing with availability counts and images.
- Cart add/update/remove; one request per submission with pickup time.
- Request history for borrowers; approval, handover, and return processing for admins.
- Asset management with uploaded images, tag status, and deployment location.
- Audit log and printable inventory report.
- Constraint: no framework and no build step. Plain PHP includes, Bootstrap 5 from CDN, Font Awesome, one shared stylesheet (`assets/css/styles.css`).
- Constraint: the landing page must degrade for a logged-in session, showing inventory access instead of login.
- Open decision: no design system file existed before this redesign; DESIGN.md is written from the built result.

## Brand Commitments

- Name: Nuqtah (NUQTAH capstone, SCI). Institution: ITQSHHB.
- Both logos ship together: `assets/img/logoNuqtah_White.png` and `assets/img/ITQSHHBLogo.png`.
- Core teal: `#00796B`, deep teal `#004D40`.
- Building footage `assets/video/Front_slowfront_boomerang.mp4` is the established hero backdrop and stays.
- Footer content, credits, address, and embedded map stay as they are.
- Voice: plain, institutional, helpful. No hype, no invented statistics.

## Evidence on Hand

- Real asset photography in `assets/img/` (building, clock, front views) and `assets/upload/` (real catalog items: MacBook Air M2, Dell Latitude 5420, HP EliteBook 840, Logitech MX Master 3S, Epson EB-X06 projector, Insta360 X5, Anker cable).
- Real logo files, real institute address, real map embed.
- No testimonials, no usage numbers, no pricing. Future work must not invent them.

## Product Principles

1. The shelf is the truth: show real availability, not marketing claims.
2. One clear next action for the visitor, and it changes with session state.
3. Paperless means fewer steps, not more screens.
4. Institutional trust comes from precision, not decoration.
5. The borrower's time is the budget: every block on the landing page must earn a scroll.

## Accessibility & Inclusion

- Keyboard reachable navigation, visible focus, 4.5:1 body contrast.
- Must hold at 390px wide on a phone on campus wifi; video is a backdrop, not a dependency for reading.
