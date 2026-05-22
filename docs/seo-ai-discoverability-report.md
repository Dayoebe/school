# SEO and AI Discoverability Report

## 1. Project stack detected
- Framework: Laravel 10 PHP application with Livewire 3, Blade templates, Tailwind CSS, Vite, Alpine/Livewire client behavior.
- Rendering: public pages are server-rendered Blade/Livewire views with some Alpine enhancements; authenticated dashboard/result/CBT areas are private.
- Public routes: `/`, `/about`, `/admission`, `/gallery`, `/contact`.
- Private routes: dashboard, auth, account applications, admissions dashboard, contacts dashboard, students, teachers, parents, fees, results, CBT, timetable, subjects, sections, admins, users, API, Livewire internals.
- Metadata location: global `<head>` in `resources/views/layouts/app.blade.php`; dashboard/result/exam layouts have separate heads.
- Sitemap/robots/AI files: generated through `App\Http\Controllers\SeoController`.
- Production domain status: TODO. `.env` currently has `APP_URL=http://localhost:8000`; set the real production domain before submitting sitemaps.

## 2. SEO issues found
- Public metadata existed but was generic and inconsistent across public pages.
- Non-article public pages were using article-like Open Graph type behavior.
- Dashboard/result/exam layouts were indexable.
- Sitemap was a single flat URL set and did not include image sitemap support.
- Robots policy blocked some private paths but did not clearly separate public assets, AI discovery crawlers, and training crawler policy.
- No `/llms.txt`, `/llms-full.txt`, or `/ai.txt` endpoint existed.
- FAQ content on key public pages was mostly JavaScript-rendered instead of crawlable server HTML.
- Public forms had visible labels but no explicit `for`/`id` associations.

## 3. AI discoverability issues found
- No concise AI-readable site summary existed.
- No fuller agent-readable digest existed.
- Citation guidance, freshness guidance, sitemap links, and priority public pages were not exposed in a machine-friendly text file.
- Core identity was present in public content and settings, but not consolidated for LLM/agent retrieval.

## 4. Files changed
- `app/Http/Controllers/SeoController.php`
- `app/Support/PublicSeo.php`
- `routes/web.php`
- `resources/views/layouts/app.blade.php`
- `resources/views/layouts/dashboard.blade.php`
- `resources/views/layouts/result.blade.php`
- `resources/views/layouts/exam.blade.php`
- `resources/css/app.css`
- `resources/views/livewire/site/home.blade.php`
- `resources/views/livewire/site/about.blade.php`
- `resources/views/livewire/site/admission.blade.php`
- `resources/views/livewire/site/contact.blade.php`
- `resources/views/livewire/site/gallery.blade.php`
- `resources/views/livewire/admissions/public-admission-form.blade.php`
- `resources/views/livewire/contacts/public-contact-form.blade.php`
- `resources/views/livewire/result/pages/print.blade.php`
- `resources/views/livewire/result/pages/print-class.blade.php`
- `resources/views/livewire/result/pages/class-spreadsheet-pdf.blade.php`
- `resources/views/cbt/print-result.blade.php`
- `resources/views/cbt/print-summary.blade.php`
- `resources/views/partials/header.blade.php`
- `resources/views/partials/footer.blade.php`
- `resources/views/seo/sitemap.blade.php` removed

## 5. Files created
- `app/Support/PublicSeo.php`
- `resources/views/partials/public-page-summary.blade.php`
- `resources/views/seo/sitemap-index.blade.php`
- `resources/views/seo/sitemap-pages.blade.php`
- `resources/views/seo/sitemap-images.blade.php`
- `docs/seo-ai-discoverability-report.md`

## 6. Schema types implemented
- `EducationalOrganization`
- `School`
- `WebSite`
- `WebPage`
- `AboutPage`
- `ContactPage`
- `CollectionPage`
- `BreadcrumbList`
- `FAQPage` on pages with visible FAQ content
- `Service` for the admission registration page
- `ContactPoint`
- `PostalAddress` where public address data exists

## 7. Sitemap and feed status
- `/sitemap.xml` is now a sitemap index.
- `/sitemap-pages.xml` lists public pages only.
- `/sitemap-images.xml` lists the public logo/social image and active gallery images where available.
- No public article, news, blog, podcast, or RSS/Atom feed was found, so no feed discovery tag was added.

## 8. robots.txt policy
- Public pages and rendering assets are crawlable.
- Private portal, authentication, student, result, CBT, admin, Livewire, Sanctum, and API routes are blocked.
- `Googlebot`, `Bingbot`, `DuckDuckBot`, and `Slurp` are explicitly allowed for public crawl with the same private-path restrictions.
- `OAI-SearchBot`, `ChatGPT-User`, and `PerplexityBot` are allowed for public discovery with the same private-path restrictions.
- `GPTBot`, `Google-Extended`, and `CCBot` are disallowed by default because training-crawler consent was not confirmed.

## 9. llms.txt and llms-full.txt summary
- `/llms.txt` now exposes a concise Markdown overview, core pages, main topics, priority content, citation guidance, and freshness guidance.
- `/llms-full.txt` now exposes a fuller AI-readable digest with overview, public offerings, audience, mission/vision, key pages, contact information, content categories, sitemaps, and citation rules.
- `/ai.txt` adds short AI/citation and crawler-policy guidance.

## 10. Performance and accessibility improvements
- Removed public CDN CSS dependencies for Font Awesome and Animate.css by relying on the local Vite bundle and a small local animation utility.
- Added image width/height, lazy loading, and async decoding where safe.
- Added public page summaries and visible breadcrumbs for better scanning and crawl context.
- Converted public FAQ content to server-rendered Blade markup.
- Added explicit labels/IDs for public contact and admission form controls.
- Added iframe title text for the public map.
- Marked private dashboard/result/CBT/print surfaces `noindex, nofollow, noarchive`.
- Made SEO text endpoints stateless to avoid session cookies.

## 11. Remaining TODOs
- Set the production `APP_URL` to the real HTTPS domain.
- Confirm the official brand name spelling: the code currently uses both "Elites International School" and older "Elite/Elites International College" text in some private print templates.
- Confirm whether training crawlers such as `GPTBot`, `Google-Extended`, and `CCBot` should remain blocked or be allowed.
- Add real social profile URLs in site settings if the school has official profiles.
- Add an RSS/news/blog system only if the school plans to publish public articles or news.
- Replace fallback/gallery stock images with controlled school-owned media where possible.

## 12. Manual steps for the site owner
- Set `APP_URL` to the production HTTPS domain and clear/rebuild config cache.
- Submit `/sitemap.xml` in Google Search Console.
- Submit `/sitemap.xml` in Bing Webmaster Tools.
- Verify `/robots.txt` after deployment.
- Test important public pages in Google Rich Results Test.
- Test social previews for Facebook, X/Twitter, WhatsApp, LinkedIn, and Telegram.
- Check important pages in Google indexing reports.
- Keep admissions, contact, gallery, and school information updated.
- Build external authority through mentions, backlinks, citations, directories, and official social profiles.
- Maintain factual consistency across website, social profiles, directories, and school documents.
