# Handoff: Kaligirl Financial Services — WordPress + MemberPress Migration

## Overview
Full redesign of the Kaligirl Financial Services marketing site. Public pages: Home, Services (with a Personal/Business mega-menu), Personal Financial Consulting, Business Advisory, Resources (with an Articles/Videos/Calculators mega-menu), About, Contact, Get Started, Login. Logged-in-only: Account, Library, Lessons, Tools. MemberPress handles login/membership gating; Zoho handles the client-facing backend (booking, forms, and eventually the client portal).

## About the Design File
`Kaligirl Website.dc.html` is a **design reference** — a working, click-through prototype in HTML/React-like JSX showing exact look, copy, layout, and behavior. **It is not production code to paste into WordPress.** It runs on a proprietary templating runtime (`support.js`) that only exists in the design tool. The task is to **recreate this design as a real WordPress theme** (custom theme or child theme + page templates) wired to MemberPress for auth/gating, matching this file pixel-for-pixel — colors, type, spacing, copy, hover states, and interactions all final unless marked as a placeholder below.

---

## Exact Migration Instructions (step-by-step)

1. **Set up WordPress + plugins**
   - Install WordPress; install and activate **MemberPress** (login, registration, membership tiers, content gating).
   - Do NOT use a page builder (Elementor/Divi). Build a proper custom theme (or child theme of a minimal base like `_s`) so hover states, mega menus, and conditional nav can be implemented exactly.

2. **Build the theme structure**
   - `header.php`: sticky header, logo, nav + mega menus (see Header spec).
   - `footer.php`: footer nav + disclosure (see Footer spec).
   - Public page templates: `page-home.php`, `page-services.php`, `page-personal-consulting.php`, `page-business-advisory.php`, `page-resources.php`, `page-about.php`, `page-contact.php`, `page-get-started.php`. MemberPress supplies its own login/registration shortcodes — style them to match the Login spec rather than building custom auth forms.
   - `page-account.php`, `page-library.php`, `page-lessons.php`, `page-tools.php`: MemberPress-gated templates. Protect with a MemberPress Rule at the URL level (not just by hiding nav links) so these 404/redirect for logged-out users even on direct visit.

3. **Wire up MemberPress**
   - Create membership level(s) mapping to the "Entry / Grow / Exceed" pricing tiers on Services (pricing still TBD — set up the levels now, finalize pricing before launch; "Exceed" has no fixed price, it's "Contact us").
   - MemberPress Rules: Account, Library, Lessons, Tools require login; everything else public.
   - Replace the prototype's demo `handleLoginSubmit`/`handleLogout`/`goHomeAndLogout` (logo click while logged in also logs the user out) with real MemberPress session logic.

4. **Get Started page — Zoho Bookings**
   - The client is migrating their backend to Zoho. The intended integration is **Zoho Bookings**, not a raw iframe: `https://kaligirlfinancialservices.zohobookings.com/4946279000000136007` **refuses to be framed** (Zoho sends `X-Frame-Options`/CSP headers blocking iframe embedding) — a bare `<iframe src="...">` will fail with a "refused to connect" error in every browser.
   - Use Zoho Bookings' own **official embed widget** instead of an iframe: in Zoho Bookings → the booking page → **Share and Publish → Embed**, copy the provided `<script>` widget snippet (it opens Bookings in a popup/overlay rather than framing it directly) and drop that into the Get Started page.
   - If Zoho doesn't offer a usable embed for this booking page, fall back to a **"Book now" button** that opens `https://kaligirlfinancialservices.zohobookings.com/4946279000000136007` in a new tab — do not attempt to force an iframe.
   - Previously this page used an embedded Zoho **Form** — that piece is superseded by Bookings; don't build both, Bookings is the current source of truth for this step.
   - QuickBooks/Xero integration badges (About page, Services copy) refer to the client's own accounting stack, unrelated to this booking widget — keep as-is. Plaid is planned but not live — keep labeled "coming soon."
   - The Account page in the prototype is a placeholder for whatever Zoho-linked client dashboard the business builds out later.

5. **Logo asset**: `assets/kaligirl-logo.png` (included), 68px tall in the header, 46px in the footer.

6. **Theme system**: the prototype ships 3 selectable color themes (a Tweaks-panel toggle in the design tool) — **Terracotta is the shipped default**; do not build the other two (Ocean, Dark) unless the client asks — implement Terracotta's fixed values directly rather than a runtime theme switcher, since WordPress doesn't need that toggle. See Design Tokens for the exact Terracotta values.

7. **Security requirements** — apply to all forms, logins, and endpoints:
   - **Rate limiting**: 10 req/min per IP unauthenticated, 100 req/min authenticated (via a security plugin or server-level config, not custom PHP).
   - **Input validation**: sanitize all form input; reject script tags/HTML/SQL fragments. Use `sanitize_text_field()`, `wp_kses()`, `$wpdb->prepare()` — never raw-concatenate user input into queries.
   - **Honeypot traps**: invisible fields on all public forms + hidden bait routes; log and auto-block IPs that trip them.
   - **Automated challenges**: CAPTCHA (reCAPTCHA v3 / hCaptcha) on registration, uploads, and contact submissions.
   - **File uploads**: image types only, renamed on upload to prevent execution, **1GB** max size.
   - **Secrets**: no hardcoded API keys/DB creds/JWT secrets — environment variables / `wp-config.php` constants only.
   - **Audit logging**: track failed logins, repeated 404s, honeypot hits (Wordfence or similar covers this out of the box).

8. **Compliance placeholders — do not alter or remove:**
   - Footer disclosure text is final short-form copy (S corp, not a registered investment advisory firm, fiduciary certification in progress) — reproduce exactly; get real compliance/counsel sign-off before public launch regardless.
   - "Form ADV / Disclosures" footer link is a stub — needs a real destination once available.
   - About page intro paragraph is marked `[Founder bio and firm background — replace with approved copy.]` — do not publish as-is.

---

## Screens / Views

### Header (all pages)
- 108px tall, sticky, white-tinted translucent background (`rgba(255,255,255,0.92)`) with blur, 1px bottom border.
- Logo left (68px). Clicking it goes home; if logged in, it also logs the user out.
- **Logged-out, left of logo group**: Services (hover reveals a mega menu — see below), Resources (hover reveals a mega menu — see below), About, Contact — each with a 2px accent-color underline when active (not shown on Home).
- **Logged-out, right**: Login (text link) + "Get started" pill button (dark anchor bg, white text, lightens + lifts 2px on hover).
- **Logged-in**: Services/Resources/About/Contact are hidden entirely. Right side shows Account (text link, same active-underline treatment) + a "Resources" click-toggle dropdown (Library, Lessons, Tools) with a rotating caret.
- All nav links lift 2px + tint accent-color on hover (0.25s).
- **Mobile (<1040px)**: collapses to a 3-line hamburger; opens a full-width stacked panel below the header, same link set and visibility rules, including indented "— Personal Financial Consulting" / "— Business Operations Consulting" sub-links under Services.

#### Services mega menu (hover-triggered, pops in with a scale+slide-down animation)
Anchored just under "Services," not full-width. Two content columns + one CTA column:
- **Personal** column: small image placeholder, then link to Personal Financial Consulting, then plain-text sub-links (Financial planning / Investment guidance / Financial decisions).
- **Business** column: small image placeholder, then link to Business Advisory, then sub-links (Financial operations / Fractional CFO).
- **CTA column**: "Not sure where to begin?" label, a card with heading "Book a no obligation conversation to get started" and a "Book now" button.

#### Resources mega menu (hover-triggered, same pop animation)
Anchored under "Resources," headed "Financial resources." Three columns, each with a small image placeholder on top: **Articles**, **Videos**, **Calculators** — each with a one-line description, all linking to the Resources page (content itself is "coming soon").

### Home
- Hero: 2-column grid — eyebrow "Financial services & fractional CFO · California," H1 "Financial advice that answers to you, and only you." (Fraunces, up to 5.5rem), subhead, two CTAs ("Book an introduction" filled, "See how we work" outlined — outline fills solid + lifts on hover). Right: image placeholder (4:5, "advisor portrait"). Section has a soft radial-gradient wash fading from the accent-soft tint into the page background.
- "The standard we hold": soft radial-tinted background, 3 principle rows with circular outlined number badges (not solid).
- "How we help": gradient band, small animated 3-bar chart icon top-right (bars grow in on load), 4 service cards (Financial planning, Investment guidance, Financial decisions, Fractional CFO) — each with an icon badge, accent top border, and on hover: lifts 6-8px, shadow deepens, icon badge scales+rotates+lightens, title tints, underline bar sweeps full width.
- Closing CTA band: gradient background, "Let's start with a conversation," white "Book an introduction" button (inverts to dark on hover).

### Services
- Hero + 4 numbered principle rows (adds "Fractional CFO services" as #04).
- **Fee structure**: 3 pricing cards — Entry (price TBD), Grow (price TBD), Exceed ("Contact us," tailored/comprehensive framing) — same card treatment as service cards.
- Closing CTA band, same style as Home's.

### Personal Financial Consulting (`/personal-consulting`)
- Hero with the same radial-gradient wash as other interior pages.
- Grid of 4 horizontal cards, 2 per row: **Financial Planning, Financial Strategy, Financial Coaching, Life Transitions** (Life Transitions copy explicitly includes divorce). Each card: image on the left (~40% width), title + description on the right, accent-color left border, lift+shadow+border-color+title-color hover.
- **Click-to-focus interaction**: clicking any card hides the grid and shows ONLY that card, expanded — larger image on the left (~42% width), full title + a longer detail paragraph + CTA button on the right — with a "← Back to all" link above it that returns to the grid. This is a pure client-side show/hide (no page navigation) — implement with JS state toggling grid vs. detail view, not a real page reload.
- CTA button under the grid reads "Find the Right Plan" (not "Book an introduction" or "Get started").

### Business Advisory (`/business-advisory`, was "Business Operations Consulting")
- Hero: H1 "Business Advisory," subhead "Strengthen your financial foundation, operating model, organization, and customer experience."
- Grid of 6 horizontal cards, 2 per row, same visual treatment as Personal's cards: **Fractional CFO, Fractional COO, Financial Strategy, Organizational Design, Operations Design, Customer Experience.**
- Same click-to-focus expand/collapse behavior as Personal Financial Consulting.
- CTA button reads "Schedule a Consultation."

### Resources (`/resources`, public)
- Hero: "Free articles & videos," subhead about open access. Content section currently says "Content is coming soon."

### About
- Hero (with intro paragraph flagged as placeholder — see Compliance placeholders) + 3 cards: "Fiduciary-minded" (certification underway — never claim "registered"), "Fee-only," "Based in Morgan Hill."
- "Integrates with" block: QuickBooks/Xero as solid cards, Plaid dashed/muted "coming soon."

### Contact
- Hero + 3 info cards (Email, Location, Hours).
- 2-column closing block: heading "Want to get in touch directly?" + CTA button on the left, placeholder map/office image on the right (16:9).

### Get Started
- Tight hero: "Book your introduction right here," subhead "Fill out the form below to get onboarded — it's secure, private, and goes straight to us."
- Zoho Bookings widget/button immediately below, no scrolling required — see step 4 above for the exact integration approach (NOT a raw iframe).
- 3-step explainer below (Reach out / Introductory meeting / Secure onboarding).

### Login (public)
- Centered card: email + password fields, "Log in" button, link to Get Started for new users. Replace with MemberPress's real login shortcode/flow, styled to match.

### Account (logged-in only)
- "Welcome back" hero + Log out button.
- 3 cards: Documents, Messages, Plan — placeholders for a future MemberPress/Zoho-linked dashboard.

### Library / Lessons / Tools (logged-in only, via Resources dropdown)
- Centered placeholder: eyebrow "Client resources," H1 = page name, "This area is coming soon."

### Footer (all pages)
- Logo (46px) left; links right: Services, About, Contact, Form ADV / Disclosures.
- Disclosure paragraph below a dashed divider — reproduce exactly (see Compliance placeholders).
- Background fades from the page background into the header's tint (not a hard cut).

---

## Interactions & Behavior
- All primary/filled buttons: background lightens + lifts 2px on hover (0.25s).
- Outline buttons: invert to solid + white text + lift on hover.
- Cards (service/pricing/consulting): lift + shadow deepens + icon/border/title accent shifts + underline sweep, all on hover, 0.3–0.4s eased.
- Mega menus (Services, Resources): hover-triggered (not click), pop in with a scale(0.96→1) + translateY(-10px→0) animation, ~0.22s eased; a short close-delay on mouse-leave prevents flicker when moving from the trigger into the menu.
- Logged-in Resources dropdown: click-toggle (not hover), rotating caret.
- Mobile menu: hamburger toggles a stacked panel; breakpoint 1040px.
- Personal/Business consulting cards: click-to-expand-in-place (grid ⇄ single focused card), no page navigation.
- Active-page nav gets a 2px accent underline (not shown on Home).

## State Management
- Logged-in/out boolean → real WordPress/MemberPress session state.
- Current page → real URL/routing.
- Mega-menu open/closed, mobile-menu open/closed, Resources-dropdown open/closed, selected consulting card → all plain client-side UI state (fine to keep as vanilla JS/Alpine/whatever the theme uses).

## Design Tokens (Terracotta theme — the shipped default)
- **Primary/anchor** (buttons, active nav text, structural elements): `#1b2333`; hover-lightens to `#c1603f` (terracotta).
- **Accent/secondary** (eyebrows, underlines, icon accents, card top/left borders): `#2f6b64` (deep teal).
- **Accent-soft tint** (gradient washes, hover backgrounds): `#dce9e6`.
- **Page background**: `#f5ead9` (soft cream).
- **Header/footer tint**: `rgba(255,255,255,0.92)` (white, not matching the cream body — this was an explicit fix so the header reads as solid white against the cream page).
- **Body text**: `#2b2b2b` (charcoal) for headings/active states, `#5b6472` for secondary/muted text.
- **Card background**: `#fff`; **card border**: `#e6e9ef`.
- **Typography**: Headings — 'Fraunces' (serif, weight 500, sizes 1.1rem–5.5rem via `clamp()`). Body/UI — 'Inter' (400–700). Both from Google Fonts.
- **Radius**: pills/links `100px`; cards `14px`; large banners `20px`; small badges/inputs `8–12px`.
- **Shadows**: card default `0 1px 2px rgba(28,40,66,0.04)`; card hover `0 20–24px 36–40px rgba(44,62,99,0.14–0.2)`.
- **Spacing**: section vertical padding via `clamp()` roughly 2rem–9vh; max content width `100rem`.

## Assets
- `assets/kaligirl-logo.png` — wordmark logo.
- All other image spots (hero portraits, mega-menu thumbnails, card images, map/office shot) are placeholders — replace with real photography before launch.

## Files
- `Kaligirl Website.dc.html` — full design reference (all views + logic) for exact markup/style/copy lookup.
- `assets/kaligirl-logo.png` — logo asset.
