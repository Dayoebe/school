<?php

namespace App\Livewire\Schools;

use App\Models\School;
use App\Models\SiteSetting;
use App\Support\SiteSettings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Livewire\Component;

class ManageSiteSettings extends Component
{
    use AuthorizesRequests;

    public string $scope = 'school';

    public $schoolId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $pagesJson = '';

    protected $queryString = [
        'scope' => ['except' => 'school'],
        'schoolId' => ['except' => ''],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage school settings'), 403);

        if (!$this->canManageGeneralSettings()) {
            $this->scope = 'school';
            $this->schoolId = auth()->user()?->school_id;
        } else {
            if (!in_array($this->scope, ['general', 'school'], true)) {
                $this->scope = 'school';
            }

            if ($this->scope === 'school' && !$this->schoolId) {
                $this->schoolId = auth()->user()?->school_id ?: School::query()->value('id');
            }
        }

        $this->loadSettings();
    }

    public function updatedScope(): void
    {
        if (!$this->canManageGeneralSettings()) {
            $this->scope = 'school';
        }

        if ($this->scope === 'school' && !$this->targetSchoolId()) {
            $this->schoolId = auth()->user()?->school_id ?: School::query()->value('id');
        }

        $this->loadSettings();
    }

    public function updatedSchoolId(): void
    {
        if ($this->scope === 'school') {
            $this->loadSettings();
        }
    }

    public function save(): void
    {
        abort_unless(auth()->user()?->can('manage school settings'), 403);

        $validated = $this->validate([
            'form.school_name' => 'nullable|string|max:255',
            'form.school_location' => 'nullable|string|max:255',
            'form.school_motto' => 'nullable|string|max:255',
            'form.about_summary' => 'nullable|string|max:1000',
            'form.mission' => 'nullable|string|max:4000',
            'form.vision' => 'nullable|string|max:4000',
            'form.school_promise' => 'nullable|string|max:2000',

            'form.contact_address' => 'nullable|string|max:1000',
            'form.contact_phone_primary' => 'nullable|string|max:50',
            'form.contact_phone_secondary' => 'nullable|string|max:50',
            'form.contact_email' => 'nullable|email|max:255',
            'form.contact_map_embed_url' => 'nullable|url|max:4000',

            'form.home_hero_badge' => 'nullable|string|max:255',
            'form.home_hero_title' => 'nullable|string|max:255',
            'form.home_hero_highlight' => 'nullable|string|max:255',
            'form.home_hero_description' => 'nullable|string|max:3000',
            'form.home_contact_title' => 'nullable|string|max:255',
            'form.home_contact_description' => 'nullable|string|max:1000',

            'form.about_hero_badge' => 'nullable|string|max:255',
            'form.about_hero_title' => 'nullable|string|max:255',
            'form.about_hero_highlight' => 'nullable|string|max:255',
            'form.about_hero_description' => 'nullable|string|max:3000',
            'form.about_story_title' => 'nullable|string|max:255',
            'form.about_story_description' => 'nullable|string|max:3000',
            'form.about_identity_title' => 'nullable|string|max:255',
            'form.about_visit_title' => 'nullable|string|max:255',
            'form.about_visit_description' => 'nullable|string|max:3000',

            'form.admission_hero_badge' => 'nullable|string|max:255',
            'form.admission_hero_title' => 'nullable|string|max:255',
            'form.admission_hero_highlight' => 'nullable|string|max:255',
            'form.admission_hero_description' => 'nullable|string|max:3000',

            'form.contact_hero_badge' => 'nullable|string|max:255',
            'form.contact_hero_title' => 'nullable|string|max:255',
            'form.contact_hero_description' => 'nullable|string|max:3000',

            'form.gallery_hero_badge' => 'nullable|string|max:255',
            'form.gallery_hero_title' => 'nullable|string|max:255',
            'form.gallery_hero_description' => 'nullable|string|max:3000',

            'form.footer_admissions_badge' => 'nullable|string|max:255',
            'form.footer_admissions_title' => 'nullable|string|max:255',
            'form.footer_admissions_description' => 'nullable|string|max:2000',
            'form.footer_copyright_suffix' => 'nullable|string|max:255',

            'form.meta_description' => 'nullable|string|max:1000',
            'form.meta_keywords' => 'nullable|string|max:1000',
            'form.meta_author' => 'nullable|string|max:255',
            'form.meta_og_description' => 'nullable|string|max:1000',

            'pagesJson' => 'nullable|string',
        ]);

        $pages = [];
        $pagesJson = trim($validated['pagesJson'] ?? '');

        if ($pagesJson !== '') {
            try {
                $decoded = json_decode($pagesJson, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($decoded)) {
                    $this->addError('pagesJson', 'Additional page content must be a valid JSON object.');

                    return;
                }
                $pages = $decoded;
            } catch (\Throwable $exception) {
                $this->addError('pagesJson', 'Invalid JSON: ' . $exception->getMessage());

                return;
            }
        }

        $targetSchoolId = $this->targetSchoolId();

        if ($this->scope === 'school' && !$targetSchoolId) {
            $this->addError('schoolId', 'Select a school to save school-specific settings.');

            return;
        }

        if ($this->scope === 'school' && !School::query()->whereKey($targetSchoolId)->exists()) {
            $this->addError('schoolId', 'Selected school no longer exists.');

            return;
        }

        $payload = [
            'school_name' => trim((string) ($validated['form']['school_name'] ?? '')),
            'school_location' => trim((string) ($validated['form']['school_location'] ?? '')),
            'school_motto' => trim((string) ($validated['form']['school_motto'] ?? '')),
            'about_summary' => trim((string) ($validated['form']['about_summary'] ?? '')),
            'mission' => trim((string) ($validated['form']['mission'] ?? '')),
            'vision' => trim((string) ($validated['form']['vision'] ?? '')),
            'school_promise' => trim((string) ($validated['form']['school_promise'] ?? '')),
            'contact' => [
                'address' => trim((string) ($validated['form']['contact_address'] ?? '')),
                'phone_primary' => trim((string) ($validated['form']['contact_phone_primary'] ?? '')),
                'phone_secondary' => trim((string) ($validated['form']['contact_phone_secondary'] ?? '')),
                'email' => trim((string) ($validated['form']['contact_email'] ?? '')),
                'map_embed_url' => trim((string) ($validated['form']['contact_map_embed_url'] ?? '')),
            ],
            'home' => [
                'hero_badge' => trim((string) ($validated['form']['home_hero_badge'] ?? '')),
                'hero_title' => trim((string) ($validated['form']['home_hero_title'] ?? '')),
                'hero_highlight' => trim((string) ($validated['form']['home_hero_highlight'] ?? '')),
                'hero_description' => trim((string) ($validated['form']['home_hero_description'] ?? '')),
                'contact_title' => trim((string) ($validated['form']['home_contact_title'] ?? '')),
                'contact_description' => trim((string) ($validated['form']['home_contact_description'] ?? '')),
            ],
            'about_page' => [
                'hero_badge' => trim((string) ($validated['form']['about_hero_badge'] ?? '')),
                'hero_title' => trim((string) ($validated['form']['about_hero_title'] ?? '')),
                'hero_highlight' => trim((string) ($validated['form']['about_hero_highlight'] ?? '')),
                'hero_description' => trim((string) ($validated['form']['about_hero_description'] ?? '')),
                'story_title' => trim((string) ($validated['form']['about_story_title'] ?? '')),
                'story_description' => trim((string) ($validated['form']['about_story_description'] ?? '')),
                'identity_title' => trim((string) ($validated['form']['about_identity_title'] ?? '')),
                'visit_title' => trim((string) ($validated['form']['about_visit_title'] ?? '')),
                'visit_description' => trim((string) ($validated['form']['about_visit_description'] ?? '')),
            ],
            'admission_page' => [
                'hero_badge' => trim((string) ($validated['form']['admission_hero_badge'] ?? '')),
                'hero_title' => trim((string) ($validated['form']['admission_hero_title'] ?? '')),
                'hero_highlight' => trim((string) ($validated['form']['admission_hero_highlight'] ?? '')),
                'hero_description' => trim((string) ($validated['form']['admission_hero_description'] ?? '')),
            ],
            'contact_page' => [
                'hero_badge' => trim((string) ($validated['form']['contact_hero_badge'] ?? '')),
                'hero_title' => trim((string) ($validated['form']['contact_hero_title'] ?? '')),
                'hero_description' => trim((string) ($validated['form']['contact_hero_description'] ?? '')),
            ],
            'gallery_page' => [
                'hero_badge' => trim((string) ($validated['form']['gallery_hero_badge'] ?? '')),
                'hero_title' => trim((string) ($validated['form']['gallery_hero_title'] ?? '')),
                'hero_description' => trim((string) ($validated['form']['gallery_hero_description'] ?? '')),
            ],
            'footer' => [
                'admissions_badge' => trim((string) ($validated['form']['footer_admissions_badge'] ?? '')),
                'admissions_title' => trim((string) ($validated['form']['footer_admissions_title'] ?? '')),
                'admissions_description' => trim((string) ($validated['form']['footer_admissions_description'] ?? '')),
                'copyright_suffix' => trim((string) ($validated['form']['footer_copyright_suffix'] ?? '')),
            ],
            'meta' => [
                'description' => trim((string) ($validated['form']['meta_description'] ?? '')),
                'keywords' => trim((string) ($validated['form']['meta_keywords'] ?? '')),
                'author' => trim((string) ($validated['form']['meta_author'] ?? '')),
                'og_description' => trim((string) ($validated['form']['meta_og_description'] ?? '')),
            ],
            'pages' => $pages,
        ];

        SiteSetting::query()->updateOrCreate(
            ['scope_key' => $this->scopeKey()],
            [
                'school_id' => $this->scope === 'school' ? $targetSchoolId : null,
                'settings' => $payload,
            ]
        );

        SiteSettings::clearCache();
        $this->loadSettings();

        $scopeLabel = $this->scope === 'general' ? 'general' : 'selected school';
        session()->flash('success', 'Website settings saved for ' . $scopeLabel . '.');
    }

    protected function canManageGeneralSettings(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'super_admin']) === true;
    }

    protected function targetSchoolId(): ?int
    {
        if ($this->scope === 'general') {
            return null;
        }

        if ($this->canManageGeneralSettings()) {
            if ($this->schoolId === null || $this->schoolId === '') {
                return null;
            }

            return (int) $this->schoolId;
        }

        $currentSchoolId = auth()->user()?->school_id;

        return $currentSchoolId ? (int) $currentSchoolId : null;
    }

    protected function scopeKey(): string
    {
        $targetSchoolId = $this->targetSchoolId();

        return $this->scope === 'general'
            ? SiteSetting::generalScopeKey()
            : SiteSetting::schoolScopeKey((int) $targetSchoolId);
    }

    protected function loadSettings(): void
    {
        $targetSchoolId = $this->targetSchoolId();

        $settings = SiteSettings::forSchool($targetSchoolId);

        $this->form = [
            'school_name' => (string) data_get($settings, 'school_name', ''),
            'school_location' => (string) data_get($settings, 'school_location', ''),
            'school_motto' => (string) data_get($settings, 'school_motto', ''),
            'about_summary' => (string) data_get($settings, 'about_summary', ''),
            'mission' => (string) data_get($settings, 'mission', ''),
            'vision' => (string) data_get($settings, 'vision', ''),
            'school_promise' => (string) data_get($settings, 'school_promise', ''),

            'contact_address' => (string) data_get($settings, 'contact.address', ''),
            'contact_phone_primary' => (string) data_get($settings, 'contact.phone_primary', ''),
            'contact_phone_secondary' => (string) data_get($settings, 'contact.phone_secondary', ''),
            'contact_email' => (string) data_get($settings, 'contact.email', ''),
            'contact_map_embed_url' => (string) data_get($settings, 'contact.map_embed_url', ''),

            'home_hero_badge' => (string) data_get($settings, 'home.hero_badge', ''),
            'home_hero_title' => (string) data_get($settings, 'home.hero_title', ''),
            'home_hero_highlight' => (string) data_get($settings, 'home.hero_highlight', ''),
            'home_hero_description' => (string) data_get($settings, 'home.hero_description', ''),
            'home_contact_title' => (string) data_get($settings, 'home.contact_title', ''),
            'home_contact_description' => (string) data_get($settings, 'home.contact_description', ''),

            'about_hero_badge' => (string) data_get($settings, 'about_page.hero_badge', ''),
            'about_hero_title' => (string) data_get($settings, 'about_page.hero_title', ''),
            'about_hero_highlight' => (string) data_get($settings, 'about_page.hero_highlight', ''),
            'about_hero_description' => (string) data_get($settings, 'about_page.hero_description', ''),
            'about_story_title' => (string) data_get($settings, 'about_page.story_title', ''),
            'about_story_description' => (string) data_get($settings, 'about_page.story_description', ''),
            'about_identity_title' => (string) data_get($settings, 'about_page.identity_title', ''),
            'about_visit_title' => (string) data_get($settings, 'about_page.visit_title', ''),
            'about_visit_description' => (string) data_get($settings, 'about_page.visit_description', ''),

            'admission_hero_badge' => (string) data_get($settings, 'admission_page.hero_badge', ''),
            'admission_hero_title' => (string) data_get($settings, 'admission_page.hero_title', ''),
            'admission_hero_highlight' => (string) data_get($settings, 'admission_page.hero_highlight', ''),
            'admission_hero_description' => (string) data_get($settings, 'admission_page.hero_description', ''),

            'contact_hero_badge' => (string) data_get($settings, 'contact_page.hero_badge', ''),
            'contact_hero_title' => (string) data_get($settings, 'contact_page.hero_title', ''),
            'contact_hero_description' => (string) data_get($settings, 'contact_page.hero_description', ''),

            'gallery_hero_badge' => (string) data_get($settings, 'gallery_page.hero_badge', ''),
            'gallery_hero_title' => (string) data_get($settings, 'gallery_page.hero_title', ''),
            'gallery_hero_description' => (string) data_get($settings, 'gallery_page.hero_description', ''),

            'footer_admissions_badge' => (string) data_get($settings, 'footer.admissions_badge', ''),
            'footer_admissions_title' => (string) data_get($settings, 'footer.admissions_title', ''),
            'footer_admissions_description' => (string) data_get($settings, 'footer.admissions_description', ''),
            'footer_copyright_suffix' => (string) data_get($settings, 'footer.copyright_suffix', ''),

            'meta_description' => (string) data_get($settings, 'meta.description', ''),
            'meta_keywords' => (string) data_get($settings, 'meta.keywords', ''),
            'meta_author' => (string) data_get($settings, 'meta.author', ''),
            'meta_og_description' => (string) data_get($settings, 'meta.og_description', ''),
        ];

        $this->pagesJson = json_encode(
            data_get($settings, 'pages', []),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) ?: '{}';
    }

    public function render()
    {
        $schools = $this->canManageGeneralSettings()
            ? School::query()->orderBy('name')->get(['id', 'name', 'code'])
            : new Collection();

        return view('livewire.schools.manage-site-settings', compact('schools'))
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('schools.settings'), 'text' => 'Website Settings', 'active' => true],
                ],
            ])
            ->title('Website Settings');
    }
}
