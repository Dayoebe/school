<?php

namespace App\Livewire\Media;

use App\Models\MediaAsset;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageMediaLibrary extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $search = '';

    public string $usageFilter = 'all';

    public int $perPage = 12;

    public string $usageArea = 'general';

    public string $title = '';

    public string $altText = '';

    public $mediaFile = null;

    protected $queryString = [
        'search' => ['except' => ''],
        'usageFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage media library'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingUsageFilter(): void
    {
        $this->resetPage();
    }

    public function uploadAsset(): void
    {
        $this->validate([
            'usageArea' => ['required', 'in:general,home,about,gallery,seo'],
            'title' => ['nullable', 'string', 'max:255'],
            'altText' => ['nullable', 'string', 'max:255'],
            'mediaFile' => ['required', 'file', 'max:15360', 'mimetypes:image/jpeg,image/png,image/webp,image/gif,image/svg+xml,video/mp4,video/webm,application/pdf'],
        ]);

        $schoolId = (int) auth()->user()?->school_id;
        if ($schoolId <= 0) {
            $this->addError('mediaFile', 'Select a school context before uploading media.');
            return;
        }

        $disk = 'public';
        $storedPath = $this->mediaFile->store('media-library/' . $schoolId . '/' . now()->format('Y/m'), $disk);

        $mimeType = (string) ($this->mediaFile->getMimeType() ?: 'application/octet-stream');
        $fileSize = (int) ($this->mediaFile->getSize() ?: 0);

        $optimizedPath = null;
        $thumbnails = [];
        $width = null;
        $height = null;

        if (str_starts_with($mimeType, 'image/')) {
            [$width, $height] = $this->imageDimensions($disk, $storedPath);
            [$optimizedPath, $thumbnails] = $this->optimizeImage($disk, $storedPath, $mimeType, $width, $height);
        }

        MediaAsset::query()->create([
            'school_id' => $schoolId,
            'usage_area' => $this->usageArea,
            'title' => trim($this->title) ?: null,
            'alt_text' => trim($this->altText) ?: null,
            'disk' => $disk,
            'path' => $storedPath,
            'optimized_path' => $optimizedPath,
            'thumbnails' => $thumbnails !== [] ? $thumbnails : null,
            'mime_type' => $mimeType,
            'file_size' => $fileSize,
            'width' => $width,
            'height' => $height,
            'uploaded_by' => auth()->id(),
        ]);

        $this->reset(['title', 'altText', 'mediaFile']);
        $this->usageArea = 'general';

        session()->flash('success', 'Media asset uploaded successfully.');
    }

    public function deleteAsset(int $assetId): void
    {
        $asset = MediaAsset::query()
            ->where('school_id', auth()->user()?->school_id)
            ->findOrFail($assetId);

        Storage::disk($asset->disk ?: 'public')->delete($asset->path);

        if ($asset->optimized_path) {
            Storage::disk($asset->disk ?: 'public')->delete($asset->optimized_path);
        }

        if (is_array($asset->thumbnails)) {
            foreach ($asset->thumbnails as $thumbPath) {
                if (is_string($thumbPath) && $thumbPath !== '') {
                    Storage::disk($asset->disk ?: 'public')->delete($thumbPath);
                }
            }
        }

        $asset->delete();

        session()->flash('success', 'Media asset deleted.');
    }

    protected function imageDimensions(string $disk, string $path): array
    {
        $fullPath = Storage::disk($disk)->path($path);
        $size = @getimagesize($fullPath);

        if (!is_array($size) || count($size) < 2) {
            return [null, null];
        }

        return [(int) $size[0], (int) $size[1]];
    }

    protected function optimizeImage(string $disk, string $path, string $mimeType, ?int $width, ?int $height): array
    {
        if (!function_exists('imagecreatefromstring') || !function_exists('imagescale')) {
            return [null, []];
        }

        $fullPath = Storage::disk($disk)->path($path);
        $binary = @file_get_contents($fullPath);
        if ($binary === false) {
            return [null, []];
        }

        $sourceImage = @imagecreatefromstring($binary);
        if (!$sourceImage) {
            return [null, []];
        }

        $sourceWidth = $width ?: imagesx($sourceImage);
        $sourceHeight = $height ?: imagesy($sourceImage);

        $optimizedPath = null;
        $thumbnails = [];

        try {
            $maxWidth = min(1600, $sourceWidth > 0 ? $sourceWidth : 1600);
            $optimizedImage = $sourceImage;

            if ($sourceWidth > $maxWidth && $maxWidth > 0) {
                $scaledHeight = (int) round(($sourceHeight / $sourceWidth) * $maxWidth);
                $scaledImage = @imagescale($sourceImage, $maxWidth, $scaledHeight, IMG_BICUBIC_FIXED);
                if ($scaledImage) {
                    $optimizedImage = $scaledImage;
                }
            }

            $base = pathinfo($path, PATHINFO_FILENAME);
            $dir = trim(pathinfo($path, PATHINFO_DIRNAME), '.');

            if (function_exists('imagewebp')) {
                $optimizedPath = $dir . '/' . $base . '-optimized.webp';
                @imagewebp($optimizedImage, Storage::disk($disk)->path($optimizedPath), 82);

                $thumbSmall = $dir . '/' . $base . '-thumb-400.webp';
                $thumbLarge = $dir . '/' . $base . '-thumb-900.webp';

                $this->writeWebpThumb($optimizedImage, Storage::disk($disk)->path($thumbSmall), 400);
                $this->writeWebpThumb($optimizedImage, Storage::disk($disk)->path($thumbLarge), 900);

                $thumbnails = [
                    'small' => $thumbSmall,
                    'large' => $thumbLarge,
                ];
            }

            if ($optimizedImage !== $sourceImage) {
                imagedestroy($optimizedImage);
            }
        } finally {
            imagedestroy($sourceImage);
        }

        return [$optimizedPath, $thumbnails];
    }

    protected function writeWebpThumb($sourceImage, string $targetPath, int $targetWidth): void
    {
        if (!function_exists('imagewebp')) {
            return;
        }

        $srcWidth = imagesx($sourceImage);
        $srcHeight = imagesy($sourceImage);

        if ($srcWidth <= 0 || $srcHeight <= 0) {
            return;
        }

        $width = min($targetWidth, $srcWidth);
        $height = (int) round(($srcHeight / $srcWidth) * $width);

        $thumb = @imagescale($sourceImage, $width, $height, IMG_BICUBIC_FIXED);
        if (!$thumb) {
            return;
        }

        @imagewebp($thumb, $targetPath, 80);
        imagedestroy($thumb);
    }

    public function render()
    {
        $assets = MediaAsset::query()
            ->where('school_id', auth()->user()?->school_id)
            ->when($this->usageFilter !== 'all', function ($query): void {
                $query->where('usage_area', $this->usageFilter);
            })
            ->when($this->search !== '', function ($query): void {
                $search = '%' . trim($this->search) . '%';
                $query->where(function ($inner) use ($search): void {
                    $inner->where('title', 'like', $search)
                        ->orWhere('alt_text', 'like', $search)
                        ->orWhere('path', 'like', $search);
                });
            })
            ->latest()
            ->paginate($this->perPage);

        return view('livewire.media.manage-media-library', [
            'assets' => $assets,
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('media-library.index'), 'text' => 'Media Library', 'active' => true],
                ],
            ])
            ->title('Media Library');
    }
}
