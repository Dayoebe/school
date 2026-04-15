<div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
    <h3 class="text-lg font-bold text-slate-900">Create Notice</h3>
    <p class="mt-1 text-sm text-slate-600">Publish a notice with an active window and optional attachment.</p>

    <form action="{{ route('notices.store') }}" method="POST" enctype="multipart/form-data" class="mt-5 space-y-4">
        @csrf
        <x-display-validation-errors />

        <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
            <div class="md:col-span-2">
                <label for="title" class="mb-1 block text-sm font-semibold text-slate-700">Notice Title</label>
                <input id="title" name="title" type="text" value="{{ old('title') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    placeholder="Enter notice title" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="content" class="mb-1 block text-sm font-semibold text-slate-700">Notice Content</label>
                <textarea id="content" name="content" rows="6"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                    placeholder="Write the full notice content">{{ old('content') }}</textarea>
                @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="start_date" class="mb-1 block text-sm font-semibold text-slate-700">Start Date</label>
                <input id="start_date" name="start_date" type="date" value="{{ old('start_date') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div>
                <label for="stop_date" class="mb-1 block text-sm font-semibold text-slate-700">Stop Date</label>
                <input id="stop_date" name="stop_date" type="date" value="{{ old('stop_date') }}"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                @error('stop_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>

            <div class="md:col-span-2">
                <label for="attachment" class="mb-1 block text-sm font-semibold text-slate-700">Attachment (optional)</label>
                <input id="attachment" type="file" name="attachment" accept=".gif,.jpg,.jpeg,.png,.doc,.docx,.pdf"
                    class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200" />
                <p class="mt-1 text-xs text-slate-500">Accepted: JPG, PNG, GIF, PDF, DOC, DOCX (max 10MB).</p>
                @error('attachment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
        </div>

        @if ($canSendNoticeEmail)
            <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                <div class="flex flex-col gap-3 sm:flex-row sm:items-start sm:justify-between">
                    <div>
                        <h4 class="text-base font-bold text-slate-900">Email Delivery</h4>
                        <p class="mt-1 text-sm text-slate-600">
                            Optionally email this notice to selected school users after it is created.
                        </p>
                    </div>

                    <label class="inline-flex items-center gap-2 rounded-full bg-white px-4 py-2 text-sm font-semibold text-slate-800 shadow-sm">
                        <input
                            type="checkbox"
                            name="send_email"
                            value="1"
                            @checked(old('send_email'))
                            class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                        >
                        Send email
                    </label>
                </div>

                <div class="mt-5 grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="mb-2 block text-sm font-semibold text-slate-700">Email Recipients</label>
                        <div class="grid grid-cols-1 gap-2 sm:grid-cols-2 lg:grid-cols-3">
                            @foreach ($noticeEmailRoleOptions as $roleValue => $roleLabel)
                                <label class="flex items-center gap-2 rounded-xl border border-blue-100 bg-white px-3 py-2 text-sm font-medium text-slate-700">
                                    <input
                                        type="checkbox"
                                        name="email_recipient_roles[]"
                                        value="{{ $roleValue }}"
                                        @checked(in_array($roleValue, old('email_recipient_roles', ['student', 'teacher', 'parent']), true))
                                        class="rounded border-slate-300 text-blue-600 focus:ring-blue-500"
                                    >
                                    {{ $roleLabel }}
                                </label>
                            @endforeach
                        </div>
                        <p class="mt-2 text-xs text-slate-500">If none is selected, the email will default to students, teachers, parents, admins, and principals.</p>
                        @error('email_recipient_roles') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                        @error('email_recipient_roles.*') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="email_subject" class="mb-1 block text-sm font-semibold text-slate-700">Custom Email Subject</label>
                        <input
                            id="email_subject"
                            name="email_subject"
                            type="text"
                            value="{{ old('email_subject') }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Leave blank to use the notice title"
                        />
                        @error('email_subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="email_body" class="mb-1 block text-sm font-semibold text-slate-700">Custom Email Message</label>
                        <textarea
                            id="email_body"
                            name="email_body"
                            rows="5"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Leave blank to email the notice content"
                        >{{ old('email_body') }}</textarea>
                        <p class="mt-1 text-xs text-slate-500">Use this when the email needs a shorter or more direct message than the dashboard notice.</p>
                        @error('email_body') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>
        @endif

        <div class="flex justify-end gap-3">
            <a href="{{ route('notices.index') }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                Cancel
            </a>
            <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                <i class="fas fa-bullhorn mr-2"></i>Create Notice
            </button>
        </div>
    </form>
</div>
