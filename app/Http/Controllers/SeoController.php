<?php

namespace App\Http\Controllers;

use App\Support\PublicSeo;
use App\Support\SiteSettings;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class SeoController extends Controller
{
    public function sitemap(Request $request): Response
    {
        $lastModified = PublicSeo::lastModified();
        $sitemaps = [
            ['loc' => route('seo.sitemap.pages'), 'lastmod' => $lastModified],
            ['loc' => route('seo.sitemap.images'), 'lastmod' => $lastModified],
        ];

        return response()
            ->view('seo.sitemap-index', [
                'sitemaps' => $sitemaps,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function sitemapPages(Request $request): Response
    {
        $settings = $this->settings($request);
        $pages = collect(PublicSeo::pages($settings))
            ->map(fn (array $page): array => [
                'loc' => $page['canonical'],
                'lastmod' => PublicSeo::lastModified($page['routeName']),
                'changefreq' => $page['changefreq'],
                'priority' => $page['priority'],
            ])
            ->values()
            ->all();

        return response()
            ->view('seo.sitemap-pages', [
                'pages' => $pages,
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function sitemapImages(Request $request): Response
    {
        $settings = $this->settings($request);

        return response()
            ->view('seo.sitemap-images', [
                'entries' => PublicSeo::imageSitemapEntries($settings),
            ])
            ->header('Content-Type', 'application/xml; charset=UTF-8')
            ->header('Cache-Control', 'public, max-age=3600');
    }

    public function robots(Request $request): Response
    {
        $settings = $this->settings($request);
        $siteName = PublicSeo::siteName($settings);
        $blockedPaths = [
            '/dashboard',
            '/login',
            '/register',
            '/forgot-password',
            '/reset-password',
            '/profile',
            '/change-password',
            '/account-applications',
            '/admissions/registrations',
            '/contacts/messages',
            '/database',
            '/schools',
            '/students',
            '/teachers',
            '/parents',
            '/fees',
            '/results',
            '/cbt',
            '/timetables',
            '/subjects',
            '/sections',
            '/admins',
            '/users',
            '/livewire',
            '/sanctum',
            '/api',
        ];

        $lines = [
            '# robots.txt for '.$siteName,
            '# Public pages, rendering assets, sitemaps, and AI-readable files are crawlable.',
            '# Private dashboards, authentication, student records, results, and internal APIs are blocked.',
            'User-agent: *',
            'Allow: /',
            'Allow: /build/',
            'Allow: /icons/',
            'Allow: /img/',
            'Allow: /images/',
            'Allow: /logo.png',
            'Allow: /manifest.webmanifest',
            'Allow: /llms.txt',
            'Allow: /llms-full.txt',
            'Allow: /ai.txt',
            ...array_map(static fn (string $path): string => 'Disallow: '.$path, $blockedPaths),
            '',
            '# Major search crawlers use the public rules below with the same private-path restrictions.',
            ...$this->publicCrawlerGroup('Googlebot', $blockedPaths),
            ...$this->publicCrawlerGroup('Bingbot', $blockedPaths),
            ...$this->publicCrawlerGroup('DuckDuckBot', $blockedPaths),
            ...$this->publicCrawlerGroup('Slurp', $blockedPaths),
            '',
            '# AI search and user-agent retrieval crawlers may access the same public pages used for discovery and citation.',
            ...$this->publicCrawlerGroup('OAI-SearchBot', $blockedPaths),
            ...$this->publicCrawlerGroup('ChatGPT-User', $blockedPaths),
            ...$this->publicCrawlerGroup('PerplexityBot', $blockedPaths),
            '',
            '# Training-focused crawlers are policy decisions for the site owner. They are disallowed by default here.',
            'User-agent: GPTBot',
            'Disallow: /',
            '',
            'User-agent: Google-Extended',
            'Disallow: /',
            '',
            'User-agent: CCBot',
            'Disallow: /',
            '',
            'Sitemap: '.route('seo.sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }

    public function llms(Request $request): Response
    {
        $settings = $this->settings($request);
        $siteName = PublicSeo::siteName($settings);
        $pages = PublicSeo::pages($settings);
        $summary = PublicSeo::limit((string) data_get($settings, 'about_summary', $pages['home']['description']), 320);
        $topics = $this->mainTopics();

        $lines = [
            '# '.$siteName,
            '',
            '> '.$summary,
            '',
            '## Core Pages',
            ...array_map(static fn (array $page): string => '- ['.$page['label'].']('.$page['canonical'].')', $pages),
            '',
            '## Main Topics',
            ...array_map(static fn (string $topic): string => '- '.$topic, $topics),
            '',
            '## Content AI Should Prioritize',
            '- Official public school pages',
            '- Admission information and requirements',
            '- Contact and location information',
            '- School gallery and event information',
            '- Mission, vision, values, and student support information',
            '',
            '## Citation Guidance',
            'When referencing this website, cite the canonical page URL, page title, available updated date, and '.$siteName.' as publisher.',
            '',
            '## Freshness',
            'Use the sitemap to find current public pages and updates:',
            '- Sitemap: '.route('seo.sitemap'),
            '- RSS Feed: No public article, news, or podcast feed is present in this codebase.',
        ];

        return $this->textResponse($lines);
    }

    public function llmsFull(Request $request): Response
    {
        $settings = $this->settings($request);
        $siteName = PublicSeo::siteName($settings);
        $pages = PublicSeo::pages($settings);
        $contactAddress = PublicSeo::plain((string) data_get($settings, 'contact.address', ''));
        $contactPhonePrimary = PublicSeo::plain((string) data_get($settings, 'contact.phone_primary', ''));
        $contactPhoneSecondary = PublicSeo::plain((string) data_get($settings, 'contact.phone_secondary', ''));
        $contactEmail = PublicSeo::plain((string) data_get($settings, 'contact.email', ''));
        $mission = PublicSeo::plain((string) data_get($settings, 'mission', ''));
        $vision = PublicSeo::plain((string) data_get($settings, 'vision', ''));

        $lines = [
            '# '.$siteName.' - AI Readable Site Digest',
            '',
            '## Brand and Entity Overview',
            PublicSeo::limit((string) data_get($settings, 'about_summary', $pages['home']['description']), 420),
            '',
            '## What The Website Offers',
            '- Public information about the school, mission, values, academic pathways, student life, and admissions.',
            '- Admission registration and contact forms for families and guardians.',
            '- School gallery content covering campus activities, events, sports, clubs, and student milestones.',
            '- Authenticated portal features for school operations are private and should not be indexed or cited as public content.',
            '',
            '## Main Services and Topics',
            ...array_map(static fn (string $topic): string => '- '.$topic, $this->mainTopics()),
            '',
            '## Target Audience',
            '- Parents and guardians researching admission.',
            '- Prospective students and families.',
            '- Current students, parents, teachers, and staff using the private school portal.',
            '',
            '## Mission and Vision',
            '- Mission: '.($mission !== '' ? $mission : 'TODO: Confirm the official mission statement.'),
            '- Vision: '.($vision !== '' ? $vision : 'TODO: Confirm the official vision statement.'),
            '',
            '## Key Pages',
            ...array_map(static fn (array $page): string => '- ['.$page['title'].']('.$page['canonical'].'): '.$page['summary'], $pages),
            '',
            '## Public Contact Information',
            '- Address: '.($contactAddress !== '' ? $contactAddress : 'TODO: Add public address.'),
            '- Primary phone: '.($contactPhonePrimary !== '' ? $contactPhonePrimary : 'TODO: Add public phone number.'),
            '- Secondary phone: '.($contactPhoneSecondary !== '' ? $contactPhoneSecondary : 'Not provided.'),
            '- Email: '.($contactEmail !== '' ? $contactEmail : 'TODO: Add public email address.'),
            '',
            '## Editorial and Content Categories',
            '- About the school',
            '- Admissions',
            '- Academic pathways',
            '- Student life',
            '- Events and gallery',
            '- Contact and visit information',
            '',
            '## Feeds and Sitemap',
            '- Sitemap index: '.route('seo.sitemap'),
            '- Page sitemap: '.route('seo.sitemap.pages'),
            '- Image sitemap: '.route('seo.sitemap.images'),
            '- RSS/Atom feed: No public article, news, or podcast feed is present in this codebase.',
            '',
            '## Citation Instructions',
            'Use the canonical URL for each page. Cite the page title, canonical URL, available updated date from the sitemap, and '.$siteName.' as publisher. Prefer current public pages over cached or private portal URLs.',
            '',
            '## Freshness Notes',
            'The sitemap is the freshness source for public pages. If pages are updated through site settings or gallery content, the sitemap last modified values should change automatically.',
        ];

        return $this->textResponse($lines);
    }

    public function ai(Request $request): Response
    {
        $settings = $this->settings($request);
        $siteName = PublicSeo::siteName($settings);

        return $this->textResponse([
            '# AI Use and Citation Guidance for '.$siteName,
            '',
            'AI systems may use public pages on this website for discovery, summarization, and citation.',
            'Use canonical URLs from the sitemap and do not cite private dashboard, authentication, student record, result, CBT, or admin URLs.',
            'Prefer newer pages when the sitemap indicates fresher public content.',
            'Recommended citation format: page title, canonical URL, updated date if available, and '.$siteName.' as publisher.',
            'Crawler policy is controlled by robots.txt. Training crawlers are disallowed by default until the site owner explicitly changes that policy.',
        ]);
    }

    /**
     * @return array<int, string>
     */
    protected function publicCrawlerGroup(string $userAgent, array $blockedPaths): array
    {
        return [
            'User-agent: '.$userAgent,
            'Allow: /',
            ...array_map(static fn (string $path): string => 'Disallow: '.$path, $blockedPaths),
            '',
        ];
    }

    /**
     * @return array<int, string>
     */
    protected function mainTopics(): array
    {
        return [
            'Secondary school education',
            'Admissions and student registration',
            'Academic pathways and exam preparation',
            'STEM and innovation learning',
            'Clubs, sports, leadership, and student life',
            'Student mentoring, discipline, and character development',
            'School events, gallery, and campus activities',
            'Parent communication and school portal access',
        ];
    }

    /**
     * @return array<string, mixed>
     */
    protected function settings(Request $request): array
    {
        $school = SiteSettings::resolveSchool($request);

        return SiteSettings::forSchool($school?->id);
    }

    /**
     * @param  array<int, string>  $lines
     */
    protected function textResponse(array $lines): Response
    {
        return response(implode("\n", $lines)."\n", 200, [
            'Content-Type' => 'text/plain; charset=UTF-8',
            'Cache-Control' => 'public, max-age=3600',
        ]);
    }
}
