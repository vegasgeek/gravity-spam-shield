# Gravity Forms Spam Shield

Spam filters like reCAPTCHA and Cloudflare Turnstile cut down on spam — but they
don't stop all of it. As bots get better (and AI helps them beat challenges), some
junk always slips through. The real pain isn't the spam entry itself; it's that
**every submission fires an admin notification email**, so a single automated run can
bury an inbox under hundreds or thousands of messages overnight.

**Gravity Forms Spam Shield catches what your spam filter misses.** It scores each
submission as it comes in, silently quarantines the spammy ones as native Gravity
Forms spam — so no per-submission email goes out — and then sends the admin a single
**daily summary** of what was blocked. The flood stops, the inbox stays clean, and you
still get a once-a-day digest to review for anything caught by mistake.

It runs *alongside* reCAPTCHA/Turnstile, not instead of them — a second line of
defense that you control.

## How it works
- Scores every Gravity Forms submission with a tunable, weighted engine.
- Anything at or above your threshold is marked as Gravity Forms spam (no admin email)
  and recorded in a log.
- Once a day, if anything was blocked, it emails one digest summarizing it — with a
  link to the spam folder so you can rescue a false positive. Quiet day, no email.

## Detection
- **Heuristic signals (weighted):** gibberish names/text, honeypot field, submit
  timing & velocity, duplicate/repeated values.
- **Admin blocklists (instant block):** IP / CIDR ranges, email domains
  (incl. `*.tld` wildcards), and keywords.
- Per-signal on/off and weights, an overall score threshold, and per-form exclusions —
  all under **Forms → Spam Shield**. A read-only **Spam Shield Log** shows what's been
  caught and why.

## Requirements
- WordPress 6.4+
- PHP 8.1+
- Gravity Forms

## Install
Download the latest `vg-gravity-spam-shield-X.Y.Z.zip` from the
[Releases](../../releases) page and upload it via **Plugins → Add New → Upload Plugin**.
Configure it under **Forms → Spam Shield** (set at least one digest recipient).

Updates are delivered automatically from this repository's GitHub Releases.

## License
GPL-2.0-or-later
