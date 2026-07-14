# Kaligirl Financial Services — WordPress theme

Custom WordPress theme (not a page builder export) implementing the design in
`design_handoff_wordpress_migration/`, wired to Paid Memberships Pro for login
and membership gating and to Moxo for client onboarding.

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
├── page-get-started.php      # Template Name: Get Started (Moxo iframe)
├── page-login.php            # Template Name: Login (wraps core wp_login_form())
├── page-account.php          # Template Name: Account (membership-gated)
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
   Plugins > Add New > search "Paid Memberships Pro").
4. Create pages for each section and assign the matching template under
   Page Attributes (WordPress also auto-matches these by slug, e.g. a page
   at `/services/` picks up `page-services.php` automatically):
   - `/` or "Home" → Home
   - `services`, `about`, `contact`, `get-started` → matching templates
   - `account`, `library`, `lessons`, `tools` → matching templates (see gating below)
   - `login` → the **Login** template (a plain page you create yourself — PMP
     doesn't own a login page the way MemberPress did; the template wraps
     WordPress core's own login form in this theme's centered card).
5. Settings > Reading: leave "Your homepage displays" as-is — `front-page.php`
   renders the Home design regardless of that setting.
6. Settings > Permalinks: use a non-default structure (e.g. "Post name") so
   the slug-based URLs above resolve as expected.

## Paid Memberships Pro configuration (in wp-admin, not code)

- **Membership levels**: Memberships > Membership Levels — create Entry /
  Grow / Exceed levels matching the Services pricing tiers. Pricing is TBD
  per the design handoff — use placeholder pricing now, finalize before
  launch.
- **Page protection**: open each of `Account`, `Library`, `Lessons`, and
  `Tools` in the page editor and use the **Require Membership** box PMP adds
  to the page-edit screen — check the level(s) that should have access, so
  only logged-in/subscribed members can load these URLs directly (not just
  when reached via the Resources nav link). This is the primary access
  control; `kaligirl_require_login()` in `inc/membership.php` (called at the
  top of each of those templates) is a template-level backup in case that
  setting is missing or misconfigured — the two are meant to overlap, not
  substitute for each other.
- Leave `Home`, `Services`, `About`, `Contact`, `Get Started`, and `Login`
  unprotected.
- **reCAPTCHA**: Memberships > Settings > reCAPTCHA — enable v3 and supply
  site/secret keys as environment variables or wp-config constants (see
  Secrets below), per the security requirements.
- Login itself is WordPress core's own login form (styled by this theme's
  `page-login.php` + the "Login form overrides" section of `style.css`) —
  PMP doesn't need to be configured for login, only for levels and page
  protection.

## Moxo

The Get Started page (`page-get-started.php`) embeds the iframe exactly as
given in the handoff:

```html
<iframe src="https://app.moxo.com/embed/de789728-f434-4be6-9a47-218400bf7d8d" width="100%" height="600" frameborder="0"></iframe>
```

Moxo owns onboarding, e-signature, secure document exchange, and chat from
here on — none of that is reimplemented in WordPress. The Account page is a
placeholder for a similar Moxo-linked dashboard once the client's specific
portal URL/session is available.

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
  - **Honeypot fields** on the core login form (`wp-login.php` and the
    theme's Login page, which renders the same form) and on Paid
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
