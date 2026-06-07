# Gravity Forms Spam Shield

Scores Gravity Forms submissions, quarantines spam as native Gravity Forms spam
(so spammers don't trigger a per-submission admin email), logs each one, and sends
a single **daily digest** instead of a flood of notifications.

## What it does
- **Weighted scoring engine** with a tunable threshold and per-signal weights.
- **Heuristic signals:** gibberish text, honeypot field, submit timing & velocity,
  duplicate/repeated values.
- **Admin-curated blocklists** (instant block): IP/CIDR, email domain, keyword.
- **Daily digest email** summarizing what was blocked (sends nothing on a quiet day).
- **Read-only log viewer** under the Forms menu.
- **Self-updates** from this repo's GitHub Releases.

## Requirements
- WordPress 6.4+
- PHP 8.1+
- Gravity Forms

## Install
Download the latest `vg-gravity-spam-shield-X.Y.Z.zip` from the
[Releases](../../releases) page and upload it via **Plugins → Add New → Upload Plugin**.
Settings live under **Forms → Spam Shield**.

## License
GPL-2.0-or-later
