<div class="space-y-6">
    <div class="rounded-lg bg-white p-6 shadow">
        <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
            <div>
                <h2 class="text-2xl font-bold text-slate-900">Website Settings</h2>
                <p class="mt-1 text-sm text-slate-600">
                    Configure public page content for either all schools (general) or a specific school.
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
    </div>

    <form wire:submit.prevent="save" class="space-y-6">
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
                <h3 class="text-lg font-bold text-slate-900">Additional Page JSON</h3>
                <p class="mt-1 text-sm text-slate-600">
                    Use this for extra page keys not covered above. It is stored under `pages` in settings.
                </p>
                <textarea rows="14" wire:model.defer="pagesJson" class="mt-3 w-full rounded-lg border border-slate-300 px-3 py-2 font-mono text-xs"></textarea>
                @error('pagesJson') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        <div class="flex justify-end">
            <button type="submit" class="rounded-lg bg-blue-600 px-6 py-3 text-sm font-semibold text-white transition hover:bg-blue-700">
                <i class="fas fa-save mr-2"></i>Save Settings
            </button>
        </div>
    </form>
</div>
