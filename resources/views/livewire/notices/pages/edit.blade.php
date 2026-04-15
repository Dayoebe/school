@extends('layouts.app', ['breadcrumbs' => [
    ['href' => route('dashboard'), 'text' => 'Dashboard'],
    ['href' => route('notices.index'), 'text' => 'Notices'],
    ['href' => route('notices.show', $notice), 'text' => 'View'],
    ['href' => route('notices.edit', $notice), 'text' => 'Edit', 'active'],
]])

@section('title', __("Edit Notice: {$notice->title}"))
@section('page_heading', __('Edit Notice'))

@section('content')
    <div class="space-y-5">
        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <div class="flex flex-col gap-4 lg:flex-row lg:items-center lg:justify-between">
                <div>
                    <h2 class="text-xl font-bold text-slate-900">Update Notice</h2>
                    <p class="mt-1 text-sm text-slate-600">Change the title, message, active dates, status, or attachment.</p>
                </div>

                <a href="{{ route('notices.show', $notice) }}" class="inline-flex items-center rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                    <i class="fas fa-arrow-left mr-2"></i>Back to Notice
                </a>
            </div>
        </div>

        <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
            <form action="{{ route('notices.update', $notice) }}" method="POST" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')
                <x-display-validation-errors />

                <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label for="title" class="mb-1 block text-sm font-semibold text-slate-700">Notice Title</label>
                        <input
                            id="title"
                            name="title"
                            type="text"
                            value="{{ old('title', $notice->title) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Enter notice title"
                        />
                        @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="content" class="mb-1 block text-sm font-semibold text-slate-700">Notice Content</label>
                        <textarea
                            id="content"
                            name="content"
                            rows="7"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                            placeholder="Write the full notice content"
                        >{{ old('content', $notice->content) }}</textarea>
                        @error('content') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="start_date" class="mb-1 block text-sm font-semibold text-slate-700">Start Date</label>
                        <input
                            id="start_date"
                            name="start_date"
                            type="date"
                            value="{{ old('start_date', $notice->start_date?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                        @error('start_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label for="stop_date" class="mb-1 block text-sm font-semibold text-slate-700">Stop Date</label>
                        <input
                            id="stop_date"
                            name="stop_date"
                            type="date"
                            value="{{ old('stop_date', $notice->stop_date?->format('Y-m-d')) }}"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                        @error('stop_date') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <input type="hidden" name="active" value="0">
                        <label class="inline-flex items-center gap-2 rounded-xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-semibold text-emerald-900">
                            <input
                                type="checkbox"
                                name="active"
                                value="1"
                                @checked(old('active', $notice->active))
                                class="rounded border-slate-300 text-emerald-600 focus:ring-emerald-500"
                            >
                            Keep this notice active and visible during its date window
                        </label>
                        @error('active') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                    </div>

                    <div class="md:col-span-2">
                        <label for="attachment" class="mb-1 block text-sm font-semibold text-slate-700">Replace Attachment (optional)</label>
                        <input
                            id="attachment"
                            type="file"
                            name="attachment"
                            accept=".gif,.jpg,.jpeg,.png,.doc,.docx,.pdf"
                            class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm focus:border-blue-500 focus:outline-none focus:ring-2 focus:ring-blue-200"
                        />
                        <p class="mt-1 text-xs text-slate-500">Accepted: JPG, PNG, GIF, PDF, DOC, DOCX (max 10MB).</p>
                        @error('attachment') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror

                        @if ($notice->attachment)
                            <div class="mt-3 rounded-xl border border-slate-200 bg-slate-50 p-4">
                                <p class="text-sm font-semibold text-slate-800">Current attachment</p>
                                <div class="mt-2 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
                                    <a
                                        href="{{ asset('storage/' . $notice->attachment) }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center text-sm font-semibold text-blue-700 hover:text-blue-800"
                                    >
                                        <i class="fas fa-paperclip mr-2"></i>Open current file
                                    </a>

                                    <label class="inline-flex items-center gap-2 text-sm font-semibold text-red-700">
                                        <input
                                            type="checkbox"
                                            name="remove_attachment"
                                            value="1"
                                            @checked(old('remove_attachment'))
                                            class="rounded border-slate-300 text-red-600 focus:ring-red-500"
                                        >
                                        Remove attachment
                                    </label>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>

                @if ($notice->send_email)
                    <div class="rounded-2xl border border-blue-200 bg-blue-50 p-5">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-blue-800">Email delivery history</h3>
                        <p class="mt-2 text-sm text-slate-700">
                            This notice was configured for email delivery. Editing it will not resend emails automatically.
                        </p>
                        <div class="mt-3 grid gap-3 sm:grid-cols-2">
                            <div class="rounded-xl bg-white px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Delivered</p>
                                <p class="mt-1 text-lg font-black text-slate-900">{{ number_format($notice->email_recipient_count ?? 0) }}</p>
                            </div>
                            <div class="rounded-xl bg-white px-4 py-3">
                                <p class="text-xs font-semibold uppercase tracking-wide text-slate-500">Sent At</p>
                                <p class="mt-1 text-sm font-semibold text-slate-900">{{ $notice->email_sent_at?->format('M d, Y g:i A') ?? 'Not sent' }}</p>
                            </div>
                        </div>
                    </div>
                @endif

                <div class="flex justify-end gap-3">
                    <a href="{{ route('notices.show', $notice) }}" class="rounded-lg border border-slate-300 px-4 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">
                        Cancel
                    </a>
                    <button type="submit" class="rounded-lg bg-blue-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-blue-700">
                        <i class="fas fa-save mr-2"></i>Update Notice
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
