# Handoff: Kaligirl Financial Services — WordPress + MemberPress Migration

## Overview
A full redesign of the Kaligirl Financial Services marketing site: Home, Services, About, Contact, Get Started, Login, and a logged-in Account area with a Resources dropdown (Library, Lessons, Tools). The task is to rebuild this as a real WordPress site with MemberPress handling login/membership gating, and Moxo handling the actual client portal (chat, file sharing, onboarding forms).

## About the Design Files
The file `Kaligirl Website.dc.html` in this folder is a **design reference built in HTML/React-like JSX** — it is a working, click-through prototype showing exact look, copy, layout, and behavior. **It is not production code to paste into WordPress.** It uses a proprietary templating runtime (`support.js`) that only exists in the design tool and will not run in WordPress. The task is to **recreate this design as a real WordPress theme** (custom theme or child theme + page templates), wired to MemberPress for auth/gating, with the Moxo iframe embedded as shown.

## Fidelity
**High-fidelity.** Colors, typography, spacing, copy, and interactions in the HTML file are final — reproduce them pixel-for-pixel. Do not restyle or "improve" — match exactly, including hover states and the specific copy provided (some copy is explicitly marked as placeholder pending compliance review — see "Compliance placeholders" below).

---

## Exact Migration Instructions (step-by-step)

1. **Set up WordPress + plugins**
   - Install WordPress (hosting of your choice — must support PHP theme development).
   - Install and activate **MemberPress** (handles login, registration, membership tiers/subscriptions, and content gating).
   - Do NOT use a page builder (Elementor/Divi) to copy this design — build a proper custom theme so the layout, hover states, and conditional logged-in nav can be implemented exactly as specified below. A child theme of a minimal/blank base theme (e.g. `_s`/underscores, or a lightweight theme) is the cleanest starting point.

2. **Build the theme structure**
   - `header.php`: sticky header, logo, nav (see "Header / Navigation" spec below).
   - `footer.php`: footer nav + disclosure text (see "Footer" spec).
   - Page templates: `page-home.php`, `page-services.php`, `page-about.php`, `page-contact.php`, `page-get-started.php`. MemberPress supplies its own login/registration templates/shortcodes — style them to match `#login` page look (see Login spec) rather than building a custom login page.
   - `page-account.php`: MemberPress-gated template (only accessible to logged-in members — use MemberPress's `[mepr-account-*]` shortcodes or `MeprUser::is_logged_in()` / rule protection to enforce this at the template level, not just by hiding a nav link).
   - `page-library.php`, `page-lessons.php`, `page-tools.php`: MemberPress-protected placeholder pages (see "Resources placeholder pages" spec) — protect via a MemberPress Rule so only logged-in/subscribed members can load these URLs directly, not just when they click through the nav.

3. **Wire up MemberPress**
   - Create the membership level(s) that map to the "Entry / Grow / Exceed" pricing tiers described in Services (see Design Tokens/Pricing below) — pricing itself is still TBD, so create the levels now with placeholder pricing, to be finalized before launch.
   - Set MemberPress Rules so: Account, Library, Lessons, and Tools pages require an active membership/login; Home, Services, About, Contact, Get Started remain public.
   - Replace the demo `handleLoginSubmit`/`handleLogout` JS in the prototype with real MemberPress login/logout — do not build custom auth.

4. **Embed the Moxo client portal**
   - On the Get Started page, embed the existing Moxo iframe exactly as given:
     `<iframe src="https://app.moxo.com/embed/de789728-f434-4be6-9a47-218400bf7d8d" width="100%" height="600" frameborder="0"></iframe>`
   - This iframe handles onboarding, secure document exchange, chat, and file sharing — do not build any of that functionality natively in WordPress. The Account page in the prototype is a placeholder for a similar Moxo-embedded or Moxo-linked dashboard once the client's specific portal URL/session is available.
   - Moxo already connects to QuickBooks and Xero (shown as integration badges on About and mentioned in Services copy); Plaid integration is planned but not live — keep it labeled "coming soon" until connected.

5. **Logo asset**: use `assets/kaligirl-logo.png` (included in this folder) in the header (68px tall on desktop / 46px in footer) and footer exactly as sized in the prototype.

6. **Security requirements — required at the WordPress/hosting level, apply to all forms, logins, and endpoints:**
   - **Rate limiting**: cap unauthenticated requests to 10/min per IP, authenticated requests to 100/min per IP (implement via a security plugin, e.g. Wordfence/Cloudflare rate rules, or server-level config — not custom PHP).
   - **Input validation/sanitization**: sanitize all form inputs (contact, login, registration); reject/strip any input containing script tags, raw HTML, or SQL fragments. Use WordPress's built-in `sanitize_text_field()`, `wp_kses()`, and prepared statements (`$wpdb->prepare`) — never concatenate raw user input into queries.
   - **Honeypot traps**: add invisible honeypot fields to all public forms (login, contact, registration) and hidden bait routes; log and auto-block IPs that submit the honeypot field or hit bait routes.
   - **Automated challenges**: add CAPTCHA (reCAPTCHA v3 or hCaptcha) on registration, file uploads, and any contact form submission.
   - **File uploads**: if/when file upload features are added beyond Moxo's own uploader, restrict to image types only, rename files on upload to prevent execution, and cap file size at **1GB**.
   - **Secrets management**: no API keys, DB credentials, or JWT secrets hardcoded anywhere in the theme/plugin code — all secrets via environment variables / `wp-config.php` constants pulled from environment, never committed to version control.
   - **Audit logging**: log anomalous behavior — failed logins, repeated 404s, honeypot triggers — for periodic review (a security plugin like Wordfence covers this out of the box).

7. **Compliance placeholders — do not remove, do not treat as final copy:**
   - Footer disclosure text is final short-form copy as provided (S corp, not a registered investment advisory firm, fiduciary certification in progress) — reproduce exactly, it has already been reviewed for tone; but do have real counsel/compliance sign off before public launch.
   - "Form ADV / Disclosures" footer link is a stub (`alert()` in the prototype) — needs a real destination once available.

---

## Screens / Views

### Header (all pages)
- Height: 108px desktop, sticky (`position: sticky; top: 0`), semi-transparent white background (`rgba(248,249,251,0.92)`) with backdrop blur, 1px bottom border `#e1e5ec`.
- Logo left (68px tall), clicking it always routes home; **if the user is logged in, clicking the logo also logs them out** (returns to public Home nav state).
- **Logged-out state**: left group = Services, About, Contact (each 1.2rem, weight 500, color `#5b6472` inactive / `#1b2333` active page, with a 2px solid `#5b8fd6` underline under the active page — Home has no underline). Right group = Login (text link) + "Get started" pill button (`#2c3e63` bg, white text, 100px radius, hover lightens to `#8fb3e8` + lifts 2px).
- **Logged-in state**: Services/About/Contact are hidden entirely. Right group shows: Account (text link, same active/underline treatment as above) + "Resources" dropdown button (click-toggle, small triangle caret that rotates 180° when open) revealing a floating card with Library, Lessons, Tools links.
- All header nav links lift 2px and tint `#5b8fd6` on hover (transition 0.25s).
- **Mobile (<1040px width)**: nav collapses to a 3-line hamburger icon; clicking opens a full-width dropdown panel below the header with the same links stacked vertically (still respecting logged-in/out visibility rules).

### Home
- Hero: 2-column grid (1.1fr / 0.9fr) — left: eyebrow "Financial services & fractional CFO · California", H1 "Financial advice that answers to you, and only you." (Fraunces serif, up to 5.5rem), subhead, two CTAs ("Book an introduction" filled pill, "See how we work" outlined pill, outline fills solid navy + lifts on hover). Right: placeholder image slot (4:5 ratio, striped placeholder — swap for a real advisor portrait).
- "The standard we hold": soft radial blue-tinted background, 3 numbered principle rows (circular outlined badges, not solid).
- "How we help": light blue gradient band, small animated 3-bar chart icon top-right, 4 service cards (Financial planning, Investment guidance, Financial decisions, Fractional CFO) each with a navy icon badge, blue top border, lift+shadow+icon-rotate+underline-sweep on hover.
- CTA band: 3-stop navy gradient, "Let's start with a conversation," white "Book an introduction" button (inverts to dark navy on hover).

### Services
- Hero + 4 numbered principle rows (same as Home's fiduciary section but Services-specific, including Fractional CFO as #04).
- **Fee structure section** (soft blue radial bg): 3 pricing cards — **Entry** ("For individuals just starting to build a plan and get organized," price TBD), **Grow** ("Ongoing planning and investment guidance as your finances get more complex," price TBD), **Exceed** ("Tailored, comprehensive planning or fractional CFO services for small businesses," price = "Contact us"). Same card style as service cards (navy top border, lift on hover).
- Closing navy CTA band, same style as Home's.

### About
- Hero + 3 cards: "Fiduciary-minded" (certification underway — do not claim registered status), "Fee-only," "Based in Morgan Hill."
- Integrations block below: "Integrates with" label + QuickBooks/Xero shown as solid white cards with serif wordmark styling; Plaid shown dashed/muted labeled "— coming soon."

### Contact
- Hero + 3 info cards (Email, Location, Hours).
- 2-column closing block: left = heading "Want to get in touch directly?" + CTA button; right = placeholder map/office image slot (16:9).

### Get Started
- Tight hero ("Book your introduction right here," subhead: "Fill out the form below to get onboarded — it's secure, private, and goes straight to us.")
- Moxo iframe embedded immediately below (no scrolling required to reach it) — see embed code above.
- 3-step explainer below the iframe (Reach out / Introductory meeting / Secure onboarding).

### Login (public)
- Centered card (26rem max-width): Log in heading, email + password fields, "Log in" button (navy, lightens on hover), link to Get Started for new users. In the prototype this is a demo state toggle — in WordPress, replace with MemberPress's real login form/shortcode styled to match.

### Account (logged-in only)
- "Welcome back" hero + Log out button (outline, inverts to solid navy on hover).
- 3 cards: Documents, Messages, Plan — placeholders for whatever the real MemberPress/Moxo-linked dashboard will show.

### Library / Lessons / Tools (logged-in only, reached via Resources dropdown)
- Centered placeholder page: eyebrow "Client resources," H1 = page name, one line: "This area is coming soon. Once live, it'll live here for logged-in clients."

### Footer (all pages)
- Logo (46px) left; link list right: Services, About, Contact, Form ADV / Disclosures (all `#5b6472`, hover to `#2c3e63`).
- Disclosure paragraph below a dashed top border (0.78rem, `#5b6472`) — reproduce the exact final text (see step 7 above).

---

## Interactions & Behavior
- Page navigation is client-side in the prototype (no real routing) — in WordPress these become real page loads/permalinks.
- All primary/pill buttons: background lightens (`#2c3e63` → `#8fb3e8`) + lifts 2px on hover, 0.25s ease transition.
- Outline buttons ("See how we work," "Log out"): invert to solid navy + white text + lift on hover.
- Service/pricing cards: lift 6–8px, shadow deepens, icon badge (services only) scales 1.15x + rotates -6deg + background lightens, title tints navy, thin underline bar sweeps to full width — all 0.3–0.4s eased transitions.
- Resources dropdown: click toggle (not hover), caret rotates 180° open, closes on link click.
- Mobile menu: hamburger toggles a full stacked panel; breakpoint at 1040px width.
- Active-page nav indicator: 2px solid `#5b8fd6` underline under the current page's nav link (not shown for Home).

## State Management
- Logged-in/out boolean (→ becomes real WordPress/MemberPress session state).
- Current page (→ becomes real URL/routing).
- Mobile menu open/closed, Resources dropdown open/closed (simple UI toggle state, can stay client-side JS in the real build).

## Design Tokens
- **Colors**: Navy primary `#2c3e63` (darker variant `#1b2333`/`#233257` for gradients), accent blue `#5b8fd6` (light hover `#8fb3e8`, pale tint `#eef4fc`/`#dbe7f7`), body text `#1b2333` (headings) / `#5b6472` (body/secondary), borders `#e6e9ef`/`#e1e5ec`/`#c7cedb`, page background `#f8f9fb`.
- **Typography**: Headings — 'Fraunces' (serif, weight 500, sizes from 1.1rem up to 5.5rem via `clamp()`). Body/UI — 'Inter' (weights 400–700). Both loaded from Google Fonts.
- **Radius**: pill buttons/links = `100px`; cards = `14px`; large banners/CTA bands = `20px`; small badges/inputs = `8–12px`.
- **Shadows**: card default `0 1px 2px rgba(28,40,66,0.04)`; card hover `0 20–24px 36–40px rgba(44,62,99,0.14–0.2)`.
- **Spacing scale**: section vertical padding uses `clamp()` roughly between 2rem and 9vh; max content width `100rem`.

## Assets
- `assets/kaligirl-logo.png` — wordmark logo, included in this folder.
- Placeholder image slots (hero, about, contact) are striped gray/blue diagonal placeholders — replace with real photography before launch (advisor portrait, office/team photo, map/office location).

## Files
- `Kaligirl Website.dc.html` — the full design reference (all 8 views + logic) for line-by-line lookup of exact markup, inline styles, and copy.
- `assets/kaligirl-logo.png` — logo asset.
