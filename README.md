# AI SEO Content Generator

A WordPress plugin that generates SEO-optimized blog posts with Claude or OpenAI.

![WordPress 5.8+](https://img.shields.io/badge/WordPress-5.8%2B-21759b)
![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb3)
![License GPL-2.0+](https://img.shields.io/badge/License-GPL--2.0%2B-blue)
![Version 2.0.0](https://img.shields.io/badge/version-2.0.0--dev-orange)

> **This is a WordPress plugin, not a standalone app.** It runs inside a WordPress site.
> There is no external server, account, or license to set up: you bring your own AI provider key.

> **Status: work in progress.** This branch is a rewrite that merges the former client and host
> plugins into one self-contained plugin. The foundation is in place; the generation pipeline and
> admin UI are being added step by step. The sections below say what exists today and what is planned.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer (libsodium or OpenSSL with AES-256-GCM for key encryption)
- An API key from Anthropic or OpenAI

## Installation

Not yet installable as a finished plugin. When the first release is ready:

1. In WordPress go to **Plugins → Add New → Upload Plugin** and upload the zip, or copy the
   `ai-seo-content-generator` folder to `wp-content/plugins/`.
2. Activate it.

## What exists today

- Plugin skeleton, autoloader and requirement checks (PHP 7.4+, WordPress 5.8+).
- Encrypted API key storage (libsodium secretbox, or AES-256-GCM as a fallback), with keys derived
  from your WordPress salts. Keys can also be defined in `wp-config.php`:
  `AISCG_ANTHROPIC_API_KEY`, `AISCG_OPENAI_API_KEY`, `AISCG_PEXELS_API_KEY`,
  `AISCG_UNSPLASH_API_KEY`, `AISCG_SHUTTERSTOCK_API_KEY`, `AISCG_SHUTTERSTOCK_API_SECRET`.
- Settings storage with sanitization for every field.
- One-time migration of the old client plugin's `aiscp_*` options to `aiscg_*`. API keys cannot be
  migrated, so re-enter them after upgrading.
- A safe fetch helper for URLs: http(s) only, public IPs only, size and time limits.
- Unit tests for the above (`composer install && vendor/bin/phpunit`).

## Planned (not implemented yet)

- Provider choice (Anthropic Claude or OpenAI) with a model picker and a "Test connection" button
- Scheduled and on-demand post generation with SEO meta for Yoast, Rank Math, AIOSEO, SEOPress,
  Slim SEO, The SEO Framework and Squirrly
- Stock images from Pexels, Unsplash or Shutterstock
- Tabbed admin UI, WP-CLI command, and a hooks/filters API
- Later: retrieval over your site's existing posts (see the roadmap, once written)

## Privacy and costs

Prompts and any fetched page text are sent to the AI provider you choose. You pay your provider
directly for usage; the plugin itself is free.

## Contributing

Issues and pull requests are welcome. Run `composer install` and `vendor/bin/phpunit` before
submitting a change.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

Author: [Aliyan Faisal](https://aliyanfaisal.com)
