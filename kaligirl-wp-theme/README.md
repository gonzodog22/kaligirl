# Kaligirl Financial Services — WordPress theme

Custom WordPress theme (not a page builder export) implementing the design in
`design_handoff_wordpress_migration/`, wired to Paid Memberships Pro for login
and membership gating. Get Started uses a custom intake form (see below)
rather than the Moxo embed originally specified in the handoff.

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
├── page-get-started.php      # Template Name: Get Started (custom intake form + calendar embed)
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
│   └── security.php          # See "Security" below
├── js/main.js                # Mobile menu + Resources dropdown toggles
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

## Get Started: custom intake form (replaces the earlier Moxo embed)

The Get Started page (`page-get-started.php`) no longer embeds Moxo. It's a
public, unauthenticated custom intake form — collects contact info, a
required "what brings you here" segment (Personal financial planning /
Business or CFO services), segment-specific follow-up fields, an optional
"how did you hear about us," and a consent checkbox — plus a Google
Calendar booking embed so either segment can grab a real meeting slot
directly. Deliberately **does not** collect SSN/DOB/account numbers/
balances/income, and has no file upload — that stays behind authentication,
later, once a lead is qualified.

Two placeholders need real values before launch:

1. **`KALIGIRL_INTAKE_WEBHOOK_URL`** at the top of `js/main.js` — the form
   POSTs the submitted JSON straight to this URL from the browser (a
   Zapier/Make/n8n catch hook, or your own endpoint); nothing server-side in
   WordPress sees this data. The JSON payload always includes `segment`
   explicitly, plus the shared fields, plus only the fields belonging to
   whichever segment was selected.
2. **`$kg_calendar_embed_url`** at the top of `page-get-started.php` — a
   published Google Calendar "Appointment schedule" URL
   (calendar.google.com/calendar/appointments), embedded as an iframe below
   the form so visitors can book a slot without a second login.

The form has an invisible honeypot field (`kaligirl_honeypot_field()`, same
helper used elsewhere in the theme) — since submission goes straight to an
external webhook rather than through WordPress, the honeypot check happens
client-side in `js/main.js`: a filled honeypot field silently drops the
submission with no feedback, rather than being logged server-side like the
login/checkout honeypots are.

Account is still a placeholder for a similar Moxo-linked (or other
portal-linked) dashboard once a client's specific portal URL/session is
available, per the design handoff — that part of the spec is unchanged.

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
    Moxo handles the site's actual document exchange today.
  - **Audit logging** (`kaligirl_security_log()`) to a directory outside the
    theme, protected by a deny-all `.htaccess`, capturing failed logins,
    honeypot triggers, rate-limit blocks, and repeated 404s.
  - Baseline hardening: XML-RPC disabled, WP version hidden, file editor
    disabled, security response headers.

### Secrets management

No API keys, DB credentials, or JWT secrets are hardcoded anywhere in this
theme. Use `kaligirl_secret( 'NAME', $default )` (in `inc/security.php`) to
read any future secret from an environment variable first, falling back to
a `wp-config.php` constant — never a committed value. Example
`wp-config.php` snippet (add outside version control, e.g. via your host's
environment/secrets manager):

```php
define( 'KALIGIRL_RECAPTCHA_SITE_KEY', getenv( 'KALIGIRL_RECAPTCHA_SITE_KEY' ) ?: '' );
define( 'KALIGIRL_RECAPTCHA_SECRET_KEY', getenv( 'KALIGIRL_RECAPTCHA_SECRET_KEY' ) ?: '' );
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
