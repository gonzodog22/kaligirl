# Kaligirl Financial Services — WordPress theme

Custom WordPress theme (not a page builder export) implementing the design in
`design_handoff_wordpress_migration/`, wired to Paid Memberships Pro for login
and membership gating (unrelated to the change below — left as-is; see the
"Get Started flow" section for why). Get Started is a Zoho-native,
token-gated flow per a later migration handoff that **drops Moxo entirely**
— do not reintroduce the Moxo iframe.

## What's here

```
kaligirl-wp-theme/
├── style.css                 # Theme header + design tokens + all component CSS
├── functions.php             # Theme bootstrap, enqueues, includes
├── header.php / footer.php   # Sticky header (logged-in/out states), footer
├── front-page.php            # Site root
├── page-home.php             # Template Name: Home
├── page-services.php         # Template Name: Services
├── page-about.php            # Template Name: About
├── page-contact.php          # Template Name: Contact
├── page-get-started.php      # Template Name: Get Started (fork page — see "Get Started flow")
├── page-booking.php          # Template Name: Booking (Route Two destination, token-gated)
├── page-payment.php          # Template Name: Payment (Route One destination, token-gated, scaffolded — payments not wired up yet)
├── page-thank-you.php        # Template Name: Thank You (post-booking confirmation, no gating)
├── page-login.php            # Template Name: Login (unused stub, see below)
├── page-account.php          # Template Name: Account (unused stub, see below)
├── page-library.php          # Template Name: Library (membership-gated placeholder)
├── page-lessons.php          # Template Name: Lessons (membership-gated placeholder)
├── page-tools.php            # Template Name: Tools (membership-gated placeholder)
├── page.php / index.php      # Fallbacks for ad-hoc pages / anything else
├── template-parts/           # Shared content partials
├── inc/
│   ├── template-tags.php     # Nav link + principle-row render helpers
│   ├── membership.php        # Login/account URL + gating helpers (PMP)
│   ├── security.php          # See "Security" below
│   └── zoho.php              # Gate-token validation against Zoho Creator (see "Get Started flow")
├── js/main.js                # Mobile menu + Resources dropdown, Get Started fork/token logic
└── assets/kaligirl-logo.png
```

## Install

1. Set up WordPress on hosting that supports custom PHP themes.
2. Copy `kaligirl-wp-theme/` into `wp-content/themes/` and activate it under
   Appearance > Themes.
3. Install and activate **Paid Memberships Pro** (free core plugin —
   Plugins > Add New > search "Paid Memberships Pro"). Activating it
   auto-creates its own Membership Account page.
4. Create pages for each section and assign the matching template under
   Page Attributes (WordPress also auto-matches these by slug, e.g. a page
   at `/services/` picks up `page-services.php` automatically):
   - `/` or "Home" → Home
   - `services`, `about`, `contact`, `get-started` → matching templates
   - `booking`, `payment` → matching templates (URLs must be exactly
     `/booking` and `/payment` — Zoho Forms' post-submission redirect and
     this theme's fallback "Continue" links both point at those two paths
     literally; see "Get Started flow" below)
   - `thank-you` → the **Thank You** template (URL must be exactly
     `/thank-you` — point Zoho Bookings' post-booking redirect setting at it)
   - `library`, `lessons`, `tools` → matching templates (see gating below)
   - **Do not** create your own `account` or `login` pages using this
     theme's Account/Login templates — see "Login and Account" below for
     why, and what to do instead.
5. Settings > Reading: leave "Your homepage displays" as-is — `front-page.php`
   renders the Home design regardless of that setting.
6. Settings > Permalinks: use a non-default structure (e.g. "Post name") so
   the slug-based URLs above resolve as expected.

## Login and Account (important — read before creating pages)

This theme originally had its own pixel-matched Login and Account page
templates (`page-login.php`, `page-account.php`). They're still in the
theme, but **unassigned to any page for now** — both are stubs that just
redirect to Paid Memberships Pro's own equivalent pages. Here's why, and
what to do instead:

- PMP's `[pmpro_account]` shortcode (its Membership Account page) has no
  logged-out handling at all — it assumes you're already logged in.
- PMP's `[pmpro_login]` shortcode (a separate Login page) is what actually
  handles guests: login form when logged out, a "Welcome" widget when
  logged in, from one URL, with PMP's own post-login redirect deciding
  where to send someone next.
- A **second**, separately-gated custom Login page — like this theme's
  original one, which redirected away from itself whenever
  `is_user_logged_in()` was true, while the Account page only let someone
  stay if they held an active membership *level* — creates a mismatch:
  log in without a level assigned yet, and the two pages bounce you between
  each other forever ("too many redirects"). Routing both through PMP's own
  pages avoids this because one plugin owns both ends of the redirect.

**What to do:**
- Confirm/set PMP's Login and Membership Account pages under **Memberships
  > Page Settings** (create a page with the `[pmpro_login]` shortcode if
  one doesn't already exist, publish it, then select it there).
- Nav links for "Login" and "Account" in `header.php` already resolve to
  these PMP pages automatically via `kaligirl_login_url()` /
  `kaligirl_account_url()` in `inc/membership.php` — no header changes needed.
- If you revive the custom design later: fix the mismatch above first (e.g.
  make both pages check the same thing — either both check plain login
  state, or both check membership level, not one of each), and avoid the
  page slug `login` specifically — PMP treats any page at that slug as its
  own login page and layers its own redirect behavior on top, regardless of
  which template is assigned.

## Paid Memberships Pro configuration (in wp-admin, not code)

- **Membership levels**: Memberships > Membership Levels — create Entry /
  Grow / Exceed levels matching the Services pricing tiers. Pricing is TBD
  per the design handoff — use placeholder pricing now, finalize before
  launch.
- **Page protection**: open each of `Library`, `Lessons`, and `Tools` in the
  page editor and use the **Require Membership** box PMP adds to the
  page-edit screen — check the level(s) that should have access. Note: PMP's
  restriction actually filters `the_content`, which these custom-templated
  pages never call, so this setting alone won't gate them — the real control
  is `kaligirl_require_login()` in `inc/membership.php` (called at the top of
  each template). Set the "Require Membership" box anyway for admin-UI
  clarity/consistency, but don't rely on it alone for these three pages.
- Leave `Home`, `Services`, `About`, `Contact`, and `Get Started` unprotected.
- **reCAPTCHA**: Memberships > Settings > reCAPTCHA — enable v3 and supply
  site/secret keys as environment variables or wp-config constants (see
  Secrets below), per the security requirements.

## Get Started flow (Zoho-native — Moxo is dropped)

Moxo was dropped from the plan (cost vs. what it offered at the needed
tier). The Get Started page is now a **fork page**, not a form itself:

1. **`page-get-started.php`** — a Personal/Business Advising toggle (pure
   UI, no gating logic) plus two buttons:
   - **"Schedule an Introductory Consultation"** (Route Two) — always
     visible in both toggle states.
   - **"Find the Right Plan"** (Route One) — visible only in "Personal
     Advising" mode; Business/CFO visitors never see it.

   Clicking either reveals that route's already-built Zoho Forms iframe
   (not custom — these forms exist and work in Zoho already). Before each
   iframe's `src` is set, `js/main.js` generates a one-time token
   (`crypto.randomUUID()`, with an older-browser fallback) and appends it
   as `?gated_token=...`. The same token is stashed in `sessionStorage` and
   used to build a "Continue" link as a fallback path to `/booking` or
   `/payment`.

   **Iframe breakout on redirect:** Zoho's "Redirect URL on Submission"
   navigates *within the form's own iframe*, not the top-level page — left
   alone, `/booking`/`/payment` would render nested inside that small form
   iframe instead of taking over the tab. `js/main.js` listens for the
   iframe's `load` event and tries to read `iframe.contentWindow.location`;
   that throws (cross-origin) while it's still showing Zoho's domain, but
   succeeds the instant Zoho's redirect lands it on our own domain — at
   which point it forces a real `window.top.location` navigation to that
   same URL. Confirmed working: submitting a form now takes over the whole
   tab instead of loading `/booking` nested inside the form iframe.

2. **`page-booking.php`** (Route Two destination) and **`page-payment.php`**
   (Route One destination, scaffolded — Zoho Billing's hosted payment page
   doesn't exist yet) both validate `?token=` **server-side** against Zoho
   Creator's `intake-token-gate` datastore (`inc/zoho.php`,
   `kaligirl_validate_gate_token()`) before rendering anything. No valid,
   "used" token record → a friendly message and a link back to Get Started,
   never the real Zoho Bookings/Billing embed. This is the actual security
   boundary — nobody reaches either embed by guessing the URL, only by
   completing a real Zoho Forms submission first.

### Confirmed working

- **Zoho Forms' "Redirect URL on Submission"** does carry `gated_token`
  forward dynamically — configured per-form as a static
  `https://kaligirlfinancialservices.com/booking?token=` (or `/payment?token=`)
  prefix plus the `gated_token` field inserted via Zoho's own merge-field
  picker in that setting. Tested end to end, including the iframe-breakout
  fix above. The "Continue" link in `page-get-started.php` (reads the same
  token back out of `sessionStorage`) still exists as a manual fallback in
  case this ever isn't configured on a given form — it only ever carries
  the token, though, not the autofill fields below (see why in
  `page-booking.php`'s header comment).

### Autofilling Name/Email/Phone on the Bookings embed — confirmed working

`page-booking.php` reads `?Name=`, `?Email=`, `?Phone=` off its own URL
(capitalized — exactly these, `$_GET` keys are case-sensitive) and
appends them onto the Zoho Bookings widget's own URL as a query string
placed **after** the `#/4946279000000039045` hash fragment. This is the
opposite of normal URL structure (query strings normally precede a
fragment) and opposite of what an earlier version of this file did, but
it's confirmed by testing: placing it before the hash does **not**
prefill the widget, placing it after does. Don't move it back.

Zoho Bookings maps the phone field to one literally named `Contact
Number` (with a space) — sent as-is, not renamed to something
space-free, because that's the exact field name that prefills correctly.

Extend each Zoho Form's Redirect URL setting (via Zoho's own field-merge
picker, same as the token) to include all three:

```
https://kaligirlfinancialservices.com/booking?token={gated_token}&Name={Name field}&Email={Email field}&Phone={Phone field}
```

`kg_email` is validated with `is_email()` after sanitizing — an
invalid/malformed email is silently dropped from the prefill rather than
passed through, so the widget just shows that one field blank instead of
prefilling garbage.

You do **not** need to store name/email/phone in the `intake-token-gate`
Creator report for this — whatever the real mechanism turns out to be,
the redirect URL already carries everything `page-booking.php` needs.

### Still needs manual confirmation

- **Zoho Bookings' own "post-booking redirect" setting** (configured in
  Zoho Bookings, not this repo) should point at `/thank-you` on this
  domain. `page-thank-you.php` exists and is unrelated to the token
  gating above (no gating logic — anyone landing there after a real
  booking, or by finding the URL directly, sees the same simple "check
  your email" message) — create a page at that slug with the **Thank
  You** template, then point Zoho Bookings' redirect setting at it.

### Payments — deliberately not built yet

`page-payment.php` is still the placeholder scaffold from the original
handoff (a bare `checkout.zoho.com/embed/{page-id}` iframe with no real
page id). Zoho Payments turns out to need a proper session-based widget
integration instead (JS SDK + a server-side "Payment Session Create" API
call, not a static hosted-page iframe) — holding off until the Payments
account/API credentials and the per-plan amount logic are sorted out; see
chat history for the credentials list once that's ready to pick back up.

### Placeholders still needing real values

- The literal Zoho referrer-tracking `<script>` block that ships with each
  form's embed code isn't reproduced in `page-get-started.php` (wasn't
  available at build time) — see the `<!-- TODO -->` HTML comments marking
  exactly where to paste each one in, unmodified.
- `$kg_zoho_billing_page_id` at the top of `page-payment.php` — the Zoho
  Billing hosted payment page ID, once that page exists.
- `ZOHO_CREATOR_ACCOUNT_OWNER` (see Secrets below) — the `{account-owner}`
  segment of the Creator report URL.

### Credentials (already provisioned on the server)

`ZOHO_CLIENT_ID`, `ZOHO_CLIENT_SECRET`, and `ZOHO_REFRESH_TOKEN` are live
`wp-config.php` constants, already set up — this repo only ever references
the constant *names* via `kaligirl_secret()` (see Secrets below), never
their values. `.gitignore` at the repo root excludes `wp-config.php`
explicitly — confirmed present before this branch merges toward main, per
the handoff's compliance note (these are live production credentials).

### Known intermittent failure: token not "used" yet at validation time

Observed in testing: the same flow, same form, sometimes lands on `/booking`
fine and sometimes shows the "invalid link" message — with no code change
between attempts. Most likely cause: if the Creator record's `status` gets
set to `"used"` by a Zoho Flow/automation step (rather than a fully
synchronous action tied directly to the form submission), there's a real
window where the browser's redirect can arrive at `/booking`/`/payment`
*before* that write finishes — eventual-consistency lag on Zoho's side, not
something fixable from WordPress. `kaligirl_validate_gate_token()`
(`inc/zoho.php`) now retries the Creator lookup up to 3 times with a short
delay (0s, 1s, 2s — ~3s worst case) before giving up, which should absorb
typical automation lag. If it's still flaky after this, check on the Zoho
side exactly how/when the `IntakeTokens_Report` record gets written
relative to the form's redirect firing.

### Rate limiting & audit logging for this flow

`inc/security.php`'s fallback rate limiter also covers `/booking` and
`/payment` whenever a `token` query param is present — each hit makes a
real round-trip to Zoho's OAuth + Creator APIs, worth protecting the same
way as login/checkout. Failed or invalid token validations log via
`kaligirl_security_log()` as `token_validation_failed` /
`token_validation_error` (a short, non-reversible fragment of the token is
logged for correlation, not the full value).

### MemberPress / PMP note

The original spec's Login/Account/Library/Lessons/Tools gating (this repo
already runs on Paid Memberships Pro, having migrated off MemberPress
earlier) is **unrelated to this flow and was left untouched** — Get
Started, `/booking`, and `/payment` are all public URLs gated by the Zoho
token mechanism above, not by PMP membership state.

## Security

Per the migration spec, the heavyweight controls (real-time rate limiting,
WAF rules, brute-force IP blocking) belong at the hosting/plugin level, not
hand-rolled in theme PHP:

- **Install a security plugin** (Wordfence is the reference recommendation)
  for rate limiting, brute-force protection, and out-of-the-box audit
  logging — or configure equivalent rules at your CDN/edge (e.g. Cloudflare
  rate limiting rules): 10 req/min/IP unauthenticated, 100 req/min/IP
  authenticated.
- `inc/security.php` implements what legitimately belongs in code as a
  defense-in-depth layer, and documents the rest:
  - A **fallback rate limiter** (transient-based, scoped to login/registration
    endpoints only) for environments without a WAF/edge layer yet.
  - **Honeypot fields** on the core login form (`wp-login.php` and PMP's own
    Login page, which renders the same form under the hood) and on Paid
    Memberships Pro's checkout/registration form, logged and blocked on
    trigger.
  - **Input sanitization helpers** (`kaligirl_sanitize_input()`,
    `kaligirl_sanitize_rich_text()`) for any field the theme or a future
    native form accepts — reject script tags/raw HTML/SQL fragments, and
    use `sanitize_text_field()` / `wp_kses()` / `$wpdb->prepare()`
    conventions throughout; there is no raw-input-into-query code in this
    theme.
  - **reCAPTCHA v3 verification helper** (`kaligirl_verify_recaptcha()`) for
    any form not already covered by PMP's native reCAPTCHA setting.
  - **File upload restrictions** (images only, renamed on upload, 1GB cap) —
    guardrails in place now in case a native upload feature is added later;
    Zoho's own tools (Forms, Bookings, Billing) handle the site's actual
    document exchange today.
  - **Audit logging** (`kaligirl_security_log()`) to a directory outside the
    theme, protected by a deny-all `.htaccess`, capturing failed logins,
    honeypot triggers, rate-limit blocks, repeated 404s, and (see "Get
    Started flow" above) failed `/booking`/`/payment` token validations.
  - Baseline hardening: XML-RPC disabled, WP version hidden, file editor
    disabled, security response headers.

### Secrets management

No API keys, DB credentials, or JWT secrets are hardcoded anywhere in this
theme. Use `kaligirl_secret( 'NAME', $default )` (in `inc/security.php`) to
read any future secret from an environment variable first, falling back to
a `wp-config.php` constant — never a committed value. `wp-config.php`
itself is excluded via the repo-root `.gitignore`. Example `wp-config.php`
snippet (add outside version control, e.g. via your host's
environment/secrets manager):

```php
define( 'KALIGIRL_RECAPTCHA_SITE_KEY', getenv( 'KALIGIRL_RECAPTCHA_SITE_KEY' ) ?: '' );
define( 'KALIGIRL_RECAPTCHA_SECRET_KEY', getenv( 'KALIGIRL_RECAPTCHA_SECRET_KEY' ) ?: '' );

// Get Started flow (see above) — already live on the server per the handoff.
define( 'ZOHO_CLIENT_ID', getenv( 'ZOHO_CLIENT_ID' ) ?: '' );
define( 'ZOHO_CLIENT_SECRET', getenv( 'ZOHO_CLIENT_SECRET' ) ?: '' );
define( 'ZOHO_REFRESH_TOKEN', getenv( 'ZOHO_REFRESH_TOKEN' ) ?: '' );
// Not itself sensitive, but kept out of hardcoded URLs the same way:
define( 'ZOHO_CREATOR_ACCOUNT_OWNER', getenv( 'ZOHO_CREATOR_ACCOUNT_OWNER' ) ?: '' );
```

## Compliance placeholders — do not remove or treat as final

- Footer disclosure text (in `footer.php`) is final short-form copy as
  provided in the handoff — reproduced exactly. Have real counsel/compliance
  sign off before public launch.
- "Form ADV / Disclosures" footer link is a stub (`js/main.js` shows an
  alert) pending a real destination.
- About page founder bio and Services pricing are explicitly marked TBD in
  the copy — do not replace with invented figures or claims.

## Fidelity notes

Colors, typography, spacing, copy, and interactions match
`design_handoff_wordpress_migration/Kaligirl Website.dc.html` exactly,
translated from that file's inline styles into `style.css`'s classes and
custom properties. The `.dc.html` file itself is a design-tool prototype
(depends on a proprietary `support.js` runtime) and is not used at runtime —
it exists only as the line-by-line visual/behavioral reference this theme
was built against.
