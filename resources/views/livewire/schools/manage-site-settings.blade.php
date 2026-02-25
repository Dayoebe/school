<div>
    <div class="space-y-6">
        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-slate-900">Website Settings</h2>
                    <p class="mt-1 text-sm text-slate-600">
                        Draft changes safely, submit for approval, publish to live, and rollback to a previous stable
                        version.
                    </p>
                </div>

                @if (auth()->user()->hasAnyRole(['super-admin', 'super_admin']))
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label
                                class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Scope</label>
                            <select wire:model.live="scope"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="general">General (All Schools)</option>
                                <option value="school">Per School</option>
                            </select>
                        </div>

                        @if ($scope === 'school')
                            <div>
                                <label
                                    class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">School</label>
                                <select wire:model.live="schoolId"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                    <option value="">Select school</option>
                                    @foreach ($schools as $school)
                                        <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->code }})
                                        </option>
                                    @endforeach
                                </select>
                                @error('schoolId')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        @endif
                    </div>
                @else
                    <div class="rounded-lg bg-blue-50 px-3 py-2 text-xs font-semibold text-blue-700">
                        School-specific settings mode
                    </div>
                @endif
            </div>

            <div class="mt-4 grid grid-cols-1 gap-3 sm:grid-cols-2 lg:grid-cols-4">
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Draft Version</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $draftVersion ?: 'N/A' }}</p>
                    <p class="text-[11px] text-slate-500">{{ $draftUpdatedAtLabel ?: 'Not saved yet' }}</p>
                </div>
                <div class="rounded-lg border border-slate-200 bg-slate-50 px-3 py-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Published Version</p>
                    <p class="mt-1 text-lg font-bold text-slate-900">{{ $publishedVersion ?: 'N/A' }}</p>
                    <p class="text-[11px] text-slate-500">{{ $publishedAtLabel ?: 'Not published yet' }}</p>
                </div>
                <div class="rounded-lg border border-blue-200 bg-blue-50 px-3 py-2 sm:col-span-2 lg:col-span-2">
                    <p class="text-xs font-semibold uppercase tracking-wide text-blue-600">Workflow Status</p>
                    <p class="mt-1 text-sm font-semibold text-blue-800">
                        {{ str_replace('_', ' ', ucfirst($workflowStatus)) }}</p>
                    <p class="text-[11px] text-blue-700">
                        @if ($workflowStatus === 'pending_approval')
                            Awaiting super-admin approval
                            @if ($pendingVersion)
                                (draft v{{ $pendingVersion }})
                            @endif
                            @if ($approvalRequestedByLabel)
                                by {{ $approvalRequestedByLabel }}
                            @endif
                            @if ($approvalRequestedAtLabel)
                                at {{ $approvalRequestedAtLabel }}
                            @endif
                        @elseif ($workflowStatus === 'rejected')
                            Rejected
                            @if ($rejectedByLabel)
                                by {{ $rejectedByLabel }}
                            @endif
                            @if ($rejectedAtLabel)
                                at {{ $rejectedAtLabel }}
                            @endif
                        @elseif ($workflowStatus === 'approved')
                            Approved for live use.
                            @if ($approvedByLabel)
                                Last approved by {{ $approvedByLabel }}
                            @endif
                            @if ($approvedAtLabel)
                                at {{ $approvedAtLabel }}
                            @endif
                        @else
                            Draft mode. Save and submit when ready.
                        @endif
                    </p>
                    @if ($workflowStatus === 'rejected' && $rejectionNote)
                        <p class="mt-1 text-xs text-red-700">Rejection note: {{ $rejectionNote }}</p>
                    @endif
                </div>
            </div>
        </div>

        <form wire:submit.prevent="saveDraft" class="space-y-6">
            <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Branding</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">School Name</label>
                            <input type="text" wire:model.defer="form.school_name"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.school_name')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Location Label</label>
                            <input type="text" wire:model.defer="form.school_location"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="Awka, Anambra" />
                            @error('form.school_location')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Motto</label>
                            <input type="text" wire:model.defer="form.school_motto"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.school_motto')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">About Summary</label>
                            <textarea rows="3" wire:model.defer="form.about_summary"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_summary')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Theme Controls</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Primary Color (HEX)</label>
                            <input type="text" wire:model.defer="form.theme_primary_color" placeholder="#dc2626"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.theme_primary_color')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Logo URL (optional
                                override)</label>
                            <input type="text" wire:model.defer="form.theme_logo_url"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.theme_logo_url')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Logo</label>
                            <input type="file" wire:model="themeLogoFile" accept="image/*"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            <p class="mt-1 text-[11px] text-slate-500">Recommended: square image, max 3MB.</p>
                            @error('themeLogoFile')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div wire:loading wire:target="themeLogoFile" class="mt-1 text-xs text-blue-600">Uploading
                                logo...</div>
                            @if ($themeLogoFile)
                                <img src="{{ $themeLogoFile->temporaryUrl() }}" alt="Logo preview"
                                    class="mt-2 h-12 w-12 rounded-full border border-slate-200 object-cover" />
                            @elseif (!empty($form['theme_logo_url']))
                                <img src="{{ $form['theme_logo_url'] }}" alt="Current logo"
                                    class="mt-2 h-12 w-12 rounded-full border border-slate-200 object-cover" />
                            @endif
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Favicon URL (optional
                                override)</label>
                            <input type="text" wire:model.defer="form.theme_favicon_url"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.theme_favicon_url')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Favicon</label>
                            <input type="file" wire:model="themeFaviconFile"
                                accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            <p class="mt-1 text-[11px] text-slate-500">Accepted: ICO, PNG, JPG, WEBP, SVG (max 2MB).</p>
                            @error('themeFaviconFile')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                            <div wire:loading wire:target="themeFaviconFile" class="mt-1 text-xs text-blue-600">
                                Uploading favicon...</div>
                            @if (!empty($form['theme_favicon_url']))
                                <a href="{{ $form['theme_favicon_url'] }}" target="_blank" rel="noopener noreferrer"
                                    class="mt-2 inline-flex items-center text-xs font-semibold text-blue-700 hover:text-blue-800">
                                    Preview current favicon
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Mission & Vision</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Mission</label>
                            <textarea rows="4" wire:model.defer="form.mission"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.mission')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Vision</label>
                            <textarea rows="4" wire:model.defer="form.vision"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.vision')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">School Promise</label>
                            <textarea rows="3" wire:model.defer="form.school_promise"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.school_promise')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Contact Details</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                            <textarea rows="3" wire:model.defer="form.contact_address"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.contact_address')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Phone (Primary)</label>
                                <input type="text" wire:model.defer="form.contact_phone_primary"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.contact_phone_primary')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="mb-1 block text-sm font-semibold text-slate-700">Phone
                                    (Secondary)</label>
                                <input type="text" wire:model.defer="form.contact_phone_secondary"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.contact_phone_secondary')
                                    <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                            <input type="email" wire:model.defer="form.contact_email"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_email')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Google Map Embed URL</label>
                            <input type="url" wire:model.defer="form.contact_map_embed_url"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_map_embed_url')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Home Page</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                            <input type="text" wire:model.defer="form.home_hero_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.home_hero_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                            <input type="text" wire:model.defer="form.home_hero_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.home_hero_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                            <input type="text" wire:model.defer="form.home_hero_highlight"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.home_hero_highlight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                            <textarea rows="3" wire:model.defer="form.home_hero_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.home_hero_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Block Title</label>
                            <input type="text" wire:model.defer="form.home_contact_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.home_contact_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Block
                                Description</label>
                            <textarea rows="2" wire:model.defer="form.home_contact_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.home_contact_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h3 class="text-lg font-bold text-slate-900">About Page</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                            <input type="text" wire:model.defer="form.about_hero_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_hero_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                            <input type="text" wire:model.defer="form.about_hero_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_hero_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                            <input type="text" wire:model.defer="form.about_hero_highlight"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_hero_highlight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Identity Section
                                Title</label>
                            <input type="text" wire:model.defer="form.about_identity_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_identity_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                            <textarea rows="3" wire:model.defer="form.about_hero_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_hero_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Story Title</label>
                            <input type="text" wire:model.defer="form.about_story_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_story_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Story Description</label>
                            <textarea rows="3" wire:model.defer="form.about_story_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_story_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Story Extra
                                Paragraph</label>
                            <textarea rows="3" wire:model.defer="form.about_story_extra"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_story_extra')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="md:col-span-2">
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Story Quote</label>
                            <textarea rows="2" wire:model.defer="form.about_story_quote"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_story_quote')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Pillars Section
                                Title</label>
                            <input type="text" wire:model.defer="form.about_pillars_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_pillars_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Leadership Section
                                Title</label>
                            <input type="text" wire:model.defer="form.about_leadership_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_leadership_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Stats Section Title</label>
                            <input type="text" wire:model.defer="form.about_stats_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_stats_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">FAQ Section Title</label>
                            <input type="text" wire:model.defer="form.about_faq_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_faq_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">FAQ Eyebrow</label>
                            <input type="text" wire:model.defer="form.about_faq_subtitle"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_faq_subtitle')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Updates Section
                                Title</label>
                            <input type="text" wire:model.defer="form.about_updates_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_updates_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Updates Section
                                Subtitle</label>
                            <input type="text" wire:model.defer="form.about_updates_subtitle"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_updates_subtitle')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Student Voice Title</label>
                            <input type="text" wire:model.defer="form.about_student_voice_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_student_voice_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Student Voice
                                Subtitle</label>
                            <input type="text" wire:model.defer="form.about_student_voice_subtitle"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_student_voice_subtitle')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Visit Section Title</label>
                            <input type="text" wire:model.defer="form.about_visit_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.about_visit_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Visit Section
                                Description</label>
                            <textarea rows="3" wire:model.defer="form.about_visit_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.about_visit_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">About Page Collections</h3>
                            <p class="mt-1 text-sm text-slate-600">
                                Use guided editors for milestones, values, FAQ, updates, and more. No manual JSON
                                required.
                            </p>
                        </div>
                        <span
                            class="inline-flex items-center rounded-full bg-emerald-100 px-3 py-1 text-xs font-semibold text-emerald-700">
                            <i class="fas fa-wand-magic-sparkles mr-1"></i>Friendly Editor
                        </span>
                    </div>

                    @php
                        $milestoneFields = [
                            ['key' => 'title', 'label' => 'Title', 'placeholder' => 'Foundation Stage'],
                            [
                                'key' => 'note',
                                'label' => 'Note',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Brief description for this milestone',
                            ],
                            [
                                'key' => 'dotClass',
                                'label' => 'Dot Color Class',
                                'placeholder' => 'bg-red-500',
                                'help' => 'Tailwind class used for the small timeline dot.',
                                'advanced' => true,
                            ],
                        ];

                        $valueFields = [
                            ['key' => 'title', 'label' => 'Value Title', 'placeholder' => 'Excellence'],
                            [
                                'key' => 'icon',
                                'label' => 'Font Awesome Icon',
                                'placeholder' => 'fa-medal',
                                'help' => 'Example: fa-medal, fa-lightbulb, fa-hand-holding-heart',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'text',
                                'label' => 'Description',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'What this value means to your school',
                            ],
                            [
                                'key' => 'cardClass',
                                'label' => 'Card Class',
                                'placeholder' => 'border-red-200 bg-red-50 text-red-900',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'badgeClass',
                                'label' => 'Badge Class',
                                'placeholder' => 'bg-red-600 text-white',
                                'advanced' => true,
                            ],
                        ];

                        $pillarTabFields = [
                            [
                                'key' => 'key',
                                'label' => 'Pillar Key',
                                'placeholder' => 'academics',
                                'help' => 'Must match a pillar key below.',
                            ],
                            ['key' => 'label', 'label' => 'Tab Label', 'placeholder' => 'Academic Rigor'],
                        ];

                        $pillarFields = [
                            [
                                'key' => 'key',
                                'label' => 'Pillar Key',
                                'placeholder' => 'academics',
                                'help' => 'Unique key, e.g. academics or mentorship.',
                            ],
                            [
                                'key' => 'title',
                                'label' => 'Title',
                                'placeholder' => 'Academic Rigor and Structured Progress',
                            ],
                            [
                                'key' => 'description',
                                'label' => 'Description',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Overview text for this pillar',
                            ],
                            [
                                'key' => 'points',
                                'label' => 'Bullet Points',
                                'type' => 'list',
                                'fullWidth' => true,
                                'placeholder' => 'One point per line',
                            ],
                            [
                                'key' => 'outcomes',
                                'label' => 'Outcomes',
                                'type' => 'list',
                                'fullWidth' => true,
                                'placeholder' => 'One outcome per line',
                            ],
                            [
                                'key' => 'boxClass',
                                'label' => 'Outcome Box Class',
                                'placeholder' => 'border-blue-200 bg-blue-50 text-blue-900',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'labelClass',
                                'label' => 'Outcome Label Class',
                                'placeholder' => 'text-blue-700',
                                'advanced' => true,
                            ],
                        ];

                        $leadershipFields = [
                            ['key' => 'role', 'label' => 'Role', 'placeholder' => 'Principal'],
                            ['key' => 'unit', 'label' => 'Unit', 'placeholder' => 'School Leadership Office'],
                            [
                                'key' => 'note',
                                'label' => 'Note',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Short description of this role',
                            ],
                            [
                                'key' => 'icon',
                                'label' => 'Font Awesome Icon',
                                'placeholder' => 'fa-user-tie',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'iconClass',
                                'label' => 'Icon Class',
                                'placeholder' => 'bg-red-600',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'roleClass',
                                'label' => 'Role Text Class',
                                'placeholder' => 'text-red-700',
                                'advanced' => true,
                            ],
                        ];

                        $statFields = [
                            ['key' => 'label', 'label' => 'Label', 'placeholder' => 'Students Mentored'],
                            ['key' => 'target', 'label' => 'Target Number', 'placeholder' => '1200'],
                            ['key' => 'suffix', 'label' => 'Suffix', 'placeholder' => '+'],
                            [
                                'key' => 'cardClass',
                                'label' => 'Card Class',
                                'placeholder' => 'border-red-200 bg-red-50',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'valueClass',
                                'label' => 'Value Class',
                                'placeholder' => 'text-red-700',
                                'advanced' => true,
                            ],
                        ];

                        $faqFields = [
                            [
                                'key' => 'q',
                                'label' => 'Question',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'What makes your school different?',
                            ],
                            [
                                'key' => 'a',
                                'label' => 'Answer',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Write the answer shown on the About page',
                            ],
                        ];

                        $updateFields = [
                            [
                                'key' => 'title',
                                'label' => 'Update Title',
                                'placeholder' => 'Inter-House Sports Festival',
                            ],
                            ['key' => 'date', 'label' => 'Date Label', 'placeholder' => 'March 20, 2026'],
                            [
                                'key' => 'note',
                                'label' => 'Details',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Brief details about this update',
                            ],
                        ];

                        $calendarFields = [
                            ['key' => 'period', 'label' => 'Period', 'placeholder' => 'March 2026'],
                            ['key' => 'activity', 'label' => 'Activity', 'placeholder' => 'Continuous Assessment 2'],
                        ];

                        $studentVoiceFields = [
                            ['key' => 'name', 'label' => 'Name', 'placeholder' => 'Amara E.'],
                            ['key' => 'role', 'label' => 'Role', 'placeholder' => 'SS2 Student'],
                            [
                                'key' => 'quote',
                                'label' => 'Quote',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Student or parent testimonial text',
                            ],
                        ];
                    @endphp

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <x-settings.json-collection-editor title="Milestones" model-path="form.about_milestones_json"
                            description="Timeline journey cards shown in the About Story section." :fields="$milestoneFields"
                            empty-message="No milestones added yet." />

                        <x-settings.json-collection-editor title="School Values" model-path="form.about_values_json"
                            description="Identity cards displayed under mission, vision, and promise."
                            :fields="$valueFields" empty-message="No values added yet." />

                        <x-settings.json-collection-editor title="Pillar Tabs"
                            model-path="form.about_pillar_tabs_json"
                            description="Tab buttons for switching between pillar content." :fields="$pillarTabFields"
                            empty-message="No pillar tabs added yet." />

                        <x-settings.json-collection-editor title="Leadership Team"
                            model-path="form.about_leadership_json"
                            description="Leadership cards with icon, role, unit, and note." :fields="$leadershipFields"
                            empty-message="No leadership entries added yet." />

                        <x-settings.json-collection-editor title="Statistics Cards" model-path="form.about_stats_json"
                            description="Animated stats shown in the About Snapshot section." :fields="$statFields"
                            empty-message="No stats entries added yet." />

                        <x-settings.json-collection-editor title="FAQ Items" model-path="form.about_faqs_json"
                            description="Frequently asked questions and answers." :fields="$faqFields"
                            empty-message="No FAQ entries added yet." />

                        <x-settings.json-collection-editor title="Upcoming Updates"
                            model-path="form.about_updates_json"
                            description="Events and update cards shown in the updates section." :fields="$updateFields"
                            empty-message="No updates added yet." />

                        <x-settings.json-collection-editor title="School Calendar"
                            model-path="form.about_calendar_json"
                            description="Short period/activity calendar entries." :fields="$calendarFields"
                            empty-message="No calendar entries added yet." />

                        <x-settings.json-collection-editor title="Student Voice"
                            model-path="form.about_student_voice_json"
                            description="Testimonials from students and parents." :fields="$studentVoiceFields"
                            empty-message="No testimonials added yet." />

                        <div class="md:col-span-2">
                            <x-settings.json-collection-editor title="Academic Pillars"
                                model-path="form.about_pillars_json"
                                description="Main pillar content keyed by Pillar Key." :fields="$pillarFields"
                                mode="keyed" key-field="key" empty-message="No pillar content added yet." />
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Admission Page</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                            <input type="text" wire:model.defer="form.admission_hero_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.admission_hero_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                            <input type="text" wire:model.defer="form.admission_hero_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.admission_hero_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                            <input type="text" wire:model.defer="form.admission_hero_highlight"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.admission_hero_highlight')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                            <textarea rows="3" wire:model.defer="form.admission_hero_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.admission_hero_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Contact & Gallery Hero Text</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero Badge</label>
                            <input type="text" wire:model.defer="form.contact_hero_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_hero_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero Title</label>
                            <input type="text" wire:model.defer="form.contact_hero_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_hero_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero
                                Description</label>
                            <textarea rows="3" wire:model.defer="form.contact_hero_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.contact_hero_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <hr class="my-2 border-slate-200" />

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero Badge</label>
                            <input type="text" wire:model.defer="form.gallery_hero_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.gallery_hero_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero Title</label>
                            <input type="text" wire:model.defer="form.gallery_hero_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.gallery_hero_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero
                                Description</label>
                            <textarea rows="3" wire:model.defer="form.gallery_hero_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.gallery_hero_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow">
                    <h3 class="text-lg font-bold text-slate-900">Footer & SEO</h3>
                    <div class="mt-4 space-y-3">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Badge</label>
                            <input type="text" wire:model.defer="form.footer_admissions_badge"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_admissions_badge')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Title</label>
                            <input type="text" wire:model.defer="form.footer_admissions_title"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_admissions_title')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Description</label>
                            <textarea rows="2" wire:model.defer="form.footer_admissions_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.footer_admissions_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Copyright Suffix</label>
                            <input type="text" wire:model.defer="form.footer_copyright_suffix"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"
                                placeholder="All rights reserved." />
                            @error('form.footer_copyright_suffix')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <hr class="my-2 border-slate-200" />

                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Description</label>
                            <textarea rows="2" wire:model.defer="form.meta_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.meta_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Keywords</label>
                            <input type="text" wire:model.defer="form.meta_keywords"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.meta_keywords')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Author</label>
                            <input type="text" wire:model.defer="form.meta_author"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.meta_author')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">OG Description</label>
                            <textarea rows="2" wire:model.defer="form.meta_og_description"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.meta_og_description')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h3 class="text-lg font-bold text-slate-900">Footer Social Links</h3>
                    <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Facebook URL</label>
                            <input type="url" wire:model.defer="form.footer_social_facebook"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_social_facebook')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Instagram URL</label>
                            <input type="url" wire:model.defer="form.footer_social_instagram"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_social_instagram')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">X URL</label>
                            <input type="url" wire:model.defer="form.footer_social_x"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_social_x')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">WhatsApp URL</label>
                            <input type="url" wire:model.defer="form.footer_social_whatsapp"
                                class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.footer_social_whatsapp')
                                <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h3 class="text-lg font-bold text-slate-900">Public Page SEO Manager</h3>
                    <p class="mt-1 text-sm text-slate-600">Manage meta title, description, and social preview image per
                        public page.</p>

                    <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h4 class="text-sm font-bold text-slate-800">Home Page SEO</h4>
                            <div class="mt-3 space-y-2">
                                <input type="text" wire:model.defer="form.seo_home_meta_title"
                                    placeholder="Meta title"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_home_meta_title')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <textarea rows="2" wire:model.defer="form.seo_home_meta_description" placeholder="Meta description"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                @error('form.seo_home_meta_description')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <input type="text" wire:model.defer="form.seo_home_social_image_url"
                                    placeholder="Social image URL"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_home_social_image_url')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h4 class="text-sm font-bold text-slate-800">About Page SEO</h4>
                            <div class="mt-3 space-y-2">
                                <input type="text" wire:model.defer="form.seo_about_meta_title"
                                    placeholder="Meta title"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_about_meta_title')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <textarea rows="2" wire:model.defer="form.seo_about_meta_description" placeholder="Meta description"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                @error('form.seo_about_meta_description')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <input type="text" wire:model.defer="form.seo_about_social_image_url"
                                    placeholder="Social image URL"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_about_social_image_url')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h4 class="text-sm font-bold text-slate-800">Admission Page SEO</h4>
                            <div class="mt-3 space-y-2">
                                <input type="text" wire:model.defer="form.seo_admission_meta_title"
                                    placeholder="Meta title"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_admission_meta_title')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <textarea rows="2" wire:model.defer="form.seo_admission_meta_description" placeholder="Meta description"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                @error('form.seo_admission_meta_description')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <input type="text" wire:model.defer="form.seo_admission_social_image_url"
                                    placeholder="Social image URL"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_admission_social_image_url')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                            <h4 class="text-sm font-bold text-slate-800">Contact Page SEO</h4>
                            <div class="mt-3 space-y-2">
                                <input type="text" wire:model.defer="form.seo_contact_meta_title"
                                    placeholder="Meta title"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_contact_meta_title')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <textarea rows="2" wire:model.defer="form.seo_contact_meta_description" placeholder="Meta description"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                @error('form.seo_contact_meta_description')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                                <input type="text" wire:model.defer="form.seo_contact_social_image_url"
                                    placeholder="Social image URL"
                                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_contact_social_image_url')
                                    <p class="text-xs text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                            <h4 class="text-sm font-bold text-slate-800">Gallery Page SEO</h4>
                            <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-3">
                                <div>
                                    <input type="text" wire:model.defer="form.seo_gallery_meta_title"
                                        placeholder="Meta title"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                    @error('form.seo_gallery_meta_title')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <textarea rows="2" wire:model.defer="form.seo_gallery_meta_description" placeholder="Meta description"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                    @error('form.seo_gallery_meta_description')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                                <div>
                                    <input type="text" wire:model.defer="form.seo_gallery_social_image_url"
                                        placeholder="Social image URL"
                                        class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                    @error('form.seo_gallery_social_image_url')
                                        <p class="text-xs text-red-600">{{ $message }}</p>
                                    @enderror
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h3 class="text-lg font-bold text-slate-900">Reusable Media URLs</h3>
                            <p class="mt-1 text-sm text-slate-600">Use URLs from Media Library for SEO social images,
                                homepage/about assets, and gallery highlights.</p>
                        </div>
                        <a href="{{ route('media-library.index') }}"
                            class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700"
                            wire:navigate>
                            <i class="fas fa-photo-video mr-2"></i>Open Media Library
                        </a>
                    </div>

                    <div class="mt-4 space-y-2">
                        @forelse ($mediaAssets as $asset)
                            @php
                                $assetUrl = $asset->optimized_url ?: $asset->url;
                            @endphp
                            <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                                <div
                                    class="flex flex-col gap-1 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                                    <span
                                        class="font-semibold text-slate-800">{{ $asset->title ?: 'Untitled asset' }}</span>
                                    <span class="uppercase">{{ $asset->usage_area }}</span>
                                </div>
                                <input type="text" value="{{ $assetUrl }}" readonly onclick="this.select();"
                                    class="mt-2 w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" />
                            </div>
                        @empty
                            <p
                                class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">
                                No media assets available for this scope yet.</p>
                        @endforelse
                    </div>
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    @php
                        $additionalPageFields = [
                            [
                                'key' => 'key',
                                'label' => 'Page Key',
                                'placeholder' => 'community-outreach',
                                'help' => 'Unique key used under `pages.{key}`.',
                            ],
                            ['key' => 'title', 'label' => 'Page Title', 'placeholder' => 'Community Outreach'],
                            [
                                'key' => 'subtitle',
                                'label' => 'Subtitle / Eyebrow',
                                'placeholder' => 'Beyond the classroom',
                            ],
                            ['key' => 'hero_badge', 'label' => 'Hero Badge', 'placeholder' => 'Our Programs'],
                            [
                                'key' => 'hero_highlight',
                                'label' => 'Hero Highlight',
                                'placeholder' => 'Serving with impact',
                            ],
                            [
                                'key' => 'description',
                                'label' => 'Description',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Short overview for this page',
                            ],
                            [
                                'key' => 'content',
                                'label' => 'Main Content',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'Longer body content for this page',
                            ],
                            [
                                'key' => 'points',
                                'label' => 'Highlights',
                                'type' => 'list',
                                'fullWidth' => true,
                                'placeholder' => 'One highlight per line',
                            ],
                            [
                                'key' => 'meta_title',
                                'label' => 'Meta Title',
                                'placeholder' => 'SEO title for this custom page',
                                'advanced' => true,
                            ],
                            [
                                'key' => 'meta_description',
                                'label' => 'Meta Description',
                                'type' => 'textarea',
                                'fullWidth' => true,
                                'placeholder' => 'SEO description for this custom page',
                                'advanced' => true,
                            ],
                        ];
                    @endphp

                    <h3 class="text-lg font-bold text-slate-900">Additional Pages</h3>
                    <p class="mt-1 text-sm text-slate-600">
                        Create custom page content without writing JSON. Saved under `pages` in site settings.
                    </p>

                    <div class="mt-4">
                        <x-settings.json-collection-editor title="Custom Page Entries" model-path="pagesJson"
                            description="Add optional pages or content blocks keyed by page name." :fields="$additionalPageFields"
                            mode="keyed" key-field="key" empty-message="No custom page entries added yet." />
                    </div>

                    @error('pagesJson')
                        <p class="mt-2 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                    <h3 class="text-lg font-bold text-slate-900">Database Backup</h3>
                    <p class="mt-1 text-sm text-slate-600">Download the current database directly. No backup file is
                        saved in app storage.</p>

                    @if (auth()->user()->hasAnyRole(['super-admin', 'super_admin']))
                        <a href="{{ route('database.download') }}"
                            class="mt-4 inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                            <i class="fas fa-database mr-2"></i>Download Database
                        </a>
                    @else
                        <p
                            class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-600">
                            Database backup download is restricted to super-admin accounts.
                        </p>
                    @endif
                </div>
            </div>

            @if ($canApproveSettings && $workflowStatus === 'pending_approval')
                <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                    <label class="mb-1 block text-sm font-semibold text-red-800">Rejection note (required to
                        reject)</label>
                    <textarea rows="2" wire:model.defer="rejectionReason"
                        class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm"></textarea>
                    @error('rejectionReason')
                        <p class="mt-1 text-xs text-red-700">{{ $message }}</p>
                    @enderror
                </div>
            @endif

            <div class="flex flex-col items-end gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
                <button type="submit"
                    class="rounded-lg bg-slate-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                    <i class="fas fa-save mr-2"></i>Save Draft
                </button>
                @if ($canSubmitForApproval && !$canApproveSettings && $workflowStatus !== 'pending_approval')
                    <button type="button" wire:click="submitForApproval"
                        class="rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-700">
                        <i class="fas fa-paper-plane mr-2"></i>Submit For Approval
                    </button>
                @elseif ($canSubmitForApproval && !$canApproveSettings && $workflowStatus === 'pending_approval')
                    <span class="rounded-lg bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-800">
                        Draft is pending super-admin approval
                    </span>
                @endif
                @if ($canApproveSettings)
                    <button type="button" wire:click="publishDraft"
                        class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                        <i
                            class="fas fa-check mr-2"></i>{{ $workflowStatus === 'pending_approval' ? 'Approve & Publish' : 'Publish Draft' }}
                    </button>
                    @if ($workflowStatus === 'pending_approval')
                        <button type="button" wire:click="rejectPendingDraft"
                            class="rounded-lg bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
                            <i class="fas fa-times mr-2"></i>Reject Draft
                        </button>
                    @endif
                @endif
            </div>
        </form>

        <div class="rounded-lg bg-white p-6 shadow">
            <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                <div>
                    <h3 class="text-lg font-bold text-slate-900">Version History</h3>
                    <p class="mt-1 text-sm text-slate-600">Latest 25 versions for the selected scope.</p>
                </div>
                @if (!$canRollback)
                    <p class="text-xs font-semibold text-slate-500">Rollback is restricted to super-admins.</p>
                @endif
            </div>

            <div class="mt-4 overflow-x-auto">
                <table class="w-full min-w-[680px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wide text-slate-500">
                        <tr>
                            <th class="px-3 py-2">Version</th>
                            <th class="px-3 py-2">Stage</th>
                            <th class="px-3 py-2">Changed By</th>
                            <th class="px-3 py-2">Date</th>
                            <th class="px-3 py-2">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse ($history as $item)
                            <tr>
                                <td class="px-3 py-2 font-semibold text-slate-800">v{{ $item->version_number }}</td>
                                <td class="px-3 py-2">
                                    <span
                                        @class([
                                            'rounded-full px-2 py-1 text-[11px] font-semibold uppercase',
                                            'bg-slate-100 text-slate-700' => $item->stage === 'draft_saved',
                                            'bg-indigo-100 text-indigo-700' =>
                                                $item->stage === 'submitted_for_approval',
                                            'bg-blue-100 text-blue-700' => in_array(
                                                $item->stage,
                                                ['published', 'approved_published'],
                                                true),
                                            'bg-red-100 text-red-700' => $item->stage === 'rejected',
                                            'bg-amber-100 text-amber-700' => $item->stage === 'rollback',
                                        ])>{{ str_replace('_', ' ', $item->stage) }}</span>
                                </td>
                                <td class="px-3 py-2">{{ $item->changedBy?->name ?? 'System' }}</td>
                                <td class="px-3 py-2">{{ $item->created_at?->toDateTimeString() }}</td>
                                <td class="px-3 py-2">
                                    @if ($canRollback && in_array($item->stage, ['published', 'approved_published', 'rollback'], true))
                                        <button type="button" wire:click="rollbackToVersion({{ $item->id }})"
                                            class="rounded-md bg-amber-500 px-3 py-1.5 text-xs font-semibold text-white hover:bg-amber-600">
                                            Rollback to this
                                        </button>
                                    @else
                                        <span class="text-xs text-slate-400">-</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-500">No history yet
                                    for this scope.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            if (typeof window.jsonCollectionEditor !== 'function') {
                window.jsonCollectionEditor = function(modelBinding, fields, options) {
                    return {
                        modelBinding: modelBinding,
                        fields: Array.isArray(fields) ? fields : [],
                        mode: (options && options.mode) === 'keyed' ? 'keyed' : 'array',
                        keyField: (options && options.keyField) ? options.keyField : 'key',
                        items: [],
                        rawJson: '',
                        parseError: '',
                        editorIdSeed: 0,
                        suppressModelWatcher: false,
                        showAdvanced: false,
                        hasAdvancedFields: false,

                        init() {
                            this.hasAdvancedFields = this.fields.some((field) => Boolean(field.advanced));
                            this.showAdvanced = false;
                            this.hydrateFromModel(this.modelBinding);

                            this.$watch('modelBinding', (nextValue) => {
                                if (this.suppressModelWatcher) {
                                    return;
                                }

                                const nextRaw = this.normalizeRaw(nextValue);
                                if (nextRaw === this.rawJson) {
                                    return;
                                }

                                this.hydrateFromModel(nextRaw);
                            });
                        },

                        addItem() {
                            this.items.push(this.buildItem({}));
                            this.syncModel();
                        },

                        removeItem(index) {
                            this.items.splice(index, 1);
                            this.syncModel();
                        },

                        applyRawJson() {
                            this.parseError = '';

                            let parsed;
                            try {
                                parsed = JSON.parse(this.rawJson.trim() === '' ? (this.mode === 'keyed' ? '{}' : '[]') :
                                    this.rawJson);
                            } catch (error) {
                                this.parseError = 'Invalid JSON: ' + error.message;
                                return;
                            }

                            const normalized = this.normalizeCollection(parsed);
                            this.items = this.collectionToItems(normalized);
                            this.syncModel();
                        },

                        formatRawJson() {
                            if (this.rawJson.trim() === '') {
                                this.rawJson = this.mode === 'keyed' ? '{}' : '[]';
                                return;
                            }

                            try {
                                const parsed = JSON.parse(this.rawJson);
                                this.rawJson = JSON.stringify(parsed, null, 2);
                                this.parseError = '';
                            } catch (error) {
                                this.parseError = 'Invalid JSON: ' + error.message;
                            }
                        },

                        syncModel() {
                            const storageValue = this.collectionForStorage();
                            const encoded = JSON.stringify(storageValue, null, 2);

                            this.parseError = '';
                            this.rawJson = encoded;
                            this.suppressModelWatcher = true;
                            this.modelBinding = encoded;

                            setTimeout(() => {
                                this.suppressModelWatcher = false;
                            }, 0);
                        },

                        normalizeRaw(value) {
                            return typeof value === 'string' ? value : '';
                        },

                        hydrateFromModel(rawValue) {
                            const normalizedRaw = this.normalizeRaw(rawValue);
                            let parsed;

                            if (normalizedRaw.trim() === '') {
                                parsed = this.mode === 'keyed' ? {} : [];
                            } else {
                                try {
                                    parsed = JSON.parse(normalizedRaw);
                                } catch (error) {
                                    this.items = [];
                                    this.rawJson = normalizedRaw;
                                    this.parseError = 'Stored JSON is invalid: ' + error.message;
                                    return;
                                }
                            }

                            const normalized = this.normalizeCollection(parsed);
                            this.items = this.collectionToItems(normalized);
                            this.rawJson = JSON.stringify(normalized, null, 2);
                            this.parseError = '';
                        },

                        normalizeCollection(parsed) {
                            if (this.mode === 'keyed') {
                                if (parsed && typeof parsed === 'object' && !Array.isArray(parsed)) {
                                    return parsed;
                                }

                                if (Array.isArray(parsed)) {
                                    const mapped = {};
                                    parsed.forEach((entry, index) => {
                                        if (!entry || typeof entry !== 'object' || Array.isArray(entry)) {
                                            return;
                                        }

                                        const candidate = String(entry[this.keyField] || '').trim();
                                        const key = candidate !== '' ? candidate : 'item_' + (index + 1);
                                        const copy = {
                                            ...entry
                                        };
                                        delete copy[this.keyField];
                                        mapped[key] = copy;
                                    });
                                    return mapped;
                                }

                                return {};
                            }

                            if (Array.isArray(parsed)) {
                                return parsed;
                            }

                            if (parsed && typeof parsed === 'object') {
                                return Object.values(parsed);
                            }

                            return [];
                        },

                        collectionToItems(collection) {
                            if (this.mode === 'keyed') {
                                return Object.entries(collection).map(([key, value]) => {
                                    const entry = (value && typeof value === 'object' && !Array.isArray(value)) ? {
                                        ...value
                                    } : {};
                                    entry[this.keyField] = key;
                                    return this.buildItem(entry);
                                });
                            }

                            return collection.map((value) => this.buildItem(value));
                        },

                        buildItem(source) {
                            const sourceObject = (source && typeof source === 'object' && !Array.isArray(source)) ? source :
                            {};
                            const fieldKeys = this.fields.map((field) => field.key);
                            const item = {
                                _editorId: this.nextEditorId(),
                                _extra: {},
                            };

                            this.fields.forEach((field) => {
                                item[field.key] = this.editorValueForField(field, sourceObject[field.key]);
                            });

                            Object.keys(sourceObject).forEach((key) => {
                                if (!fieldKeys.includes(key)) {
                                    item._extra[key] = sourceObject[key];
                                }
                            });

                            return item;
                        },

                        editorValueForField(field, value) {
                            if (field.type === 'list') {
                                if (Array.isArray(value)) {
                                    return value.map((item) => String(item)).join('\n');
                                }
                                if (typeof value === 'string') {
                                    return value;
                                }
                                return '';
                            }

                            if (value === null || value === undefined) {
                                return '';
                            }

                            return String(value);
                        },

                        storageValueForField(field, value) {
                            if (field.type === 'list') {
                                if (Array.isArray(value)) {
                                    return value
                                        .map((entry) => String(entry).trim())
                                        .filter((entry) => entry !== '');
                                }

                                return String(value || '')
                                    .split(/\r?\n/)
                                    .map((entry) => entry.trim())
                                    .filter((entry) => entry !== '');
                            }

                            return String(value || '').trim();
                        },

                        collectionForStorage() {
                            if (this.mode === 'keyed') {
                                const mapped = {};
                                this.items.forEach((item, index) => {
                                    const rawKey = String(item[this.keyField] || '').trim();
                                    const key = rawKey !== '' ? rawKey : 'item_' + (index + 1);

                                    const payload = {
                                        ...(item._extra || {})
                                    };
                                    this.fields.forEach((field) => {
                                        if (field.key === this.keyField) {
                                            return;
                                        }
                                        payload[field.key] = this.storageValueForField(field, item[field
                                            .key]);
                                    });

                                    mapped[key] = payload;
                                });

                                return mapped;
                            }

                            return this.items.map((item) => {
                                const payload = {
                                    ...(item._extra || {})
                                };
                                this.fields.forEach((field) => {
                                    payload[field.key] = this.storageValueForField(field, item[field.key]);
                                });
                                return payload;
                            });
                        },

                        nextEditorId() {
                            this.editorIdSeed += 1;
                            return 'editor_item_' + this.editorIdSeed;
                        },
                    };
                };
            }
        </script>
    @endpush
</div>
