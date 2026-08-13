# Kaligirl Financial Services — WordPress theme

Custom WordPress theme (not a page builder export) implementing the design in
`design_handoff_wordpress_migration/`, wired to Paid Memberships Pro for login
and membership gating (unrelated to the change below — left as-is; see the
"Get Started flow" section for why). Get Started is a Zoho-native,
token-gated flow per a later migration handoff that **drops Moxo entirely**
— do not reintroduce the Moxo iframe.

## Visual redesign — "Terracotta" theme (v2.0)

The theme was re-skinned to match a second design handoff (new
`design_handoff_wordpress_migration/README.md` + `Kaligirl Website.dc.html`,
"Terracotta" color theme instead of the original "Ocean" one). What changed:

- **Design tokens** (`style.css` `:root`): renamed to semantic names
  (`--primary`, `--primary-hover`, `--accent`, `--accent-soft`,
  `--cta-band-hover`, `--text-heading`, `--bg`, `--header-bg`, `--card-bg`,
  `--shadow-mega`) with new Terracotta values. If you add new CSS, use these
  tokens, not hardcoded hex values — a few pre-existing hardcoded gradients
  (home "How we help", Services "Fee structure") were converted to tokens as
  part of this pass; watch for the same mistake in future additions.
- **Mega menus**: "Services" and "Resources" in the logged-out desktop nav
  are now hover-triggered mega menus (`.mega-trigger` / `.mega-menu` in
  `header.php` + `style.css`, hover-open/120ms-close-delay logic in
  `js/main.js`'s `kaligirlInitMegaMenus()`). Mobile menu gets the same links
  as plain indented sub-links instead. This is a **different, unrelated**
  "Resources" from the logged-in-only Library/Lessons/Tools dropdown, which
  is untouched.
- **Two new interactive pages**: `page-personal-consulting.php` and
  `page-business-advisory.php` — a grid of topic cards that expands in
  place into a single detail card on click (no page navigation), via
  `kaligirlInitConsultingCards()` in `js/main.js` and the
  `.consulting-*` CSS component. Card copy (title/body/detail) is defined
  as a PHP array at the top of each template — edit there, not in the
  template tags helper, since this content is specific to these two pages.
- **New public Resources page**: `page-resources.php` — a "coming soon"
  placeholder per the design handoff, linked from the Resources mega menu.
- **`.hero-wash` utility**: the radial-gradient background wash behind
  every page's hero section is now a shared class
  (`kg-section--no-border hero-wash`) instead of one-off inline styles.
  Applied to Home, Services, About, Contact, Get Started, Booking, Payment,
  and the two new consulting pages. Deliberately *not* applied to the
  invalid-token error states (`.resource-hero`) or the Thank You page,
  matching the design handoff.
- **Footer gradient**: `.site-footer` now fades from `--bg` into
  `--header-bg`, matching the new design's footer.
- **Get Started page — explicitly NOT redesigned architecturally.** Per
  explicit instruction, the Personal/Business toggle, Route One/Two
  buttons, the two embedded Zoho Forms, and the token-gated
  booking/payment/thank-you flow are all preserved exactly as before — only
  colors/typography inherit the new tokens automatically via the CSS
  variable cascade. The new design handoff's mockup shows a simpler
  single-Zoho-Bookings-widget Get Started page; that was **not** adopted.
- MemberPress/PMP is unrelated to this redesign round too and was left
  untouched, consistent with the original "you make the call" decision
  documented in the "MemberPress / PMP note" section below.

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
├── page-personal-consulting.php # Template Name: Personal Financial Consulting (click-to-expand cards)
├── page-business-advisory.php   # Template Name: Business Advisory (click-to-expand cards)
├── page-resources.php        # Template Name: Resources (public, "coming soon" placeholder)
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
   - `personal-consulting`, `business-advisory`, `resources` → matching
     templates (linked from the Services/Resources mega menus — the
     mega-menu links 404 until pages with these exact slugs are published)
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
   iframe instead of taking over the tab. `js/main.js`
   (`kaligirlBreakoutIframeOnSameOrigin()`) listens for the iframe's `load`
   event and tries to read `iframe.contentWindow.location`; that throws
   (cross-origin) while it's still showing Zoho's domain, but succeeds the
   instant Zoho's redirect lands it on our own domain — at which point it
   forces a real `window.top.location` navigation to that same URL.
   Confirmed working: submitting a form now takes over the whole tab
   instead of loading `/booking` nested inside the form iframe.

   The same problem shows up one step later on the Zoho **Bookings**
   widget on `/booking` — but the iframe-breakout trick alone **did not
   work there** in testing (it never even fired). Most likely explanation:
   `Bookings.inlineEmbed()` — "inline embed," as opposed to a sandboxed
   iframe embed — probably renders its calendar as same-origin DOM content
   rather than a true iframe, so there's no iframe for that trick to find.
   `js/main.js` now runs two mechanisms in parallel on `/booking`:
   1. `kaligirlWatchEmbedContainerForIframes()` — the same `MutationObserver`
      trick, kept in case the widget *does* use an iframe in some
      configuration.
   2. `kaligirlListenForBookingComplete()` — listens for a
      `window.postMessage()` from a Zoho domain (the standard way an
      embedded widget notifies its host page of an event, iframe or not)
      and redirects to `/thank-you` on a best-effort keyword match.
      **Not confirmed against the real message shape** — Zoho's docs
      blocked every fetch attempt this session, same as the prefill
      mechanism earlier. Every matching-origin message is also logged to
      the browser console specifically so this can be tightened after one
      real test: open dev tools, complete a real booking, check the
      Console tab for a line starting `Zoho Bookings postMessage:`, and
      report back what's there if it's still not redirecting.

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

### Route Two, Personal vs. Business forms

Route Two now holds **two** Zoho Forms embeds, not one — a Personal form
(the original "GetStarted" form) and a Business form
("LetsreviewyourfinancestogetherBusiness"), each Zoho's own real embed
script (verbatim, unmodified). Which one is visible is controlled by the
same Personal/Business toggle that already hides "Find the Right Plan" in
Business mode — `data-kg-personal-only` / `data-kg-business-only` wrap
each form's container, and `applyMode()` in `js/main.js` toggles both.
Both scripts still run at page load regardless of which is visible
(same as everything else on this page), and `loadRoute()` augments every
`div[id^="zf_div_"] iframe` it finds in the view, not just one, so
whichever form the toggle reveals is already carrying `gated_token`.

The "Continue" fallback link (based on live toggle state at click time)
and each form's own Zoho-side "Redirect URL on Submission" setting (based
on which form it is, fixed at prefill time — see below) both now carry
`&flow=personal` or `&flow=business` alongside `?token=...`, since
Personal and Business feed **different** Zoho Bookings services.
`page-booking.php` reads `?flow=` and picks the matching widget ID from a
small map (`personal` → `4946279000000039045`, `business` →
`4946279000000136026`); anything unrecognized or missing falls back to
`personal`.

**Manual setup needed — both forms, two fields each:**

Zoho Forms' "Redirect URL on Submission" only accepts a literal base URL
(`https://kaligirlfinancialservices.com/booking`) — the `?field=${zf:X}`
parts get appended by Zoho's own merge-field picker as you add fields to
it (the resulting leading `?&` is normal, that's just how the picker
builds an empty query string, not a bug). For the picker to have a
`${zf:X}` to insert, each form needs its own hidden/single-line text
field, prefilled from our iframe's URL, for each value being forwarded:

| Purpose | Prefill "Get value from URL parameter" name | Merge-tag used in the redirect |
|---|---|---|
| Gate token | `gated_token` | `${zf:<that field's Link Name>}` |
| Flow (personal/business) | `flow` | `${zf:<that field's Link Name>}` |

`js/main.js`'s `loadRoute()` already appends both `gated_token=...` and
`flow=personal`/`flow=business` (chosen structurally by which
`data-kg-personal-only`/`data-kg-business-only` wrapper the form's iframe
sits in — **not** from the toggle's current state, since both forms get
prefilled together at page load before the visitor has necessarily
touched the toggle) onto both forms' iframe src. So: confirm each form
has a field with "Prefill using URL parameter" set to `gated_token`, and
add a second field prefilled from `flow`, on **both** the Personal
("GetStarted") and Business ("LetsreviewyourfinancestogetherBusiness")
forms — each form's own Link Name for these fields is independent (Zoho
auto-numbers them per form), so check each form's actual Link Names
rather than assuming they're both literally `SingleLine2`/`SingleLine3`.

The Personal form's redirect (mirroring the Business one, same shape):

```
https://kaligirlfinancialservices.com/booking?&token=${zf:SingleLine2}&flow=${zf:SingleLine3}
```

(swap in the staging domain while testing, same as everywhere else in
this doc). This is in addition to — not instead of — the existing
`Name`/`Email`/`Contact Number` merge tags on the Personal form's
redirect (see "Autofilling Name/Email/Phone" above); those still matter
for the fork-first path where someone fills the form before booking, so
don't drop them when adding token/flow.

### Route Two, booking-first variant

Route Two can also be entered in the opposite order — book first, answer
the rest of the questions after — for whichever Zoho Bookings service is
configured to redirect back to `/get-started` on completion. Zoho
Bookings' own post-booking redirect setting supports merge fields for the
customer's own submitted info (native Bookings vocabulary:
`customer_name`, `customer_first_name`, `customer_last_name`,
`customer_contact_no`, `customer_email` — confirmed against a real
completed booking's redirect URL). `page-get-started.php` reads these off
`$_GET`; if `customer_email` is present and valid, the Personal/Business
fork is skipped entirely and Route Two's form is shown immediately,
pre-populated via `data-kg-booking-prefill` (a JSON blob of whatever
customer_* values were present) on that view.

Route Two's form embed is the **real Zoho-provided embed script**
(dynamic iframe creation + UTM/referrer tracking + auto-resize
postMessage listener — pasted verbatim, do not hand-edit; replace the
whole block if Zoho reissues the embed code) rather than a static
`<iframe>` tag, since Zoho's script builds the iframe itself into a
`<div id="zf_div_...">`. Because of that, `loadRoute()` in `js/main.js`
can't set `iframe.src` directly the way it does for Route One's static
iframe — instead it finds the iframe Zoho's script already created
(`div[id^="zf_div_"] iframe`) and **appends** `gated_token` plus the
`customer_*` prefill params onto whatever src Zoho already built, so its
own UTM/referrer params survive intact.

**Not yet confirmed:** whether the Name/Email/Phone fields on this form
actually have "Prefill using URL parameter" turned on with parameter
names matching `customer_name` / `customer_email` / `customer_contact_no`
— that mapping is a per-field setting in the Zoho Forms field editor, not
visible from the embed script, and can't be confirmed from code. Test by
opening the form's iframe URL directly with e.g. `&customer_email=test@test.com`
appended; if the Email field doesn't populate, go set that field's
prefill parameter name to match (or tell me what it's already set to and
the theme's query param names can be changed to match instead).

When arriving via this booking-first path, the "Continue" fallback link
points at `/thank-you` instead of `/booking` (the booking already
happened) — the normal fork-first path into Route Two still points at
`/booking` as before.

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
