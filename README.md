# AI SEO Content Plugin

<div align="center">

**Automated AI-powered SEO blog post generation for WordPress**  
Built by [Aliyan Faisal](https://aliyanfaisal.com) · Hebrew-first · OpenAI & Claude supported

![Version](https://img.shields.io/badge/version-1.0.0-6366F1?style=flat-square)
![WordPress](https://img.shields.io/badge/WordPress-5.8%2B-21759B?style=flat-square&logo=wordpress)
![PHP](https://img.shields.io/badge/PHP-7.4%2B-777BB4?style=flat-square&logo=php)
![License](https://img.shields.io/badge/license-GPL--2.0-green?style=flat-square)

</div>

---

## Overview

AI SEO Content Plugin connects your WordPress site to Jetben's central AI processing engine to automatically generate SEO-optimized blog posts. Define your keywords, tone, and publishing preferences — the plugin handles the rest.

Designed with a **Hebrew-first** approach, the plugin produces natural, human-sounding content with storytelling, local slang, and varied sentence structure for maximum SEO impact.

---

## Features

- 🔑 &nbsp;**Keyword targeting** — define target and negative keywords per site
- ✍️ &nbsp;**Writing style & tone control** — professional, conversational, journalistic, and more
- 🗣️ &nbsp;**Brand voice matching** — paste examples to match your exact tone
- 🌐 &nbsp;**Hebrew SEO content** — natural human-style writing with Hebrew slang support
- 🖼️ &nbsp;**Auto thumbnail generation** — AI-generated featured images per post
- 📷 &nbsp;**Stock image insertion** — relevant stock photos inserted inside articles
- 🗂️ &nbsp;**Auto-categorization** — posts are matched to the correct blog category automatically
- 🔗 &nbsp;**Internal linking** — provide URLs for automatic contextual linking
- 🕵️ &nbsp;**Competitor crawling** — crawl competitor or news domains for content inspiration
- ✅ &nbsp;**AI fact-checking layer** — secondary check to reduce inaccurate information
- 📅 &nbsp;**Flexible publishing** — auto-publish, pending review, or save as draft
- 🔐 &nbsp;**Domain-based licensing** — license is validated against your registered domain, no key needed
- 🚫 &nbsp;**Auto-disables on expiry** — plugin deactivates automatically when subscription ends

---

## Requirements

| Requirement | Minimum Version |
|-------------|----------------|
| WordPress   | 5.8+           |
| PHP         | 7.4+           |
| WooCommerce | 6.0+ *(host site only)* |

---

## Installation

1. Download the plugin ZIP from the [releases page](https://github.com/aliyanfaisal/AI-SEO-Post-Content-Generator-WordPress-Plugin/releases)
2. In your WordPress admin go to **Plugins → Add New → Upload Plugin**
3. Upload the ZIP and click **Install Now**, then **Activate**
4. Navigate to **AI SEO Content** in the sidebar
5. Go to **License** and click **Validate License** to activate against your domain

> **Note:** You must have an active subscription at [jetben.com](https://jetben.com) with your domain registered before validating.

---

## Plugin Structure

```
ai-seo-content-plugin/
├── ai-seo-content-plugin.php     # Main plugin bootstrap
├── admin/
│   ├── css/
│   │   └── admin.css             # Admin UI styles
│   ├── js/
│   │   └── admin.js              # AJAX interactions
│   └── views/
│       ├── dashboard.php         # Stats & quick actions
│       ├── settings.php          # All content preferences
│       ├── license.php           # License management
│       └── partials/
│           └── header.php        # Shared navigation header
└── includes/
    ├── class-aiscp-admin.php     # Admin menus, AJAX handlers, asset loading
    ├── class-aiscp-license.php   # Domain-based license validation
    └── class-aiscp-settings.php  # Settings get/save helpers
```

---

## Admin Pages

### Dashboard
Overview of plugin activity — total posts generated, this month's usage vs limit, keywords tracked, and last generation timestamp.

### Settings
Configure all content generation preferences:

| Section | Fields |
|---------|--------|
| **Keywords & Targeting** | Target keywords, negative keywords, internal links, competitor domains |
| **Writing Style & Tone** | Writing style, content tone, language, brand voice examples |
| **Publishing & Generation** | Publish mode, AI model (OpenAI / Claude), thumbnails, stock images, auto-categorize, fact-checking |

### License
Validate your subscription by domain. No license key is required — the plugin sends your site's domain to Jetben's licensing server which checks it against your active WooCommerce subscription.

---

## License & Activation Flow

```
User purchases subscription on jetben.com
         ↓
Registers their domain during checkout
         ↓
Installs this plugin on their WordPress site
         ↓
Clicks "Validate License" in the License page
         ↓
Plugin sends home_url() → Jetben License API
         ↓
API checks domain against active subscriptions
         ↓
Returns active/inactive status + plan data
```

---

## Roadmap

- [ ] Host plugin with WooCommerce subscription handling
- [ ] REST API endpoint for license validation (`/wp-json/aiscp/v1/validate`)
- [ ] Central AI processing engine (OpenAI GPT-4 + Claude modules)
- [ ] Automated post scheduling and cron generation
- [ ] AI-generated media (Advanced plans)
- [ ] Multi-language expansion beyond Hebrew/English/Arabic

---

## Development

This repository contains the **client plugin** only. The host plugin (installed on the Jetben platform) is maintained separately and handles licensing, AI processing, and post delivery.

To contribute or report issues, open a GitHub Issue or pull request.

---

## Author

**Aliyan Faisal**  
[https://aliyanfaisal.com](https://aliyanfaisal.com)

---

<div align="center">
<sub>Built with ❤️ for the SEO content market</sub>
</div>
