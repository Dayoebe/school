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

