<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    @if($submitted)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white">
                <i class="fas fa-check text-xl"></i>
            </div>
            <h3 class="mt-4 text-xl font-black text-emerald-800">Application Submitted</h3>
            <p class="mt-2 text-sm text-emerald-700">Your admission request has been received successfully.</p>
            <p class="mt-3 text-sm font-semibold text-slate-700">
                Reference Number:
                <span class="rounded bg-white px-2 py-1 font-black text-emerald-700">{{ $submittedReference }}</span>
            </p>
            <button wire:click="submitAnother" type="button"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                <i class="fas fa-rotate-right"></i>
                <span>Submit Another</span>
            </button>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-5" enctype="multipart/form-data">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">School <span class="text-red-600">*</span></label>
                    <select wire:model.live="school_id"
                        class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                        wire:loading.attr="disabled" wire:target="school_id">
                        <option value="">Select School</option>
                        @foreach($schools as $school)
                            <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                @if($school_id !== '')
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Desired Class <span class="text-red-600">*</span></label>
                        <select wire:key="class-select-{{ $school_id !== '' ? $school_id : 'none' }}"
                            wire:model.live="my_class_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                            wire:loading.attr="disabled" wire:target="my_class_id,school_id">
                            <option value="">Select Class</option>
                            @foreach($classes as $myClass)
                                <option value="{{ $myClass['id'] }}">{{ $myClass['name'] }}</option>
                            @endforeach
                        </select>
                        @if($classNotice !== '')
                            <p class="mt-1 text-xs text-amber-700">{{ $classNotice }}</p>
                        @endif
                        @error('my_class_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif

                @if($school_id !== '')
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Desired Section</label>
                        <select wire:key="section-select-{{ $my_class_id !== '' ? $my_class_id : 'none' }}"
                            wire:model="section_id"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"
                            {{ $my_class_id === '' ? 'disabled' : '' }}>
                            <option value="">Select Section (Optional)</option>
                            @foreach($sections as $section)
                                <option value="{{ $section['id'] }}">{{ $section['name'] }}</option>
                            @endforeach
                        </select>
                        @if($sectionNotice !== '')
                            <p class="mt-1 text-xs text-amber-700">{{ $sectionNotice }}</p>
                        @endif
                        @error('section_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                @endif
            </div>

            <div class="border-t border-slate-200 pt-4">
                <h4 class="text-sm font-black uppercase tracking-wider text-red-700">Student Information</h4>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Student Full Name <span class="text-red-600">*</span></label>
                        <input wire:model="student_name" type="text" placeholder="Enter full name"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('student_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Student Email</label>
                        <input wire:model="student_email" type="email" placeholder="Optional"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('student_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Gender <span class="text-red-600">*</span></label>
                        <select wire:model="gender" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                            <option value="">Select Gender</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                        @error('gender') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Date of Birth <span class="text-red-600">*</span></label>
                        <input wire:model="birthday" type="date"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('birthday') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Previous School</label>
                        <input wire:model="previous_school" type="text" placeholder="Optional"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('previous_school') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <h4 class="text-sm font-black uppercase tracking-wider text-blue-700">Parent / Guardian Information</h4>
                <div class="mt-3 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Guardian Name <span class="text-red-600">*</span></label>
                        <input wire:model="guardian_name" type="text" placeholder="Enter guardian name"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('guardian_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Relationship</label>
                        <input wire:model="guardian_relationship" type="text" placeholder="Parent, Uncle, Aunt..."
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('guardian_relationship') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Guardian Phone <span class="text-red-600">*</span></label>
                        <input wire:model="guardian_phone" type="text" placeholder="Enter phone number"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('guardian_phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Guardian Email</label>
                        <input wire:model="guardian_email" type="email" placeholder="Optional"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        @error('guardian_email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Home Address <span class="text-red-600">*</span></label>
                        <textarea wire:model="address" rows="3" placeholder="Enter full address"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                        @error('address') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Additional Notes</label>
                        <textarea wire:model="notes" rows="3" placeholder="Medical note, special support needs, or additional details"
                            class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                        @error('notes') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Supporting Document (Optional)</label>
                        <input wire:model="document" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx"
                            class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm file:mr-3 file:rounded-lg file:border-0 file:bg-red-600 file:px-3 file:py-2 file:text-xs file:font-bold file:text-white hover:file:bg-red-700">
                        <p class="mt-1 text-xs text-slate-500">Accepted: PDF, JPG, PNG, DOC, DOCX (max 5MB)</p>
                        <div wire:loading wire:target="document" class="mt-1 text-xs font-semibold text-blue-700">
                            Uploading document...
                        </div>
                        @error('document') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    wire:loading.attr="disabled">
                    <i class="fas fa-paper-plane" wire:loading.remove wire:target="submit"></i>
                    <i class="fas fa-spinner fa-spin" wire:loading wire:target="submit"></i>
                    <span wire:loading.remove wire:target="submit">Submit Admission Form</span>
                    <span wire:loading wire:target="submit">Submitting...</span>
                </button>
            </div>
        </form>
    @endif
</div>
