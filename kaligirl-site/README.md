# Kaligirl Financial Services — website

Static site foundation for kaligirlfinancialservices.com. Plain HTML/CSS/JS,
no build step, deployable to any web host. Built as a clean base to hone in
Claude Design and implement in Claude Code.

## Structure
```
kaligirl-site/
├── index.html          # Home
├── services.html       # Services
├── contact.html        # Contact
├── get-started.html    # New-client intake (embeds Moxo onboarding)
├── login.html          # Existing-client sign-in
├── css/styles.css      # All styles + design tokens (:root)
├── js/main.js          # Minimal interactions
├── assets/logo.png     # Kali Girl wordmark + flower mark, used in the navbar
└── README.md
```

Design tokens (color, type, scale) live in `:root` at the top of `css/styles.css`.
Change them there and the whole site follows — this is also what Claude Design
reads when it imports the design system.

## The workflow this is built for

1. **Put it in Git first.** This repo is the single source of truth — not a
   Claude Project. Everything below points at it.
   ```
   cd kaligirl-site
   git init && git add . && git commit -m "Site foundation"
   # push to a GitHub repo
   ```
2. **Hone visuals in Claude Design.** Import this repo (GitHub URL, upload, or
   `/design-sync` from inside Claude Code). Iterate on the canvas, then use the
   handoff to send changes back to Claude Code.
3. **Implement in Claude Code.** Run `claude` in this directory. Code edits these
   actual files and receives Design handoff bundles.
4. **Deploy to Hostinger *web hosting*** (not the Website Builder — that's a
   separate, closed product). Upload via File Manager / FTP, or connect the repo
   with Hostinger's Git deploy, then point the domain here.

## Moxo
Regulatory intake, e-signature, secure document exchange, and the audit trail
live in Moxo. The onboarding iframe is already embedded in `get-started.html`
inside the `#moxo-embed` container (see the `MOXO INTEGRATION POINT` comment)
— swap the `src` if the Moxo flow URL ever changes. `login.html` is a static
sign-in form for existing clients; see the `AUTH INTEGRATION POINT` comment
there to point it at Moxo's client-portal login (or replace it with Moxo's
embedded login flow) once that URL is available.

## ⚠️ Before launch — compliance
This is a **state-registered fiduciary adviser's** site. The current copy is
deliberately conservative, but every `[placeholder]` and every `.disclosure`
block must be reviewed and finalized by MB's compliance resource:

- No testimonials, no client-count or performance stats (the old builder site
  had fabricated ones — do not reintroduce them).
- Fill in approved disclosure language, Form ADV link, and registration details.
- Confirm the contact email and the Moxo onboarding/login links resolve.

Not legal advice — have the advisor's compliance contact sign off on wording.
