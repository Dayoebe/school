<?php

namespace App\Support;

use App\Models\GalleryItem;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Throwable;

class PublicSeo
{
    /**
     * @return array<string, array<string, mixed>>
     */
    public static function pages(array $settings): array
    {
        $pages = [];

        foreach (['home', 'about', 'admission', 'gallery', 'contact'] as $routeName) {
            $pages[$routeName] = self::pageMeta($routeName, $settings);
        }

        return $pages;
    }

    /**
     * @return array<string, mixed>
     */
    public static function pageMeta(?string $routeName, array $settings): array
    {
        $routeName = in_array($routeName, ['home', 'about', 'admission', 'gallery', 'contact'], true)
            ? $routeName
            : 'home';

        $siteName = self::siteName($settings);
        $motto = self::plain((string) data_get($settings, 'school_motto', ''));
        $defaultDescription = self::plain((string) data_get($settings, 'meta.description', 'School portal and services.'));

        $defaults = match ($routeName) {
            'about' => [
                'label' => 'About',
                'title' => 'About '.$siteName.' | Mission, Values and Student Success',
                'description' => self::plain((string) data_get($settings, 'about_page.hero_description', $defaultDescription)),
                'summary' => self::plain((string) data_get($settings, 'about_summary', $defaultDescription)),
                'changefreq' => 'monthly',
                'priority' => '0.8',
                'schemaType' => 'AboutPage',
            ],
            'admission' => [
                'label' => 'Admission',
                'title' => self::plain((string) data_get($settings, 'admission_page.hero_title', 'Admission Registration')).' | '.$siteName,
                'description' => self::plain((string) data_get($settings, 'admission_page.hero_description', $defaultDescription)),
                'summary' => 'Admission information, registration flow, requirements, and contact support for families applying to '.$siteName.'.',
                'changefreq' => 'weekly',
                'priority' => '0.9',
                'schemaType' => 'WebPage',
            ],
            'gallery' => [
                'label' => 'Gallery',
                'title' => 'School Gallery | '.$siteName,
                'description' => self::plain((string) data_get($settings, 'gallery_page.hero_description', $defaultDescription)),
                'summary' => 'A public gallery of school events, classroom activities, sports, clubs, leadership experiences, and student milestones.',
                'changefreq' => 'weekly',
                'priority' => '0.7',
                'schemaType' => 'CollectionPage',
            ],
            'contact' => [
                'label' => 'Contact',
                'title' => 'Contact '.$siteName,
                'description' => self::plain((string) data_get($settings, 'contact_page.hero_description', $defaultDescription)),
                'summary' => 'Contact details, phone numbers, email, address, map, and enquiry form for '.$siteName.'.',
                'changefreq' => 'monthly',
                'priority' => '0.7',
                'schemaType' => 'ContactPage',
            ],
            default => [
                'label' => 'Home',
                'title' => $siteName.($motto !== '' ? ' | '.$motto : ''),
                'description' => self::plain((string) data_get($settings, 'home.hero_description', $defaultDescription)),
                'summary' => self::plain((string) data_get($settings, 'home.hero_description', $defaultDescription)),
                'changefreq' => 'weekly',
                'priority' => '1.0',
                'schemaType' => 'WebPage',
            ],
        };

        $overrides = (array) data_get($settings, 'seo.'.$routeName, []);
        $title = self::plain((string) data_get($overrides, 'meta_title', '')) ?: $defaults['title'];
        $description = self::plain((string) data_get($overrides, 'meta_description', '')) ?: $defaults['description'];
        $image = self::plain((string) data_get($overrides, 'social_image_url', ''));

        if ($image === '') {
            $image = self::defaultImage($settings);
        }

        return [
            'routeName' => $routeName,
            'label' => $defaults['label'],
            'title' => self::limit($title, 80),
            'description' => self::limit($description, 160),
            'summary' => self::limit($defaults['summary'], 260),
            'canonical' => route($routeName),
            'image' => self::absoluteUrl($image),
            'changefreq' => $defaults['changefreq'],
            'priority' => $defaults['priority'],
            'schemaType' => $defaults['schemaType'],
        ];
    }

    public static function siteName(array $settings): string
    {
        $name = self::plain((string) data_get($settings, 'school_name', config('app.name', 'School Portal')));

        return $name !== '' ? $name : 'School Portal';
    }

    public static function defaultImage(array $settings): string
    {
        $image = self::plain((string) data_get($settings, 'theme.logo_url', ''));

        return $image !== '' ? self::absoluteUrl($image) : asset(config('app.logo', 'logo.png'));
    }

    public static function absoluteUrl(?string $url): string
    {
        $url = trim((string) $url);

        if ($url === '') {
            return asset(config('app.logo', 'logo.png'));
        }

        if (Str::startsWith($url, ['http://', 'https://'])) {
            return $url;
        }

        if (Str::startsWith($url, '//')) {
            return request()->getScheme().':'.$url;
        }

        return asset(ltrim($url, '/'));
    }

    public static function plain(string $value): string
    {
        $value = html_entity_decode(strip_tags($value), ENT_QUOTES | ENT_HTML5, 'UTF-8');

        return trim((string) preg_replace('/\s+/', ' ', $value));
    }

    public static function limit(string $value, int $limit): string
    {
        $value = self::plain($value);

        return Str::limit($value, $limit, '');
    }

    /**
     * @return array<int, array{q: string, a: string}>
     */
    public static function faqItems(?string $routeName, array $settings): array
    {
        $items = match ($routeName) {
            'home' => [
                ['q' => 'When is admission open?', 'a' => 'Admission is open throughout the session, with major intakes at the beginning of each term.'],
                ['q' => 'Do you provide boarding facilities?', 'a' => 'Yes. We provide safe and supervised boarding with academic and welfare support.'],
                ['q' => 'Can parents track student performance online?', 'a' => 'Yes. Parents can view records, assessments, and updates through the school portal.'],
                ['q' => 'How do I schedule a school visit?', 'a' => 'Use the Contact or Admission page, and our admissions unit will confirm your visit time.'],
            ],
            'about' => (array) data_get($settings, 'about_page.faqs', []),
            default => [],
        };

        return collect($items)
            ->filter(fn ($item): bool => is_array($item) && self::plain((string) ($item['q'] ?? '')) !== '' && self::plain((string) ($item['a'] ?? '')) !== '')
            ->map(fn ($item): array => [
                'q' => self::plain((string) $item['q']),
                'a' => self::plain((string) $item['a']),
            ])
            ->values()
            ->all();
    }

    /**
     * @return array<string, mixed>
     */
    public static function schema(?string $routeName, array $settings, ?School $school = null): array
    {
        $meta = self::pageMeta($routeName, $settings);
        $siteName = self::siteName($settings);
        $homeUrl = route('home');
        $locale = str_replace('_', '-', app()->getLocale());
        $organizationId = $homeUrl.'#organization';
        $websiteId = $homeUrl.'#website';
        $webpageId = $meta['canonical'].'#webpage';
        $contactPhone = self::plain((string) data_get($settings, 'contact.phone_primary', ''));
        $contactEmail = self::plain((string) data_get($settings, 'contact.email', ''));
        $contactAddress = self::plain((string) data_get($settings, 'contact.address', $school?->address ?? ''));
        $schoolLocation = self::plain((string) data_get($settings, 'school_location', ''));
        $socialLinks = self::sameAs($settings);

        $organization = [
            '@type' => ['EducationalOrganization', 'School'],
            '@id' => $organizationId,
            'name' => $siteName,
            'url' => $homeUrl,
            'logo' => [
                '@type' => 'ImageObject',
                'url' => self::defaultImage($settings),
            ],
            'image' => self::defaultImage($settings),
            'description' => self::limit((string) data_get($settings, 'about_summary', $meta['description']), 260),
        ];

        if ($contactPhone !== '') {
            $organization['telephone'] = $contactPhone;
        }

        if ($contactEmail !== '') {
            $organization['email'] = $contactEmail;
        }

        if ($contactAddress !== '') {
            $organization['address'] = [
                '@type' => 'PostalAddress',
                'streetAddress' => $contactAddress,
            ];

            if ($schoolLocation !== '') {
                $organization['address']['addressLocality'] = $schoolLocation;
            }

            if (Str::contains(Str::lower($contactAddress), 'nigeria')) {
                $organization['address']['addressCountry'] = 'NG';
            }
        }

        if ($contactPhone !== '' || $contactEmail !== '') {
            $organization['contactPoint'] = [[
                '@type' => 'ContactPoint',
                'contactType' => 'Admissions and school enquiries',
                'telephone' => $contactPhone ?: null,
                'email' => $contactEmail ?: null,
                'areaServed' => 'NG',
                'availableLanguage' => ['English'],
            ]];
        }

        if ($socialLinks !== []) {
            $organization['sameAs'] = $socialLinks;
        }

        $graph = [
            self::withoutNulls($organization),
            [
                '@type' => 'WebSite',
                '@id' => $websiteId,
                'name' => $siteName,
                'url' => $homeUrl,
                'description' => self::limit((string) data_get($settings, 'meta.description', $meta['description']), 220),
                'inLanguage' => $locale,
                'publisher' => ['@id' => $organizationId],
            ],
            [
                '@type' => $meta['schemaType'],
                '@id' => $webpageId,
                'url' => $meta['canonical'],
                'name' => $meta['title'],
                'description' => $meta['description'],
                'isPartOf' => ['@id' => $websiteId],
                'about' => ['@id' => $organizationId],
                'publisher' => ['@id' => $organizationId],
                'inLanguage' => $locale,
                'primaryImageOfPage' => [
                    '@type' => 'ImageObject',
                    'url' => $meta['image'],
                ],
            ],
        ];

        if ($routeName !== 'home') {
            $graph[] = self::breadcrumbSchema($meta);
        }

        $faqs = self::faqItems($routeName, $settings);
        if ($faqs !== []) {
            $graph[] = [
                '@type' => 'FAQPage',
                '@id' => $meta['canonical'].'#faq',
                'mainEntity' => array_map(static fn (array $faq): array => [
                    '@type' => 'Question',
                    'name' => $faq['q'],
                    'acceptedAnswer' => [
                        '@type' => 'Answer',
                        'text' => $faq['a'],
                    ],
                ], $faqs),
            ];
        }

        if ($routeName === 'admission') {
            $graph[] = self::withoutNulls([
                '@type' => 'Service',
                '@id' => route('admission').'#admission-service',
                'name' => self::plain((string) data_get($settings, 'admission_page.hero_title', 'Student Admission Registration')),
                'description' => self::plain((string) data_get($settings, 'admission_page.hero_description', $meta['description'])),
                'serviceType' => 'Student admission registration',
                'provider' => ['@id' => $organizationId],
                'areaServed' => $schoolLocation !== '' ? $schoolLocation : 'Nigeria',
                'url' => route('admission'),
            ]);
        }

        return [
            '@context' => 'https://schema.org',
            '@graph' => $graph,
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function breadcrumbSchema(array $meta): array
    {
        return [
            '@type' => 'BreadcrumbList',
            '@id' => $meta['canonical'].'#breadcrumb',
            'itemListElement' => [
                [
                    '@type' => 'ListItem',
                    'position' => 1,
                    'name' => 'Home',
                    'item' => route('home'),
                ],
                [
                    '@type' => 'ListItem',
                    'position' => 2,
                    'name' => $meta['label'],
                    'item' => $meta['canonical'],
                ],
            ],
        ];
    }

    /**
     * @return array<int, string>
     */
    public static function sameAs(array $settings): array
    {
        return collect([
            data_get($settings, 'footer.social.facebook'),
            data_get($settings, 'footer.social.instagram'),
            data_get($settings, 'footer.social.x'),
            data_get($settings, 'footer.social.whatsapp'),
        ])
            ->map(fn ($url): string => trim((string) $url))
            ->filter(fn (string $url): bool => Str::startsWith($url, ['http://', 'https://']))
            ->values()
            ->all();
    }

    public static function lastModified(?string $page = null): string
    {
        $timestamps = [
            @filemtime(resource_path('views/layouts/app.blade.php')) ?: 0,
            @filemtime(resource_path('views/livewire/site/'.($page ?: 'home').'.blade.php')) ?: 0,
            @filemtime(app_path('Support/SiteSettings.php')) ?: 0,
        ];

        try {
            if (Schema::hasTable('site_settings')) {
                $latestSetting = \App\Models\SiteSetting::query()->max('updated_at');
                if ($latestSetting) {
                    $timestamps[] = Carbon::parse($latestSetting)->timestamp;
                }
            }

            if ($page === 'gallery' && Schema::hasTable('gallery_items')) {
                $latestGalleryItem = GalleryItem::query()->max('updated_at');
                if ($latestGalleryItem) {
                    $timestamps[] = Carbon::parse($latestGalleryItem)->timestamp;
                }
            }
        } catch (Throwable) {
            // Keep sitemap generation available if the database is offline.
        }

        return Carbon::createFromTimestamp(max($timestamps))->toAtomString();
    }

    /**
     * @return array<int, array<string, string>>
     */
    public static function imageSitemapEntries(array $settings): array
    {
        $entries = [[
            'loc' => route('home'),
            'image' => self::defaultImage($settings),
            'title' => self::siteName($settings).' logo',
            'caption' => self::siteName($settings).' official logo and social preview image.',
        ]];

        try {
            if (! Schema::hasTable('gallery_items')) {
                return $entries;
            }

            GalleryItem::query()
                ->with('category:id,name')
                ->where('is_active', true)
                ->orderByDesc('is_featured')
                ->orderBy('sort_order')
                ->limit(200)
                ->get()
                ->each(function (GalleryItem $item) use (&$entries): void {
                    if (! $item->media_url) {
                        return;
                    }

                    $entries[] = [
                        'loc' => route('gallery'),
                        'image' => self::absoluteUrl(GalleryItem::transformUrl($item->media_url, 'f_auto,q_auto')),
                        'title' => self::plain((string) $item->title),
                        'caption' => self::plain((string) ($item->caption ?: $item->category?->name ?: 'School gallery image.')),
                    ];
                });
        } catch (Throwable) {
            return $entries;
        }

        return $entries;
    }

    /**
     * @param  array<string, mixed>  $value
     * @return array<string, mixed>
     */
    protected static function withoutNulls(array $value): array
    {
        foreach ($value as $key => $item) {
            if ($item === null || $item === '') {
                unset($value[$key]);
            } elseif (is_array($item)) {
                $value[$key] = self::withoutNulls($item);
            }
        }

        return $value;
    }
}
