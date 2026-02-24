<?php

namespace App\Livewire\Schools;

use App\Models\MediaAsset;
use App\Models\School;
use App\Models\SiteSetting;
use App\Models\SiteSettingVersion;
use App\Support\SiteSettings;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ManageSiteSettings extends Component
{
    use AuthorizesRequests;
    use WithFileUploads;

    public string $scope = 'school';

    public $schoolId = null;

    /** @var array<string, mixed> */
    public array $form = [];

    public string $pagesJson = '';

    public $themeLogoFile = null;

    public $themeFaviconFile = null;

    public ?int $publishedVersion = null;

    public ?int $draftVersion = null;

    public ?string $publishedAtLabel = null;

    public ?string $draftUpdatedAtLabel = null;

    public string $workflowStatus = 'draft';

    public ?int $pendingVersion = null;

    public ?string $approvalRequestedAtLabel = null;

    public ?string $approvalRequestedByLabel = null;

    public ?string $approvedAtLabel = null;

    public ?string $approvedByLabel = null;

    public ?string $rejectedAtLabel = null;

    public ?string $rejectedByLabel = null;

    public ?string $rejectionNote = null;

    public string $rejectionReason = '';

    public $importSettingsFile = null;

    public bool $importPublishNow = false;

    public string $cloneSourceScope = 'general';

    public $cloneSourceSchoolId = null;

    public bool $clonePublishNow = false;

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

    public function updatedCloneSourceScope(): void
    {
        if ($this->cloneSourceScope !== 'school') {
            $this->cloneSourceSchoolId = null;
        }
    }

    public function save(): void
    {
        $this->saveDraft();
    }

    public function saveDraft(): void
    {
        abort_unless(auth()->user()?->can('manage school settings'), 403);

        [$payload, $targetSchoolId] = $this->validatedPayload();

        if ($payload === null) {
            return;
        }

        $setting = $this->settingForScope();
        if (!$setting) {
            $setting = SiteSetting::query()->create([
                'scope_key' => $this->scopeKey(),
                'school_id' => $this->scope === 'school' ? $targetSchoolId : null,
                'settings' => null,
            ]);
        }

        $nextVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'draft_settings' => $payload,
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'draft',
            'pending_version' => null,
            'approval_requested_at' => null,
            'approval_requested_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion($setting, $nextVersion, 'draft_saved', $payload);

        SiteSettings::clearCache();
        $this->loadSettings();
        $this->reset(['themeLogoFile', 'themeFaviconFile']);

        session()->flash('success', 'Draft saved successfully.');
    }

    public function publishDraft(): void
    {
        abort_unless($this->canApproveSettings(), 403);

        $setting = $this->settingForScope();
        if (!$setting) {
            $this->addError('scope', 'No draft found for this scope yet. Save a draft first.');

            return;
        }

        $draft = $setting->draft_settings;
        if (!is_array($draft)) {
            $this->addError('scope', 'Draft data is missing. Save a draft before publishing.');

            return;
        }

        $nextVersion = $this->nextVersionNumber($setting);
        $isPendingApproval = $setting->workflow_status === 'pending_approval';

        $setting->update([
            'settings' => $draft,
            'published_version' => $nextVersion,
            'published_at' => now(),
            'published_by' => auth()->id(),
            'draft_settings' => $draft,
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'approved',
            'pending_version' => null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion(
            $setting,
            $nextVersion,
            $isPendingApproval ? 'approved_published' : 'published',
            $draft,
            [
                'source_workflow_status' => $isPendingApproval ? 'pending_approval' : 'draft',
            ]
        );

        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', $isPendingApproval
            ? 'Pending draft approved and published.'
            : 'Draft published to live site.');
    }

    public function submitForApproval(): void
    {
        abort_unless($this->canSubmitForApproval(), 403);

        $setting = $this->settingForScope();
        if (!$setting) {
            $this->addError('scope', 'No draft found for this scope yet. Save a draft first.');

            return;
        }

        $draft = $setting->draft_settings;
        if (!is_array($draft)) {
            $this->addError('scope', 'Draft data is missing. Save a draft before submitting.');

            return;
        }

        if ($setting->workflow_status === 'pending_approval') {
            $this->addError('scope', 'This draft is already pending approval.');

            return;
        }

        $nextVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'pending_approval',
            'pending_version' => $nextVersion,
            'approval_requested_at' => now(),
            'approval_requested_by' => auth()->id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion(
            $setting,
            $nextVersion,
            'submitted_for_approval',
            $draft,
            [
                'pending_version' => $nextVersion,
            ]
        );

        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', 'Draft submitted for super-admin approval.');
    }

    public function rejectPendingDraft(): void
    {
        abort_unless($this->canApproveSettings(), 403);

        $validated = $this->validate([
            'rejectionReason' => 'required|string|min:3|max:1000',
        ]);

        $setting = $this->settingForScope();
        if (!$setting || $setting->workflow_status !== 'pending_approval') {
            $this->addError('scope', 'No pending approval request for this scope.');

            return;
        }

        $nextVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'rejected',
            'pending_version' => null,
            'rejected_at' => now(),
            'rejected_by' => auth()->id(),
            'rejection_note' => trim($validated['rejectionReason']),
        ]);

        $this->recordVersion(
            $setting,
            $nextVersion,
            'rejected',
            is_array($setting->draft_settings) ? $setting->draft_settings : [],
            [
                'rejection_note' => trim($validated['rejectionReason']),
            ]
        );

        $this->rejectionReason = '';
        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', 'Draft rejected. Admin can revise and resubmit.');
    }

    public function exportSettings(): mixed
    {
        abort_unless($this->canExportSettings(), 403);

        $setting = $this->settingForScope();
        $payload = SiteSettings::forSchool($this->targetSchoolId());

        if ($setting && is_array($setting->draft_settings)) {
            $payload = array_replace_recursive($payload, $setting->draft_settings);
        }

        $content = [
            'format' => 'site-settings-backup-v1',
            'scope' => $this->scope,
            'scope_key' => $this->scopeKey(),
            'school_id' => $this->targetSchoolId(),
            'exported_at' => now()->toIso8601String(),
            'settings' => $payload,
            'meta' => [
                'exported_by' => auth()->id(),
                'workflow_status' => $setting?->workflow_status,
                'published_version' => $setting?->published_version,
                'draft_version' => $setting?->draft_version,
            ],
        ];

        $filename = 'settings-' . str_replace(':', '-', $this->scopeKey()) . '-' . now()->format('Ymd_His') . '.json';

        return response()->streamDownload(function () use ($content): void {
            echo json_encode($content, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
        }, $filename, [
            'Content-Type' => 'application/json',
        ]);
    }

    public function importSettingsBackup(): void
    {
        abort_unless($this->canImportSettings(), 403);

        $validated = $this->validate([
            'importSettingsFile' => 'required|file|max:10240|mimes:json,txt',
            'importPublishNow' => 'boolean',
        ]);

        $raw = file_get_contents($this->importSettingsFile->getRealPath());
        if ($raw === false) {
            $this->addError('importSettingsFile', 'Unable to read uploaded backup file.');
            return;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            $this->addError('importSettingsFile', 'Invalid JSON backup file: ' . $exception->getMessage());
            return;
        }

        if (!is_array($decoded)) {
            $this->addError('importSettingsFile', 'Backup file content must be a JSON object.');
            return;
        }

        $payload = $this->extractImportedSettingsPayload($decoded);
        if ($payload === null) {
            $this->addError('importSettingsFile', 'Backup file does not contain a valid `settings` object.');
            return;
        }

        [$setting, $nextVersion] = $this->applyPayloadAsDraft($payload, 'imported');

        $publishedNow = false;
        if ($validated['importPublishNow'] && $this->canApproveSettings()) {
            $this->publishSettingPayload($setting, $payload, 'imported_published');
            $publishedNow = true;
        }

        $this->reset(['importSettingsFile']);
        $this->importPublishNow = false;

        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', $publishedNow
            ? 'Backup imported and published.'
            : 'Backup imported as draft (v' . $nextVersion . ').');
    }

    public function cloneFromExistingScope(): void
    {
        abort_unless($this->canImportSettings(), 403);

        $validated = $this->validate([
            'cloneSourceScope' => 'required|in:general,school',
            'cloneSourceSchoolId' => 'nullable|integer|required_if:cloneSourceScope,school',
            'clonePublishNow' => 'boolean',
        ]);

        $sourceScopeKey = $validated['cloneSourceScope'] === 'general'
            ? SiteSetting::generalScopeKey()
            : SiteSetting::schoolScopeKey((int) $validated['cloneSourceSchoolId']);

        if ($sourceScopeKey === $this->scopeKey()) {
            $this->addError('cloneSourceScope', 'Source and destination scopes are the same.');
            return;
        }

        $source = SiteSetting::query()->where('scope_key', $sourceScopeKey)->first();
        if (!$source) {
            $this->addError('cloneSourceScope', 'Selected source settings scope was not found.');
            return;
        }

        $payload = is_array($source->settings) ? $source->settings : (is_array($source->draft_settings) ? $source->draft_settings : null);
        if ($payload === null) {
            $this->addError('cloneSourceScope', 'Source scope has no settings payload to clone.');
            return;
        }

        [$setting] = $this->applyPayloadAsDraft(
            $payload,
            'cloned',
            [
                'source_scope_key' => $sourceScopeKey,
                'source_school_id' => $source->school_id,
            ]
        );

        $publishedNow = false;
        if ($validated['clonePublishNow'] && $this->canApproveSettings()) {
            $this->publishSettingPayload($setting, $payload, 'cloned_published');
            $publishedNow = true;
        }

        $this->clonePublishNow = false;
        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', $publishedNow
            ? 'Settings cloned and published.'
            : 'Settings cloned into draft. Review and publish when ready.');
    }

    protected function extractImportedSettingsPayload(array $decoded): ?array
    {
        if (isset($decoded['settings']) && is_array($decoded['settings'])) {
            return $decoded['settings'];
        }

        if (isset($decoded['payload']) && is_array($decoded['payload'])) {
            return $decoded['payload'];
        }

        // Allow importing a raw settings object directly.
        if (array_key_exists('meta', $decoded) || array_key_exists('pages', $decoded) || array_key_exists('home', $decoded)) {
            return $decoded;
        }

        return null;
    }

    protected function applyPayloadAsDraft(array $payload, string $stage, ?array $meta = null): array
    {
        $setting = $this->settingForScope();
        if (!$setting) {
            $setting = SiteSetting::query()->create([
                'scope_key' => $this->scopeKey(),
                'school_id' => $this->scope === 'school' ? $this->targetSchoolId() : null,
                'settings' => null,
            ]);
        }

        $nextVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'draft_settings' => $payload,
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'draft',
            'pending_version' => null,
            'approval_requested_at' => null,
            'approval_requested_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion($setting, $nextVersion, $stage, $payload, $meta);

        return [$setting, $nextVersion];
    }

    protected function publishSettingPayload(SiteSetting $setting, array $payload, string $stage): void
    {
        $publishVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'settings' => $payload,
            'published_version' => $publishVersion,
            'published_at' => now(),
            'published_by' => auth()->id(),
            'draft_settings' => $payload,
            'draft_version' => $publishVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'approved',
            'pending_version' => null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion($setting, $publishVersion, $stage, $payload, [
            'published_from_tool' => true,
        ]);
    }

    public function rollbackToVersion(int $versionId): void
    {
        abort_unless($this->canRollback(), 403);

        $setting = $this->settingForScope();
        if (!$setting) {
            $this->addError('scope', 'No settings record found to rollback.');

            return;
        }

        $version = $setting->versions()
            ->whereKey($versionId)
            ->whereIn('stage', ['published', 'approved_published', 'rollback'])
            ->first();

        if (!$version || !is_array($version->settings)) {
            $this->addError('scope', 'Selected version cannot be used for rollback.');

            return;
        }

        $nextVersion = $this->nextVersionNumber($setting);

        $setting->update([
            'settings' => $version->settings,
            'published_version' => $nextVersion,
            'published_at' => now(),
            'published_by' => auth()->id(),
            'draft_settings' => $version->settings,
            'draft_version' => $nextVersion,
            'draft_updated_at' => now(),
            'draft_updated_by' => auth()->id(),
            'workflow_status' => 'approved',
            'pending_version' => null,
            'approved_at' => now(),
            'approved_by' => auth()->id(),
            'approval_requested_at' => null,
            'approval_requested_by' => null,
            'rejected_at' => null,
            'rejected_by' => null,
            'rejection_note' => null,
        ]);

        $this->recordVersion(
            $setting,
            $nextVersion,
            'rollback',
            $version->settings,
            [
                'source_version_id' => $version->id,
                'source_version_number' => $version->version_number,
                'source_stage' => $version->stage,
            ]
        );

        SiteSettings::clearCache();
        $this->loadSettings();

        session()->flash('success', 'Rollback completed and published live.');
    }

    protected function validatedPayload(): array
    {
        $validated = $this->validate([
            'form.school_name' => 'nullable|string|max:255',
            'form.school_location' => 'nullable|string|max:255',
            'form.school_motto' => 'nullable|string|max:255',
            'form.about_summary' => 'nullable|string|max:1000',
            'form.mission' => 'nullable|string|max:4000',
            'form.vision' => 'nullable|string|max:4000',
            'form.school_promise' => 'nullable|string|max:2000',

            'form.theme_primary_color' => ['nullable', 'regex:/^#([A-Fa-f0-9]{6}|[A-Fa-f0-9]{3})$/'],
            'form.theme_logo_url' => 'nullable|string|max:4000',
            'form.theme_favicon_url' => 'nullable|string|max:4000',
            'themeLogoFile' => 'nullable|image|max:3072',
            'themeFaviconFile' => 'nullable|file|mimes:ico,png,jpg,jpeg,webp,svg|max:2048',

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
            'form.about_story_extra' => 'nullable|string|max:3000',
            'form.about_story_quote' => 'nullable|string|max:2000',
            'form.about_identity_title' => 'nullable|string|max:255',
            'form.about_pillars_title' => 'nullable|string|max:255',
            'form.about_leadership_title' => 'nullable|string|max:255',
            'form.about_stats_title' => 'nullable|string|max:255',
            'form.about_faq_title' => 'nullable|string|max:255',
            'form.about_faq_subtitle' => 'nullable|string|max:255',
            'form.about_updates_title' => 'nullable|string|max:255',
            'form.about_updates_subtitle' => 'nullable|string|max:255',
            'form.about_student_voice_title' => 'nullable|string|max:255',
            'form.about_student_voice_subtitle' => 'nullable|string|max:255',
            'form.about_visit_title' => 'nullable|string|max:255',
            'form.about_visit_description' => 'nullable|string|max:3000',
            'form.about_milestones_json' => 'nullable|string',
            'form.about_values_json' => 'nullable|string',
            'form.about_pillar_tabs_json' => 'nullable|string',
            'form.about_pillars_json' => 'nullable|string',
            'form.about_leadership_json' => 'nullable|string',
            'form.about_stats_json' => 'nullable|string',
            'form.about_faqs_json' => 'nullable|string',
            'form.about_updates_json' => 'nullable|string',
            'form.about_calendar_json' => 'nullable|string',
            'form.about_student_voice_json' => 'nullable|string',

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

            'form.footer_social_facebook' => 'nullable|url|max:4000',
            'form.footer_social_instagram' => 'nullable|url|max:4000',
            'form.footer_social_x' => 'nullable|url|max:4000',
            'form.footer_social_whatsapp' => 'nullable|url|max:4000',

            'form.meta_description' => 'nullable|string|max:1000',
            'form.meta_keywords' => 'nullable|string|max:1000',
            'form.meta_author' => 'nullable|string|max:255',
            'form.meta_og_description' => 'nullable|string|max:1000',

            'form.seo_home_meta_title' => 'nullable|string|max:255',
            'form.seo_home_meta_description' => 'nullable|string|max:1000',
            'form.seo_home_social_image_url' => 'nullable|string|max:4000',
            'form.seo_about_meta_title' => 'nullable|string|max:255',
            'form.seo_about_meta_description' => 'nullable|string|max:1000',
            'form.seo_about_social_image_url' => 'nullable|string|max:4000',
            'form.seo_admission_meta_title' => 'nullable|string|max:255',
            'form.seo_admission_meta_description' => 'nullable|string|max:1000',
            'form.seo_admission_social_image_url' => 'nullable|string|max:4000',
            'form.seo_contact_meta_title' => 'nullable|string|max:255',
            'form.seo_contact_meta_description' => 'nullable|string|max:1000',
            'form.seo_contact_social_image_url' => 'nullable|string|max:4000',
            'form.seo_gallery_meta_title' => 'nullable|string|max:255',
            'form.seo_gallery_meta_description' => 'nullable|string|max:1000',
            'form.seo_gallery_social_image_url' => 'nullable|string|max:4000',

            'pagesJson' => 'nullable|string',
        ], [
            'form.theme_primary_color.regex' => 'Primary color must be a valid HEX code (e.g. #dc2626).',
        ]);

        $pages = [];
        $pagesJson = trim($validated['pagesJson'] ?? '');

        if ($pagesJson !== '') {
            try {
                $decoded = json_decode($pagesJson, true, 512, JSON_THROW_ON_ERROR);
                if (!is_array($decoded)) {
                    $this->addError('pagesJson', 'Additional page content must be a valid JSON object.');

                    return [null, null];
                }
                $pages = $decoded;
            } catch (\Throwable $exception) {
                $this->addError('pagesJson', 'Invalid JSON: ' . $exception->getMessage());

                return [null, null];
            }
        }

        $targetSchoolId = $this->targetSchoolId();

        if ($this->scope === 'school' && !$targetSchoolId) {
            $this->addError('schoolId', 'Select a school to save school-specific settings.');

            return [null, null];
        }

        if ($this->scope === 'school' && !School::query()->whereKey($targetSchoolId)->exists()) {
            $this->addError('schoolId', 'Selected school no longer exists.');

            return [null, null];
        }

        $aboutMilestones = $this->decodeJsonField(
            (string) ($validated['form']['about_milestones_json'] ?? ''),
            'form.about_milestones_json',
            []
        );
        $aboutValues = $this->decodeJsonField(
            (string) ($validated['form']['about_values_json'] ?? ''),
            'form.about_values_json',
            []
        );
        $aboutPillarTabs = $this->decodeJsonField(
            (string) ($validated['form']['about_pillar_tabs_json'] ?? ''),
            'form.about_pillar_tabs_json',
            []
        );
        $aboutPillars = $this->decodeJsonField(
            (string) ($validated['form']['about_pillars_json'] ?? ''),
            'form.about_pillars_json',
            []
        );
        $aboutLeadership = $this->decodeJsonField(
            (string) ($validated['form']['about_leadership_json'] ?? ''),
            'form.about_leadership_json',
            []
        );
        $aboutStats = $this->decodeJsonField(
            (string) ($validated['form']['about_stats_json'] ?? ''),
            'form.about_stats_json',
            []
        );
        $aboutFaqs = $this->decodeJsonField(
            (string) ($validated['form']['about_faqs_json'] ?? ''),
            'form.about_faqs_json',
            []
        );
        $aboutUpdates = $this->decodeJsonField(
            (string) ($validated['form']['about_updates_json'] ?? ''),
            'form.about_updates_json',
            []
        );
        $aboutCalendar = $this->decodeJsonField(
            (string) ($validated['form']['about_calendar_json'] ?? ''),
            'form.about_calendar_json',
            []
        );
        $aboutStudentVoice = $this->decodeJsonField(
            (string) ($validated['form']['about_student_voice_json'] ?? ''),
            'form.about_student_voice_json',
            []
        );

        foreach ([
            $aboutMilestones,
            $aboutValues,
            $aboutPillarTabs,
            $aboutPillars,
            $aboutLeadership,
            $aboutStats,
            $aboutFaqs,
            $aboutUpdates,
            $aboutCalendar,
            $aboutStudentVoice,
        ] as $decodedCollection) {
            if ($decodedCollection === null) {
                return [null, null];
            }
        }

        $themeLogoUrl = trim((string) ($validated['form']['theme_logo_url'] ?? ''));
        $themeFaviconUrl = trim((string) ($validated['form']['theme_favicon_url'] ?? ''));

        if ($this->themeLogoFile) {
            $themeLogoUrl = $this->storeThemeMedia($this->themeLogoFile, 'logos', $themeLogoUrl);
        }

        if ($this->themeFaviconFile) {
            $themeFaviconUrl = $this->storeThemeMedia($this->themeFaviconFile, 'favicons', $themeFaviconUrl);
        }

        $payload = [
            'school_name' => trim((string) ($validated['form']['school_name'] ?? '')),
            'school_location' => trim((string) ($validated['form']['school_location'] ?? '')),
            'school_motto' => trim((string) ($validated['form']['school_motto'] ?? '')),
            'about_summary' => trim((string) ($validated['form']['about_summary'] ?? '')),
            'mission' => trim((string) ($validated['form']['mission'] ?? '')),
            'vision' => trim((string) ($validated['form']['vision'] ?? '')),
            'school_promise' => trim((string) ($validated['form']['school_promise'] ?? '')),
            'theme' => [
                'primary_color' => trim((string) ($validated['form']['theme_primary_color'] ?? '#dc2626')),
                'logo_url' => $themeLogoUrl,
                'favicon_url' => $themeFaviconUrl,
            ],
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
                'story_extra' => trim((string) ($validated['form']['about_story_extra'] ?? '')),
                'story_quote' => trim((string) ($validated['form']['about_story_quote'] ?? '')),
                'identity_title' => trim((string) ($validated['form']['about_identity_title'] ?? '')),
                'pillars_title' => trim((string) ($validated['form']['about_pillars_title'] ?? '')),
                'leadership_title' => trim((string) ($validated['form']['about_leadership_title'] ?? '')),
                'stats_title' => trim((string) ($validated['form']['about_stats_title'] ?? '')),
                'faq_title' => trim((string) ($validated['form']['about_faq_title'] ?? '')),
                'faq_subtitle' => trim((string) ($validated['form']['about_faq_subtitle'] ?? '')),
                'updates_title' => trim((string) ($validated['form']['about_updates_title'] ?? '')),
                'updates_subtitle' => trim((string) ($validated['form']['about_updates_subtitle'] ?? '')),
                'student_voice_title' => trim((string) ($validated['form']['about_student_voice_title'] ?? '')),
                'student_voice_subtitle' => trim((string) ($validated['form']['about_student_voice_subtitle'] ?? '')),
                'visit_title' => trim((string) ($validated['form']['about_visit_title'] ?? '')),
                'visit_description' => trim((string) ($validated['form']['about_visit_description'] ?? '')),
                'milestones' => $aboutMilestones,
                'values' => $aboutValues,
                'pillar_tabs' => $aboutPillarTabs,
                'pillars' => $aboutPillars,
                'leadership' => $aboutLeadership,
                'stats' => $aboutStats,
                'faqs' => $aboutFaqs,
                'updates' => $aboutUpdates,
                'calendar' => $aboutCalendar,
                'student_voice' => $aboutStudentVoice,
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
                'social' => [
                    'facebook' => trim((string) ($validated['form']['footer_social_facebook'] ?? '')),
                    'instagram' => trim((string) ($validated['form']['footer_social_instagram'] ?? '')),
                    'x' => trim((string) ($validated['form']['footer_social_x'] ?? '')),
                    'whatsapp' => trim((string) ($validated['form']['footer_social_whatsapp'] ?? '')),
                ],
            ],
            'meta' => [
                'description' => trim((string) ($validated['form']['meta_description'] ?? '')),
                'keywords' => trim((string) ($validated['form']['meta_keywords'] ?? '')),
                'author' => trim((string) ($validated['form']['meta_author'] ?? '')),
                'og_description' => trim((string) ($validated['form']['meta_og_description'] ?? '')),
            ],
            'seo' => [
                'home' => [
                    'meta_title' => trim((string) ($validated['form']['seo_home_meta_title'] ?? '')),
                    'meta_description' => trim((string) ($validated['form']['seo_home_meta_description'] ?? '')),
                    'social_image_url' => trim((string) ($validated['form']['seo_home_social_image_url'] ?? '')),
                ],
                'about' => [
                    'meta_title' => trim((string) ($validated['form']['seo_about_meta_title'] ?? '')),
                    'meta_description' => trim((string) ($validated['form']['seo_about_meta_description'] ?? '')),
                    'social_image_url' => trim((string) ($validated['form']['seo_about_social_image_url'] ?? '')),
                ],
                'admission' => [
                    'meta_title' => trim((string) ($validated['form']['seo_admission_meta_title'] ?? '')),
                    'meta_description' => trim((string) ($validated['form']['seo_admission_meta_description'] ?? '')),
                    'social_image_url' => trim((string) ($validated['form']['seo_admission_social_image_url'] ?? '')),
                ],
                'contact' => [
                    'meta_title' => trim((string) ($validated['form']['seo_contact_meta_title'] ?? '')),
                    'meta_description' => trim((string) ($validated['form']['seo_contact_meta_description'] ?? '')),
                    'social_image_url' => trim((string) ($validated['form']['seo_contact_social_image_url'] ?? '')),
                ],
                'gallery' => [
                    'meta_title' => trim((string) ($validated['form']['seo_gallery_meta_title'] ?? '')),
                    'meta_description' => trim((string) ($validated['form']['seo_gallery_meta_description'] ?? '')),
                    'social_image_url' => trim((string) ($validated['form']['seo_gallery_social_image_url'] ?? '')),
                ],
            ],
            'pages' => $pages,
        ];

        return [$payload, $targetSchoolId];
    }

    protected function canManageGeneralSettings(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'super_admin']) === true;
    }

    protected function canSubmitForApproval(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can('submit school settings for approval') || $user->can('manage school settings');
    }

    protected function canApproveSettings(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can('approve school settings') || $user->hasAnyRole(['super-admin', 'super_admin']);
    }

    protected function canExportSettings(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can('export school settings') || $user->can('manage school settings');
    }

    protected function canImportSettings(): bool
    {
        $user = auth()->user();

        if (!$user) {
            return false;
        }

        return $user->can('import school settings') || $user->can('manage school settings');
    }

    protected function canRollback(): bool
    {
        return $this->canApproveSettings();
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

    protected function settingForScope(): ?SiteSetting
    {
        return SiteSetting::query()->where('scope_key', $this->scopeKey())->first();
    }

    protected function nextVersionNumber(SiteSetting $setting): int
    {
        return max((int) $setting->published_version, (int) $setting->draft_version) + 1;
    }

    protected function recordVersion(
        SiteSetting $setting,
        int $version,
        string $stage,
        array $settings,
        ?array $meta = null
    ): void {
        SiteSettingVersion::query()->create([
            'site_setting_id' => $setting->id,
            'scope_key' => $setting->scope_key,
            'school_id' => $setting->school_id,
            'version_number' => $version,
            'stage' => $stage,
            'settings' => $settings,
            'meta' => $meta,
            'changed_by' => auth()->id(),
        ]);
    }

    protected function loadSettings(): void
    {
        $targetSchoolId = $this->targetSchoolId();
        $setting = $this->settingForScope();
        if ($setting) {
            $setting->loadMissing([
                'approvalRequestedBy:id,name',
                'approvedBy:id,name',
                'rejectedBy:id,name',
            ]);
        }

        $settings = SiteSettings::forSchool($targetSchoolId);

        if ($setting && is_array($setting->draft_settings)) {
            $settings = array_replace_recursive($settings, $setting->draft_settings);
        }

        $this->publishedVersion = $setting ? (int) $setting->published_version : null;
        $this->draftVersion = $setting ? (int) $setting->draft_version : null;
        $this->workflowStatus = (string) ($setting?->workflow_status ?: 'draft');
        $this->pendingVersion = $setting?->pending_version !== null ? (int) $setting->pending_version : null;
        $this->publishedAtLabel = $setting?->published_at?->toDateTimeString();
        $this->draftUpdatedAtLabel = $setting?->draft_updated_at?->toDateTimeString();
        $this->approvalRequestedAtLabel = $setting?->approval_requested_at?->toDateTimeString();
        $this->approvalRequestedByLabel = $setting?->approvalRequestedBy?->name;
        $this->approvedAtLabel = $setting?->approved_at?->toDateTimeString();
        $this->approvedByLabel = $setting?->approvedBy?->name;
        $this->rejectedAtLabel = $setting?->rejected_at?->toDateTimeString();
        $this->rejectedByLabel = $setting?->rejectedBy?->name;
        $this->rejectionNote = $setting?->rejection_note;
        $this->rejectionReason = '';

        $this->form = [
            'school_name' => (string) data_get($settings, 'school_name', ''),
            'school_location' => (string) data_get($settings, 'school_location', ''),
            'school_motto' => (string) data_get($settings, 'school_motto', ''),
            'about_summary' => (string) data_get($settings, 'about_summary', ''),
            'mission' => (string) data_get($settings, 'mission', ''),
            'vision' => (string) data_get($settings, 'vision', ''),
            'school_promise' => (string) data_get($settings, 'school_promise', ''),

            'theme_primary_color' => (string) data_get($settings, 'theme.primary_color', '#dc2626'),
            'theme_logo_url' => (string) data_get($settings, 'theme.logo_url', ''),
            'theme_favicon_url' => (string) data_get($settings, 'theme.favicon_url', ''),

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
            'about_story_extra' => (string) data_get($settings, 'about_page.story_extra', ''),
            'about_story_quote' => (string) data_get($settings, 'about_page.story_quote', ''),
            'about_identity_title' => (string) data_get($settings, 'about_page.identity_title', ''),
            'about_pillars_title' => (string) data_get($settings, 'about_page.pillars_title', ''),
            'about_leadership_title' => (string) data_get($settings, 'about_page.leadership_title', ''),
            'about_stats_title' => (string) data_get($settings, 'about_page.stats_title', ''),
            'about_faq_title' => (string) data_get($settings, 'about_page.faq_title', ''),
            'about_faq_subtitle' => (string) data_get($settings, 'about_page.faq_subtitle', ''),
            'about_updates_title' => (string) data_get($settings, 'about_page.updates_title', ''),
            'about_updates_subtitle' => (string) data_get($settings, 'about_page.updates_subtitle', ''),
            'about_student_voice_title' => (string) data_get($settings, 'about_page.student_voice_title', ''),
            'about_student_voice_subtitle' => (string) data_get($settings, 'about_page.student_voice_subtitle', ''),
            'about_visit_title' => (string) data_get($settings, 'about_page.visit_title', ''),
            'about_visit_description' => (string) data_get($settings, 'about_page.visit_description', ''),
            'about_milestones_json' => $this->prettyJson(data_get($settings, 'about_page.milestones', [])),
            'about_values_json' => $this->prettyJson(data_get($settings, 'about_page.values', [])),
            'about_pillar_tabs_json' => $this->prettyJson(data_get($settings, 'about_page.pillar_tabs', [])),
            'about_pillars_json' => $this->prettyJson(data_get($settings, 'about_page.pillars', [])),
            'about_leadership_json' => $this->prettyJson(data_get($settings, 'about_page.leadership', [])),
            'about_stats_json' => $this->prettyJson(data_get($settings, 'about_page.stats', [])),
            'about_faqs_json' => $this->prettyJson(data_get($settings, 'about_page.faqs', [])),
            'about_updates_json' => $this->prettyJson(data_get($settings, 'about_page.updates', [])),
            'about_calendar_json' => $this->prettyJson(data_get($settings, 'about_page.calendar', [])),
            'about_student_voice_json' => $this->prettyJson(data_get($settings, 'about_page.student_voice', [])),

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

            'footer_social_facebook' => (string) data_get($settings, 'footer.social.facebook', ''),
            'footer_social_instagram' => (string) data_get($settings, 'footer.social.instagram', ''),
            'footer_social_x' => (string) data_get($settings, 'footer.social.x', ''),
            'footer_social_whatsapp' => (string) data_get($settings, 'footer.social.whatsapp', ''),

            'meta_description' => (string) data_get($settings, 'meta.description', ''),
            'meta_keywords' => (string) data_get($settings, 'meta.keywords', ''),
            'meta_author' => (string) data_get($settings, 'meta.author', ''),
            'meta_og_description' => (string) data_get($settings, 'meta.og_description', ''),

            'seo_home_meta_title' => (string) data_get($settings, 'seo.home.meta_title', ''),
            'seo_home_meta_description' => (string) data_get($settings, 'seo.home.meta_description', ''),
            'seo_home_social_image_url' => (string) data_get($settings, 'seo.home.social_image_url', ''),
            'seo_about_meta_title' => (string) data_get($settings, 'seo.about.meta_title', ''),
            'seo_about_meta_description' => (string) data_get($settings, 'seo.about.meta_description', ''),
            'seo_about_social_image_url' => (string) data_get($settings, 'seo.about.social_image_url', ''),
            'seo_admission_meta_title' => (string) data_get($settings, 'seo.admission.meta_title', ''),
            'seo_admission_meta_description' => (string) data_get($settings, 'seo.admission.meta_description', ''),
            'seo_admission_social_image_url' => (string) data_get($settings, 'seo.admission.social_image_url', ''),
            'seo_contact_meta_title' => (string) data_get($settings, 'seo.contact.meta_title', ''),
            'seo_contact_meta_description' => (string) data_get($settings, 'seo.contact.meta_description', ''),
            'seo_contact_social_image_url' => (string) data_get($settings, 'seo.contact.social_image_url', ''),
            'seo_gallery_meta_title' => (string) data_get($settings, 'seo.gallery.meta_title', ''),
            'seo_gallery_meta_description' => (string) data_get($settings, 'seo.gallery.meta_description', ''),
            'seo_gallery_social_image_url' => (string) data_get($settings, 'seo.gallery.social_image_url', ''),
        ];

        $this->pagesJson = json_encode(
            data_get($settings, 'pages', []),
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) ?: '{}';
    }

    protected function decodeJsonField(string $value, string $field, array $default = []): ?array
    {
        $trimmed = trim($value);
        if ($trimmed === '') {
            return $default;
        }

        try {
            $decoded = json_decode($trimmed, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable $exception) {
            $this->addError($field, 'Invalid JSON: ' . $exception->getMessage());

            return null;
        }

        if (!is_array($decoded)) {
            $this->addError($field, 'This field must contain a JSON array or object.');

            return null;
        }

        return $decoded;
    }

    protected function prettyJson(mixed $value): string
    {
        $normalized = is_array($value) ? $value : [];

        return json_encode(
            $normalized,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES
        ) ?: '[]';
    }

    protected function storeThemeMedia(mixed $file, string $folder, string $existingUrl = ''): string
    {
        $storedPath = $file->store('site-settings/' . $folder, 'public');
        $newUrl = url(Storage::url($storedPath));

        $this->deleteManagedThemeAsset($existingUrl, $newUrl);

        return $newUrl;
    }

    protected function deleteManagedThemeAsset(?string $existingUrl, string $newUrl = ''): void
    {
        $existingPath = parse_url((string) $existingUrl, PHP_URL_PATH);
        $newPath = parse_url($newUrl, PHP_URL_PATH);

        if (!$existingPath || $existingPath === $newPath) {
            return;
        }

        if (!str_starts_with($existingPath, '/storage/site-settings/')) {
            return;
        }

        $diskPath = ltrim(substr($existingPath, strlen('/storage/')), '/');
        if ($diskPath !== '') {
            Storage::disk('public')->delete($diskPath);
        }
    }

    public function render()
    {
        $schools = $this->canManageGeneralSettings()
            ? School::query()->orderBy('name')->get(['id', 'name', 'code'])
            : new Collection();

        $setting = $this->settingForScope();

        $history = collect();
        if ($setting) {
            $history = $setting->versions()
                ->with('changedBy:id,name')
                ->orderByDesc('version_number')
                ->orderByDesc('id')
                ->limit(25)
                ->get();
        }

        $mediaAssets = collect();
        $mediaSchoolId = $this->targetSchoolId() ?: auth()->user()?->school_id;
        if ($mediaSchoolId) {
            $mediaAssets = MediaAsset::query()
                ->where('school_id', (int) $mediaSchoolId)
                ->latest()
                ->limit(20)
                ->get(['id', 'title', 'usage_area', 'disk', 'path', 'optimized_path']);
        }

        return view('livewire.schools.manage-site-settings', [
            'schools' => $schools,
            'history' => $history,
            'canRollback' => $this->canRollback(),
            'canSubmitForApproval' => $this->canSubmitForApproval(),
            'canApproveSettings' => $this->canApproveSettings(),
            'canExportSettings' => $this->canExportSettings(),
            'canImportSettings' => $this->canImportSettings(),
            'mediaAssets' => $mediaAssets,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('schools.settings'), 'text' => 'Website Settings', 'active' => true],
                ],
            ])
            ->title('Website Settings');
    }
}
