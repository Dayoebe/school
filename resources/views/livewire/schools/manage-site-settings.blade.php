<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Website Settings</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Draft changes safely, submit for approval, publish to live, and rollback to a previous stable version.
                </p>
            </div>

            @if (auth()->user()->hasAnyRole(['super-admin', 'super_admin']))
                <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">Scope</label>
                        <select wire:model.live="scope" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                            <option value="general">General (All Schools)</option>
                            <option value="school">Per School</option>
                        </select>
                    </div>

                    @if ($scope === 'school')
                        <div>
                            <label class="mb-1 block text-xs font-semibold uppercase tracking-wide text-slate-500">School</label>
                            <select wire:model.live="schoolId" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200">
                                <option value="">Select school</option>
                                @foreach ($schools as $school)
                                    <option value="{{ $school->id }}">{{ $school->name }} ({{ $school->code }})</option>
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
                <p class="mt-1 text-sm font-semibold text-blue-800">{{ str_replace('_', ' ', ucfirst($workflowStatus)) }}</p>
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
                        <input type="text" wire:model.defer="form.school_name" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.school_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Location Label</label>
                        <input type="text" wire:model.defer="form.school_location" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Awka, Anambra" />
                        @error('form.school_location') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Motto</label>
                        <input type="text" wire:model.defer="form.school_motto" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.school_motto') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">About Summary</label>
                        <textarea rows="3" wire:model.defer="form.about_summary" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_summary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Theme Controls</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Primary Color (HEX)</label>
                        <input type="text" wire:model.defer="form.theme_primary_color" placeholder="#dc2626" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.theme_primary_color') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Logo URL (optional override)</label>
                        <input type="text" wire:model.defer="form.theme_logo_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.theme_logo_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Logo</label>
                        <input type="file" wire:model="themeLogoFile" accept="image/*" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        <p class="mt-1 text-[11px] text-slate-500">Recommended: square image, max 3MB.</p>
                        @error('themeLogoFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="themeLogoFile" class="mt-1 text-xs text-blue-600">Uploading logo...</div>
                        @if ($themeLogoFile)
                            <img src="{{ $themeLogoFile->temporaryUrl() }}" alt="Logo preview" class="mt-2 h-12 w-12 rounded-full border border-slate-200 object-cover" />
                        @elseif (!empty($form['theme_logo_url']))
                            <img src="{{ $form['theme_logo_url'] }}" alt="Current logo" class="mt-2 h-12 w-12 rounded-full border border-slate-200 object-cover" />
                        @endif
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Favicon URL (optional override)</label>
                        <input type="text" wire:model.defer="form.theme_favicon_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.theme_favicon_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Upload Favicon</label>
                        <input type="file" wire:model="themeFaviconFile" accept=".ico,image/png,image/jpeg,image/webp,image/svg+xml" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        <p class="mt-1 text-[11px] text-slate-500">Accepted: ICO, PNG, JPG, WEBP, SVG (max 2MB).</p>
                        @error('themeFaviconFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        <div wire:loading wire:target="themeFaviconFile" class="mt-1 text-xs text-blue-600">Uploading favicon...</div>
                        @if (!empty($form['theme_favicon_url']))
                            <a href="{{ $form['theme_favicon_url'] }}" target="_blank" rel="noopener noreferrer" class="mt-2 inline-flex items-center text-xs font-semibold text-blue-700 hover:text-blue-800">
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
                        <textarea rows="4" wire:model.defer="form.mission" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.mission') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Vision</label>
                        <textarea rows="4" wire:model.defer="form.vision" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.vision') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">School Promise</label>
                        <textarea rows="3" wire:model.defer="form.school_promise" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.school_promise') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Contact Details</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Address</label>
                        <textarea rows="3" wire:model.defer="form.contact_address" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.contact_address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="grid grid-cols-1 gap-3 sm:grid-cols-2">
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Phone (Primary)</label>
                            <input type="text" wire:model.defer="form.contact_phone_primary" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_phone_primary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="mb-1 block text-sm font-semibold text-slate-700">Phone (Secondary)</label>
                            <input type="text" wire:model.defer="form.contact_phone_secondary" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.contact_phone_secondary') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Email</label>
                        <input type="email" wire:model.defer="form.contact_email" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.contact_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Google Map Embed URL</label>
                        <input type="url" wire:model.defer="form.contact_map_embed_url" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.contact_map_embed_url') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Home Page</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                        <input type="text" wire:model.defer="form.home_hero_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.home_hero_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                        <input type="text" wire:model.defer="form.home_hero_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.home_hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                        <input type="text" wire:model.defer="form.home_hero_highlight" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.home_hero_highlight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                        <textarea rows="3" wire:model.defer="form.home_hero_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.home_hero_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Block Title</label>
                        <input type="text" wire:model.defer="form.home_contact_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.home_contact_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Block Description</label>
                        <textarea rows="2" wire:model.defer="form.home_contact_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.home_contact_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">About Page</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                        <input type="text" wire:model.defer="form.about_hero_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_hero_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                        <input type="text" wire:model.defer="form.about_hero_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                        <input type="text" wire:model.defer="form.about_hero_highlight" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_hero_highlight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Identity Section Title</label>
                        <input type="text" wire:model.defer="form.about_identity_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_identity_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                        <textarea rows="3" wire:model.defer="form.about_hero_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_hero_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Story Title</label>
                        <input type="text" wire:model.defer="form.about_story_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_story_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Story Description</label>
                        <textarea rows="3" wire:model.defer="form.about_story_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_story_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Story Extra Paragraph</label>
                        <textarea rows="3" wire:model.defer="form.about_story_extra" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_story_extra') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div class="md:col-span-2">
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Story Quote</label>
                        <textarea rows="2" wire:model.defer="form.about_story_quote" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_story_quote') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Pillars Section Title</label>
                        <input type="text" wire:model.defer="form.about_pillars_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_pillars_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Leadership Section Title</label>
                        <input type="text" wire:model.defer="form.about_leadership_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_leadership_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Stats Section Title</label>
                        <input type="text" wire:model.defer="form.about_stats_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_stats_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">FAQ Section Title</label>
                        <input type="text" wire:model.defer="form.about_faq_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_faq_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">FAQ Eyebrow</label>
                        <input type="text" wire:model.defer="form.about_faq_subtitle" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_faq_subtitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Updates Section Title</label>
                        <input type="text" wire:model.defer="form.about_updates_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_updates_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Updates Section Subtitle</label>
                        <input type="text" wire:model.defer="form.about_updates_subtitle" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_updates_subtitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Student Voice Title</label>
                        <input type="text" wire:model.defer="form.about_student_voice_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_student_voice_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Student Voice Subtitle</label>
                        <input type="text" wire:model.defer="form.about_student_voice_subtitle" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_student_voice_subtitle') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Visit Section Title</label>
                        <input type="text" wire:model.defer="form.about_visit_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.about_visit_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Visit Section Description</label>
                        <textarea rows="3" wire:model.defer="form.about_visit_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.about_visit_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">About Page Collections (JSON)</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Edit advanced About page sections here: FAQ, milestones, updates, calendar, student voice, and other dynamic blocks.
                </p>
                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Milestones JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_milestones_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_milestones_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Values JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_values_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_values_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Pillar Tabs JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_pillar_tabs_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_pillar_tabs_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Pillars JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_pillars_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_pillars_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Leadership JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_leadership_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_leadership_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Stats JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_stats_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_stats_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">FAQ JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_faqs_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_faqs_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Upcoming Updates JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_updates_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_updates_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">School Calendar JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_calendar_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_calendar_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Student Voice JSON</label>
                        <textarea rows="8" wire:model.defer="form.about_student_voice_json" class="w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                        @error('form.about_student_voice_json') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Admission Page</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Badge</label>
                        <input type="text" wire:model.defer="form.admission_hero_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.admission_hero_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Title</label>
                        <input type="text" wire:model.defer="form.admission_hero_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.admission_hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Highlight</label>
                        <input type="text" wire:model.defer="form.admission_hero_highlight" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.admission_hero_highlight') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Hero Description</label>
                        <textarea rows="3" wire:model.defer="form.admission_hero_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.admission_hero_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Contact & Gallery Hero Text</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero Badge</label>
                        <input type="text" wire:model.defer="form.contact_hero_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.contact_hero_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero Title</label>
                        <input type="text" wire:model.defer="form.contact_hero_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.contact_hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Contact Hero Description</label>
                        <textarea rows="3" wire:model.defer="form.contact_hero_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.contact_hero_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <hr class="my-2 border-slate-200" />

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero Badge</label>
                        <input type="text" wire:model.defer="form.gallery_hero_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.gallery_hero_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero Title</label>
                        <input type="text" wire:model.defer="form.gallery_hero_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.gallery_hero_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Gallery Hero Description</label>
                        <textarea rows="3" wire:model.defer="form.gallery_hero_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.gallery_hero_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow">
                <h3 class="text-lg font-bold text-slate-900">Footer & SEO</h3>
                <div class="mt-4 space-y-3">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Badge</label>
                        <input type="text" wire:model.defer="form.footer_admissions_badge" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_admissions_badge') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Title</label>
                        <input type="text" wire:model.defer="form.footer_admissions_title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_admissions_title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Footer Description</label>
                        <textarea rows="2" wire:model.defer="form.footer_admissions_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.footer_admissions_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Copyright Suffix</label>
                        <input type="text" wire:model.defer="form.footer_copyright_suffix" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="All rights reserved." />
                        @error('form.footer_copyright_suffix') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <hr class="my-2 border-slate-200" />

                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Description</label>
                        <textarea rows="2" wire:model.defer="form.meta_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.meta_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Keywords</label>
                        <input type="text" wire:model.defer="form.meta_keywords" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.meta_keywords') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Meta Author</label>
                        <input type="text" wire:model.defer="form.meta_author" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.meta_author') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">OG Description</label>
                        <textarea rows="2" wire:model.defer="form.meta_og_description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                        @error('form.meta_og_description') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">Footer Social Links</h3>
                <div class="mt-4 grid grid-cols-1 gap-3 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Facebook URL</label>
                        <input type="url" wire:model.defer="form.footer_social_facebook" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_social_facebook') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">Instagram URL</label>
                        <input type="url" wire:model.defer="form.footer_social_instagram" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_social_instagram') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">X URL</label>
                        <input type="url" wire:model.defer="form.footer_social_x" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_social_x') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-semibold text-slate-700">WhatsApp URL</label>
                        <input type="url" wire:model.defer="form.footer_social_whatsapp" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                        @error('form.footer_social_whatsapp') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">Public Page SEO Manager</h3>
                <p class="mt-1 text-sm text-slate-600">Manage meta title, description, and social preview image per public page.</p>

                <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-bold text-slate-800">Home Page SEO</h4>
                        <div class="mt-3 space-y-2">
                            <input type="text" wire:model.defer="form.seo_home_meta_title" placeholder="Meta title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_home_meta_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <textarea rows="2" wire:model.defer="form.seo_home_meta_description" placeholder="Meta description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.seo_home_meta_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <input type="text" wire:model.defer="form.seo_home_social_image_url" placeholder="Social image URL" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_home_social_image_url') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-bold text-slate-800">About Page SEO</h4>
                        <div class="mt-3 space-y-2">
                            <input type="text" wire:model.defer="form.seo_about_meta_title" placeholder="Meta title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_about_meta_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <textarea rows="2" wire:model.defer="form.seo_about_meta_description" placeholder="Meta description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.seo_about_meta_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <input type="text" wire:model.defer="form.seo_about_social_image_url" placeholder="Social image URL" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_about_social_image_url') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-bold text-slate-800">Admission Page SEO</h4>
                        <div class="mt-3 space-y-2">
                            <input type="text" wire:model.defer="form.seo_admission_meta_title" placeholder="Meta title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_admission_meta_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <textarea rows="2" wire:model.defer="form.seo_admission_meta_description" placeholder="Meta description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.seo_admission_meta_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <input type="text" wire:model.defer="form.seo_admission_social_image_url" placeholder="Social image URL" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_admission_social_image_url') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4">
                        <h4 class="text-sm font-bold text-slate-800">Contact Page SEO</h4>
                        <div class="mt-3 space-y-2">
                            <input type="text" wire:model.defer="form.seo_contact_meta_title" placeholder="Meta title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_contact_meta_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <textarea rows="2" wire:model.defer="form.seo_contact_meta_description" placeholder="Meta description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                            @error('form.seo_contact_meta_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            <input type="text" wire:model.defer="form.seo_contact_social_image_url" placeholder="Social image URL" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                            @error('form.seo_contact_social_image_url') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                        </div>
                    </div>
                    <div class="rounded-lg border border-slate-200 bg-slate-50 p-4 md:col-span-2">
                        <h4 class="text-sm font-bold text-slate-800">Gallery Page SEO</h4>
                        <div class="mt-3 grid grid-cols-1 gap-2 md:grid-cols-3">
                            <div>
                                <input type="text" wire:model.defer="form.seo_gallery_meta_title" placeholder="Meta title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_gallery_meta_title') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <textarea rows="2" wire:model.defer="form.seo_gallery_meta_description" placeholder="Meta description" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm"></textarea>
                                @error('form.seo_gallery_meta_description') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <input type="text" wire:model.defer="form.seo_gallery_social_image_url" placeholder="Social image URL" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                                @error('form.seo_gallery_social_image_url') <p class="text-xs text-red-600">{{ $message }}</p> @enderror
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                    <div>
                        <h3 class="text-lg font-bold text-slate-900">Reusable Media URLs</h3>
                        <p class="mt-1 text-sm text-slate-600">Use URLs from Media Library for SEO social images, homepage/about assets, and gallery highlights.</p>
                    </div>
                    <a href="{{ route('media-library.index') }}" class="inline-flex items-center rounded-lg bg-indigo-600 px-4 py-2 text-xs font-semibold text-white hover:bg-indigo-700" wire:navigate>
                        <i class="fas fa-photo-video mr-2"></i>Open Media Library
                    </a>
                </div>

                <div class="mt-4 space-y-2">
                    @forelse ($mediaAssets as $asset)
                        @php($assetUrl = $asset->optimized_url ?: $asset->url)
                        <div class="rounded-lg border border-slate-200 bg-slate-50 p-3">
                            <div class="flex flex-col gap-1 text-xs text-slate-600 sm:flex-row sm:items-center sm:justify-between">
                                <span class="font-semibold text-slate-800">{{ $asset->title ?: 'Untitled asset' }}</span>
                                <span class="uppercase">{{ $asset->usage_area }}</span>
                            </div>
                            <input type="text" value="{{ $assetUrl }}" readonly onclick="this.select();" class="mt-2 w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" />
                        </div>
                    @empty
                        <p class="rounded-lg border border-slate-200 bg-slate-50 px-4 py-6 text-center text-sm text-slate-500">No media assets available for this scope yet.</p>
                    @endforelse
                </div>
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">Additional Page JSON</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Use this for extra page keys not covered above. It is stored under `pages` in settings.
                </p>
                <textarea rows="14" wire:model.defer="pagesJson" class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                @error('pagesJson') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="rounded-lg bg-white p-6 shadow lg:col-span-2">
                <h3 class="text-lg font-bold text-slate-900">Database Backup</h3>
                <p class="mt-1 text-sm text-slate-600">Download the current database directly. No backup file is saved in app storage.</p>

                @if (auth()->user()->hasAnyRole(['super-admin', 'super_admin']))
                    <a href="{{ route('database.download') }}" class="mt-4 inline-flex items-center rounded-lg bg-emerald-600 px-4 py-2 text-sm font-semibold text-white hover:bg-emerald-700">
                        <i class="fas fa-database mr-2"></i>Download Database
                    </a>
                @else
                    <p class="mt-3 rounded-lg border border-slate-200 bg-slate-50 px-4 py-3 text-xs font-semibold text-slate-600">
                        Database backup download is restricted to super-admin accounts.
                    </p>
                @endif
            </div>
        </div>

        @if ($canApproveSettings && $workflowStatus === 'pending_approval')
            <div class="rounded-lg border border-red-200 bg-red-50 p-4">
                <label class="mb-1 block text-sm font-semibold text-red-800">Rejection note (required to reject)</label>
                <textarea rows="2" wire:model.defer="rejectionReason" class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm"></textarea>
                @error('rejectionReason') <p class="mt-1 text-xs text-red-700">{{ $message }}</p> @enderror
            </div>
        @endif

        <div class="flex flex-col items-end gap-3 sm:flex-row sm:flex-wrap sm:justify-end">
            <button type="submit" class="rounded-lg bg-slate-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-slate-800">
                <i class="fas fa-save mr-2"></i>Save Draft
            </button>
            @if ($canSubmitForApproval && !$canApproveSettings && $workflowStatus !== 'pending_approval')
                <button type="button" wire:click="submitForApproval" class="rounded-lg bg-orange-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-orange-700">
                    <i class="fas fa-paper-plane mr-2"></i>Submit For Approval
                </button>
            @elseif ($canSubmitForApproval && !$canApproveSettings && $workflowStatus === 'pending_approval')
                <span class="rounded-lg bg-amber-100 px-4 py-2 text-xs font-semibold text-amber-800">
                    Draft is pending super-admin approval
                </span>
            @endif
            @if ($canApproveSettings)
                <button type="button" wire:click="publishDraft" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                    <i class="fas fa-check mr-2"></i>{{ $workflowStatus === 'pending_approval' ? 'Approve & Publish' : 'Publish Draft' }}
                </button>
                @if ($workflowStatus === 'pending_approval')
                    <button type="button" wire:click="rejectPendingDraft" class="rounded-lg bg-red-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-red-700">
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
                                <span @class([
                                    'rounded-full px-2 py-1 text-[11px] font-semibold uppercase',
                                    'bg-slate-100 text-slate-700' => $item->stage === 'draft_saved',
                                    'bg-indigo-100 text-indigo-700' => $item->stage === 'submitted_for_approval',
                                    'bg-blue-100 text-blue-700' => in_array($item->stage, ['published', 'approved_published'], true),
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
                            <td colspan="5" class="px-3 py-8 text-center text-sm text-slate-500">No history yet for this scope.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
