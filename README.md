# AI SEO Content Generator

A WordPress plugin that generates SEO-optimized blog posts with Claude or OpenAI.

![WordPress 5.8+](https://img.shields.io/badge/WordPress-5.8%2B-21759b)
![PHP 7.4+](https://img.shields.io/badge/PHP-7.4%2B-777bb3)
![License GPL-2.0+](https://img.shields.io/badge/License-GPL--2.0%2B-blue)
![Version 2.0.0](https://img.shields.io/badge/version-2.0.0-green)

> **This is a WordPress plugin, not a standalone app.** It runs inside your WordPress site.
> There is no external server and no account to create: you bring your own AI provider key and
> the plugin does everything on your site.

Pick your AI provider (Anthropic Claude or OpenAI) and any model that provider offers, describe
your keywords, tone and structure once, and the plugin writes, illustrates and publishes posts on
a schedule or on demand. Each post comes with a title, excerpt, categories, tags, a featured image,
inline images and SEO meta for your SEO plugin.

## Requirements

- WordPress 5.8 or newer
- PHP 7.4 or newer, with libsodium or OpenSSL (AES-256-GCM) for key encryption
- An API key from [Anthropic](https://console.anthropic.com/) or [OpenAI](https://platform.openai.com/)
- Optional: a free key from Pexels, Unsplash or Shutterstock for stock images

## Installation

**From the WordPress admin**

1. Download the plugin zip.
2. Go to **Plugins → Add New → Upload Plugin**, choose the zip and click **Install Now**.
3. Click **Activate**.

**Manually**

1. Copy the `ai-seo-content-generator` folder to `wp-content/plugins/`.
2. Activate it under **Plugins**.

If you used the earlier AI SEO Content client plugin, your settings are migrated automatically on
activation. API keys were never stored by that plugin, so enter your key again under
**AI Provider**.

## Configuration

### API keys

Open **AI SEO Content → AI Provider**, choose a provider, paste its API key and save. Keys are
encrypted in the database and never shown again; only a masked hint is displayed.

You can also define keys in `wp-config.php`. A constant overrides the stored value and the UI shows
"set via wp-config":

```php
define( 'AISCG_ANTHROPIC_API_KEY',       'sk-ant-...' );
define( 'AISCG_OPENAI_API_KEY',          'sk-...' );
define( 'AISCG_PEXELS_API_KEY',          '...' );
define( 'AISCG_UNSPLASH_API_KEY',        '...' );
define( 'AISCG_SHUTTERSTOCK_API_KEY',    '...' );
define( 'AISCG_SHUTTERSTOCK_API_SECRET', '...' );
```

### Choosing a model

The model dropdown is filled from your provider's own model list using your saved key, cached for
about 12 hours. Use **Refresh models** to reload it. OpenAI's list is filtered to chat models
(embedding, audio, image and moderation models are hidden). If the list cannot be loaded, a short
built-in list is shown instead. The **Custom model ID** field lets you use a model the moment it is
released, without waiting for a plugin update.

Use **Test connection** to send a tiny request and see the model, response time, and a clear error
message if something is wrong.

### Settings tabs

| Tab | What you set |
|---|---|
| **Dashboard** | Posts generated, monthly usage, last run, next scheduled run, failed jobs, **Generate now** |
| **AI Provider** | Provider, API key, model, custom model ID, max output tokens, temperature, request timeout, test connection, debug logging |
| **Content** | Target and negative keywords, tone, writing style, language, brand voice, restrictions, section prompts, global instructions, reference URLs, internal links, sitemap URL, accuracy and inspiration hints |
| **Images** | Stock image provider and keys, featured image, inline images |
| **Publishing & Schedule** | Post status, author, category and tag behaviour, interval, start time, posts per run, monthly cap |
| **Log** | Recent activity and a list of failed jobs with their errors |

## How it works

```
 WP-Cron schedule            "Generate now" / WP-CLI
        │                              │
        └──────────────┬───────────────┘
                       ▼
                  Job queue  ── one post per run, 2-minute chain, lock against overlap
                       ▼
        Prompt builder (settings + context providers)
          · cached system blocks (instructions, section prompts)
          · reference URLs fetched and reduced to readable text
          · sitemap links for internal linking
                       ▼
          AI provider (Anthropic or OpenAI)
                       ▼
        Response parser (delimiter format; salvages truncated output)
                       ▼
        Stock images → sideloaded into the Media Library
                       ▼
        Post inserted + SEO meta written to your SEO plugin
```

Everything happens inside your site's own process. There is no relay server, no callback URL and
no public endpoint.

## Features

- **Two providers.** Anthropic Claude (Messages API, with prompt caching on the instruction blocks)
  and OpenAI (Chat Completions). A third provider is one new class plus one registry entry.
- **Any model.** Live model list, custom model ID override, automatic retry without a parameter the
  model rejects (for example `temperature` on reasoning models).
- **Any language.** Free-text language setting, RTL admin and content support, and an optional
  per-language style hint (a Hebrew hint is included).
- **Structured output.** Title, excerpt, categories, tags, focus keyword, Open Graph and Twitter
  text, schema type, and image queries from a single request. If the model's answer is cut off, the
  plugin keeps everything valid and can ask for a continuation.
- **Section prompts.** Define named sections and a prompt for each; the model must follow the
  structure in order.
- **Reference URLs.** Each URL is fetched server-side, reduced to readable text, trimmed to a token
  budget and given to the model as source material.
- **Internal linking.** Provide URLs by hand and/or a sitemap; relevant links are offered to the
  model.
- **Images.** Pexels, Unsplash or Shutterstock. A featured image is set and `{{IMAGE_N}}`
  placeholders in the content become Gutenberg image blocks. Images are downloaded only from the
  chosen provider's hosts.
- **SEO meta.** Written for Yoast SEO, Rank Math, All in One SEO, SEOPress, Slim SEO, The SEO
  Framework and Squirrly SEO (only for the ones that are active).
- **Scheduling.** Daily, every 3 days, weekly, every 2 weeks or monthly, at a start time you
  choose, with a number of posts per run. Posts are generated one at a time, two minutes apart.
- **Cost controls.** Monthly post cap, posts per run, max output tokens.
- **Reliable runs.** A lock prevents overlapping runs, each job has a status, failed jobs are retried
  up to twice with backoff, and permanent failures are listed with their error.
- **Generate now without waiting.** The button queues one job and the page polls its status.
- **WP-CLI.** `wp aiscg generate --count=3`
- **Publishing options.** Draft, pending or published; choose the author; create new categories and
  tags or only use existing ones.
- **Prompt hints.** "Accuracy reminder" and "Topics/sites for inspiration" add instructions to the
  prompt. They are hints to the model; the plugin does not verify facts or crawl those sites.

## Supported SEO plugins

Yoast SEO · Rank Math · All in One SEO (AIOSEO) · SEOPress · Slim SEO · The SEO Framework ·
Squirrly SEO

## Hooks and filters

| Hook | Type | Arguments | Use |
|---|---|---|---|
| `aiscg_system_blocks` | filter | `array $blocks, array $job` | Add, remove or edit the system prompt blocks before the request. |
| `aiscg_user_prompt` | filter | `string $prompt, array $job` | Change the user prompt. |
| `aiscg_post_data` | filter | `array $post_data, array $job` | Adjust the parsed post (title, content, tags, SEO meta) before insertion. |
| `aiscg_before_insert_post` | action | `array $post_data, array $job` | Run code right before `wp_insert_post()`. |
| `aiscg_after_insert_post` | action | `int $post_id, array $post_data, array $job` | Run code after the post, images and SEO meta are saved. |
| `aiscg_providers` | filter | `array $providers` (slug => class name) | Register an additional AI provider implementing `AISCG_Provider`. |
| `aiscg_context_providers` | filter | `array $providers` | Register additional sources of context for the prompt, implementing `AISCG_Context_Provider`. |

Example, appending a rule to every request:

```php
add_filter( 'aiscg_user_prompt', function ( $prompt, $job ) {
	return $prompt . "\nAlways mention our free shipping policy once.";
}, 10, 2 );
```

## Extending

**Add an AI provider.** Create a class implementing `AISCG_Provider`
(`generate( $system_blocks, $user_prompt, $options )`), then register it:

```php
add_filter( 'aiscg_providers', function ( $providers ) {
	$providers['my-provider'] = 'My_Provider';
	return $providers;
} );
```

**Add context.** Implement `AISCG_Context_Provider` (`get_context( array $job ): array`, returning
snippets with source URLs) and register it with `aiscg_context_providers`. The sitemap links and the
reference URLs are themselves context providers.

## Security

- API keys are encrypted at rest (libsodium secretbox, or AES-256-GCM, both authenticated) with a
  key derived from your WordPress salts. They are masked in the UI and never echoed back.
- Keys can live in `wp-config.php` instead of the database.
- The plugin registers **no public REST routes and no unauthenticated endpoints.**
- Every admin action checks a nonce and the `manage_options` capability.
- Input is sanitized, output is escaped, and generated HTML passes through `wp_kses_post`.
- Server-side fetches (reference URLs, sitemaps, images) use `wp_safe_remote_get`, allow only
  http(s) on ports 80/443, refuse private, loopback and link-local addresses, and have size and time
  limits. Images are downloaded only from the selected stock provider's hosts.
- Debug logging is off by default, size-capped, and never records API keys.

## Privacy

Your prompts, settings text, and any reference page text are sent to the AI provider you select
(Anthropic or OpenAI), and image search terms are sent to the stock image provider you select. Review
their privacy policies before use. Nothing is sent to the plugin author.

## Costs

The plugin is free. You pay your AI provider (and optionally your stock image provider) directly for
what you use. Set **Max output tokens**, **Posts per run** and **Monthly post cap** to keep spending
predictable.

## Roadmap

**Retrieval over your own posts.** A future module will embed your existing posts and use them to
ground new articles in your brand voice and facts, suggest internal links from related posts, and
avoid overlapping topics. The groundwork (the context provider interface) is already part of the
plugin. See [docs/rag-roadmap.md](docs/rag-roadmap.md).

## Screenshots

Screenshots will be added to this section:

1. Dashboard
2. AI Provider tab with model picker
3. Content tab with section prompts
4. Publishing & Schedule tab
5. Log with failed jobs
6. A generated post with SEO meta

## FAQ

**Do I need an account with you?** No. Only an API key from Anthropic or OpenAI.

**Which model should I use?** Any chat model your key can access. Larger models write better long
posts; smaller ones are cheaper. Use the custom model ID field for brand-new models.

**Does it work with any SEO plugin?** It writes meta for the seven plugins listed above. With none
installed, posts are still created without SEO meta.

**Why is a post a draft?** The default status is Draft so you can review. Change it under
**Publishing & Schedule**.

**Can I write in a language other than English?** Yes, type any language in the Language setting.

**Does the schedule run if nobody visits my site?** WP-Cron needs site traffic to fire. For exact
timing, trigger `wp-cron.php` from a real server cron job, or run `wp aiscg generate`.

**Can I stop it overspending?** Yes: monthly post cap, posts per run and max output tokens.

## Contributing

Issues and pull requests are welcome. Run `composer install` and `vendor/bin/phpunit` before
submitting a change, and keep `php -l` clean on PHP 7.4.

## License

GPL-2.0-or-later. See [LICENSE](LICENSE).

Author: [Aliyan Faisal](https://aliyanfaisal.com)
