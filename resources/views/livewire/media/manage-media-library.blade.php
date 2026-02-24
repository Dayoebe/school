<div class="space-y-6">
    @if (session()->has('success'))
        <div class="rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800">
            {{ session('success') }}
        </div>
    @endif

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <h2 class="text-xl font-bold text-slate-900">Media Library</h2>
        <p class="mt-1 text-sm text-slate-600">Upload reusable homepage/about/gallery/SEO assets with optimized image variants.</p>

        <div class="mt-4 grid grid-cols-1 gap-4 md:grid-cols-2">
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Usage Area</label>
                <select wire:model="usageArea" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="general">General</option>
                    <option value="home">Home</option>
                    <option value="about">About</option>
                    <option value="gallery">Gallery</option>
                    <option value="seo">SEO / Social</option>
                </select>
                @error('usageArea') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Title (optional)</label>
                <input type="text" wire:model="title" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Asset title" />
                @error('title') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Alt Text (optional)</label>
                <input type="text" wire:model="altText" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Alternative text for accessibility" />
                @error('altText') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
            </div>
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Asset File</label>
                <input type="file" wire:model="mediaFile" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" />
                <p class="mt-1 text-xs text-slate-500">Accepted: JPG, PNG, WEBP, GIF, SVG, MP4, WEBM, PDF (max 15MB).</p>
                @error('mediaFile') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                <div wire:loading wire:target="mediaFile" class="mt-1 text-xs text-blue-600">Uploading file...</div>
            </div>
        </div>

        <div class="mt-5 flex justify-end">
            <button type="button" wire:click="uploadAsset" class="rounded-lg bg-indigo-600 px-5 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">
                <i class="fas fa-upload mr-2"></i>Upload Asset
            </button>
        </div>
    </div>

    <div class="rounded-lg border border-slate-200 bg-white p-6 shadow-sm">
        <div class="grid grid-cols-1 gap-4 md:grid-cols-3">
            <div class="md:col-span-2">
                <label class="mb-1 block text-sm font-semibold text-slate-700">Search</label>
                <input type="text" wire:model.live.debounce.300ms="search" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm" placeholder="Title, alt text, or path" />
            </div>
            <div>
                <label class="mb-1 block text-sm font-semibold text-slate-700">Filter by Usage</label>
                <select wire:model.live="usageFilter" class="w-full rounded-lg border border-slate-300 px-3 py-2 text-sm">
                    <option value="all">All</option>
                    <option value="general">General</option>
                    <option value="home">Home</option>
                    <option value="about">About</option>
                    <option value="gallery">Gallery</option>
                    <option value="seo">SEO / Social</option>
                </select>
            </div>
        </div>

        <div class="mt-5 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3">
            @forelse ($assets as $asset)
                @php
                    $isImage = str_starts_with((string) $asset->mime_type, 'image/');
                    $previewUrl = $asset->preview_url;
                    $assetUrl = $asset->url;
                @endphp
                <article class="overflow-hidden rounded-lg border border-slate-200 bg-slate-50">
                    <div class="h-44 w-full overflow-hidden bg-slate-200">
                        @if ($isImage)
                            <img src="{{ $previewUrl }}" alt="{{ $asset->alt_text ?: ($asset->title ?: 'Media asset preview') }}" class="h-full w-full object-cover" loading="lazy" />
                        @else
                            <div class="flex h-full w-full items-center justify-center text-slate-600">
                                <i class="fas fa-file-alt text-4xl"></i>
                            </div>
                        @endif
                    </div>
                    <div class="space-y-2 p-4">
                        <div class="flex items-center justify-between gap-2">
                            <p class="truncate text-sm font-semibold text-slate-900">{{ $asset->title ?: 'Untitled asset' }}</p>
                            <span class="rounded-full bg-slate-200 px-2 py-1 text-[10px] font-semibold uppercase text-slate-700">{{ $asset->usage_area }}</span>
                        </div>
                        <p class="text-xs text-slate-600">{{ $asset->mime_type ?: 'Unknown type' }}</p>
                        <p class="text-xs text-slate-600">{{ number_format(($asset->file_size ?? 0) / 1024, 1) }} KB</p>
                        @if ($asset->width && $asset->height)
                            <p class="text-xs text-slate-600">{{ $asset->width }} x {{ $asset->height }}</p>
                        @endif

                        <label class="block text-[11px] font-semibold uppercase tracking-wide text-slate-500">Reuse URL</label>
                        <input type="text" value="{{ $assetUrl }}" readonly onclick="this.select();" class="w-full rounded-md border border-slate-300 bg-white px-2 py-1 text-xs text-slate-700" />

                        <div class="flex items-center justify-between gap-2 pt-1">
                            <a href="{{ $assetUrl }}" target="_blank" rel="noopener noreferrer" class="rounded bg-indigo-100 px-3 py-1 text-xs font-semibold text-indigo-700 hover:bg-indigo-200">
                                Open
                            </a>
                            <button type="button" wire:click="deleteAsset({{ $asset->id }})" wire:confirm="Delete this media asset?" class="rounded bg-red-100 px-3 py-1 text-xs font-semibold text-red-700 hover:bg-red-200">
                                Delete
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <div class="col-span-full rounded-lg border border-slate-200 bg-white px-4 py-10 text-center text-sm text-slate-500">
                    No assets in media library yet.
                </div>
            @endforelse
        </div>

        @if ($assets->hasPages())
            <div class="mt-5 border-t border-slate-200 pt-4">
                {{ $assets->links() }}
            </div>
        @endif
    </div>
</div>
