# Changelog

All notable changes to `laravel-instagram-digest` will be documented in this file.

## 1.0.0 - 2026-04-20

Initial release.

### Added

- Apify hashtag scraper with keyword and minimum-follower filters.
- `instagram_digest_profiles` and `instagram_digest_runs` tables.
- Daily Telegram digest with inline action buttons.
- Three default actions: `Interesting`, `Reject`, `Show again later` (repropose).
- Pluggable custom actions via `InstagramDigest::registerAction()`.
- Card rendering via publishable Blade view and `CardRenderer` contract.
- Resolver pattern for hashtags, keywords, min-followers, chat id, daily count.
- Webhook controller with secret token verification.
- Events: `ProfileDiscovered`, `ProfileStatusChanged`, `DigestSent`, `ScrapingRunCompleted`.
- Artisan commands: `scrape`, `send`, `webhook`, `demo`.
- English and Italian translations.
