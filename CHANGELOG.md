# Changelog

All notable changes to `laravel-instagram-digest` will be documented in this file.

## v1.0.0 - 2026-04-20

### v1.0.0

First stable release of `plin-code/laravel-instagram-digest`.

A Laravel package that scrapes Instagram hashtags via Apify, filters profiles by keyword and follower threshold, and sends a daily Telegram digest with inline action buttons. Tap a button, classify a candidate, next card.

#### Highlights

- Apify hashtag scraper with keyword and minimum-follower filters.
- Daily Telegram digest with three default inline actions: Interesting, Reject, Show again later.
- Pluggable custom actions via `InstagramDigest::registerAction(key, label, handler)`.
- Card rendering via a publishable Blade view, or a custom `CardRenderer` contract for full control.
- Resolver pattern for hashtags, keywords, min-followers, chat id, daily count (closures on the facade, with plain config as fallback).
- Webhook controller with constant-time secret verification.
- Four public events for consumer integration: `ProfileDiscovered`, `ProfileStatusChanged`, `DigestSent`, `ScrapingRunCompleted`.
- Four artisan commands: `scrape`, `send`, `webhook`, `demo`.
- English and Italian translations out of the box.

#### Tech

- PHP 8.4+, Laravel 13 (Laravel 12 best-effort).
- PHPStan level 5 clean, 61 tests with 117 assertions, 90% line coverage.
- Built on `spatie/laravel-package-tools`.

#### Installation

```bash
composer require plin-code/laravel-instagram-digest
php artisan migrate

```
See the README for configuration and integration patterns, including how to consume the package from a CRM via events.

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
