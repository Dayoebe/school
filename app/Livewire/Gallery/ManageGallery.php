<?php

namespace App\Livewire\Gallery;

use App\Models\GalleryCategory;
use App\Models\GalleryItem;
use App\Models\School;
use Illuminate\Http\UploadedFile;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use RuntimeException;
use Livewire\Component;
use Livewire\WithFileUploads;
use Livewire\WithPagination;

class ManageGallery extends Component
{
    use WithFileUploads;
    use WithPagination;

    public string $activeTab = 'items';
    public string $search = '';
    public string $categoryFilter = 'all';
    public int $perPage = 12;

    public ?int $categoryId = null;
    public string $categoryName = '';
    public string $categoryDescription = '';
    public string $categoryColor = 'red';
    public int $categorySortOrder = 0;
    public bool $categoryIsActive = true;

    public ?int $itemId = null;
    public string $itemTitle = '';
    public string $itemCaption = '';
    public string $itemMediaUrl = '';
    public $itemImage = null;
    public string $itemCategoryId = '';
    public string $itemTakenOn = '';
    public int $itemSortOrder = 0;
    public bool $itemIsFeatured = false;
    public bool $itemIsActive = true;

    protected $queryString = [
        'activeTab' => ['except' => 'items'],
        'search' => ['except' => ''],
        'categoryFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        if (!auth()->check() || !auth()->user()->can('manage gallery')) {
            abort(403);
        }
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingCategoryFilter(): void
    {
        $this->resetPage();
    }

    public function switchTab(string $tab): void
    {
        if (!in_array($tab, ['items', 'categories'], true)) {
            return;
        }

        $this->activeTab = $tab;
    }

    public function saveCategory(): void
    {
        $schoolId = $this->activeSchoolId();
        if (!$schoolId) {
            session()->flash('error', 'Set a default school before managing gallery categories.');
            return;
        }

        $validated = $this->validate([
            'categoryName' => ['required', 'string', 'max:120'],
            'categoryDescription' => ['nullable', 'string', 'max:1000'],
            'categoryColor' => ['required', 'string', 'max:40'],
            'categorySortOrder' => ['nullable', 'integer', 'min:0'],
            'categoryIsActive' => ['boolean'],
        ]);

        $slug = $this->generateUniqueCategorySlug(
            Str::slug($validated['categoryName']) ?: 'gallery-category',
            $schoolId,
            $this->categoryId
        );

        $payload = [
            'name' => trim($validated['categoryName']),
            'slug' => $slug,
            'description' => trim((string) $validated['categoryDescription']) ?: null,
            'color' => trim($validated['categoryColor']),
            'sort_order' => (int) $validated['categorySortOrder'],
            'is_active' => (bool) $validated['categoryIsActive'],
        ];

        if ($this->categoryId) {
            $category = $this->categoriesQuery()->findOrFail($this->categoryId);
            $category->update($payload);
            session()->flash('success', 'Gallery category updated.');
        } else {
            $payload['school_id'] = $schoolId;
            GalleryCategory::create($payload);
            session()->flash('success', 'Gallery category created.');
        }

        $this->resetCategoryForm();
    }

    public function editCategory(int $categoryId): void
    {
        $category = $this->categoriesQuery()->findOrFail($categoryId);

        $this->categoryId = $category->id;
        $this->categoryName = $category->name;
        $this->categoryDescription = (string) ($category->description ?? '');
        $this->categoryColor = $category->color ?: 'red';
        $this->categorySortOrder = (int) $category->sort_order;
        $this->categoryIsActive = (bool) $category->is_active;
        $this->activeTab = 'categories';
    }

    public function deleteCategory(int $categoryId): void
    {
        $category = $this->categoriesQuery()->findOrFail($categoryId);
        $category->delete();

        if ($this->categoryId === $categoryId) {
            $this->resetCategoryForm();
        }

        if ($this->categoryFilter !== 'all' && (int) $this->categoryFilter === $categoryId) {
            $this->categoryFilter = 'all';
        }

        session()->flash('success', 'Gallery category deleted.');
    }

    public function resetCategoryForm(): void
    {
        $this->reset([
            'categoryId',
            'categoryName',
            'categoryDescription',
            'categoryColor',
            'categorySortOrder',
            'categoryIsActive',
        ]);

        $this->categoryColor = 'red';
        $this->categorySortOrder = 0;
        $this->categoryIsActive = true;
        $this->resetValidation();
    }

    public function saveItem(): void
    {
        $schoolId = $this->activeSchoolId();
        if (!$schoolId) {
            session()->flash('error', 'Set a default school before managing gallery items.');
            return;
        }

        $validated = $this->validate([
            'itemTitle' => ['required', 'string', 'max:160'],
            'itemCaption' => ['nullable', 'string', 'max:2000'],
            'itemMediaUrl' => ['nullable', 'url', 'max:2048', 'required_without:itemImage'],
            'itemImage' => ['nullable', 'image', 'max:5120'],
            'itemCategoryId' => ['required', 'integer'],
            'itemTakenOn' => ['nullable', 'date'],
            'itemSortOrder' => ['nullable', 'integer', 'min:0'],
            'itemIsFeatured' => ['boolean'],
            'itemIsActive' => ['boolean'],
        ]);

        $categoryId = (int) $validated['itemCategoryId'];
        $categoryExists = $this->categoriesQuery()->whereKey($categoryId)->exists();
        if (!$categoryExists) {
            $this->addError('itemCategoryId', 'Selected category is not valid for your school.');
            return;
        }

        $mediaUrl = trim((string) ($validated['itemMediaUrl'] ?? ''));
        if ($this->itemImage) {
            try {
                $mediaUrl = $this->uploadToCloudinary($this->itemImage, $schoolId);
            } catch (\Throwable $e) {
                report($e);
                $this->addError('itemImage', 'Image upload failed. Check CLOUDINARY_URL and try again.');
                return;
            }
        }

        if ($mediaUrl === '') {
            $this->addError('itemMediaUrl', 'Provide an image URL or upload an image.');
            return;
        }

        $payload = [
            'title' => trim($validated['itemTitle']),
            'caption' => trim((string) $validated['itemCaption']) ?: null,
            'media_url' => $mediaUrl,
            'gallery_category_id' => $categoryId,
            'taken_on' => $validated['itemTakenOn'] ?: null,
            'sort_order' => (int) $validated['itemSortOrder'],
            'is_featured' => (bool) $validated['itemIsFeatured'],
            'is_active' => (bool) $validated['itemIsActive'],
        ];

        if ($this->itemId) {
            $item = $this->itemsQuery()->findOrFail($this->itemId);
            $item->update($payload);
            session()->flash('success', 'Gallery item updated.');
        } else {
            $payload['school_id'] = $schoolId;
            $payload['uploaded_by'] = auth()->id();
            GalleryItem::create($payload);
            session()->flash('success', 'Gallery item added.');
        }

        $this->resetItemForm();
        $this->activeTab = 'items';
    }

    public function editItem(int $itemId): void
    {
        $item = $this->itemsQuery()->findOrFail($itemId);

        $this->itemId = $item->id;
        $this->itemTitle = $item->title;
        $this->itemCaption = (string) ($item->caption ?? '');
        $this->itemMediaUrl = $item->media_url;
        $this->itemImage = null;
        $this->itemCategoryId = (string) $item->gallery_category_id;
        $this->itemTakenOn = $item->taken_on?->format('Y-m-d') ?? '';
        $this->itemSortOrder = (int) $item->sort_order;
        $this->itemIsFeatured = (bool) $item->is_featured;
        $this->itemIsActive = (bool) $item->is_active;
        $this->activeTab = 'items';
    }

    public function deleteItem(int $itemId): void
    {
        $item = $this->itemsQuery()->findOrFail($itemId);
        $item->delete();

        if ($this->itemId === $itemId) {
            $this->resetItemForm();
        }

        session()->flash('success', 'Gallery item deleted.');
    }

    public function resetItemForm(): void
    {
        $this->reset([
            'itemId',
            'itemTitle',
            'itemCaption',
            'itemMediaUrl',
            'itemImage',
            'itemCategoryId',
            'itemTakenOn',
            'itemSortOrder',
            'itemIsFeatured',
            'itemIsActive',
        ]);

        $this->itemSortOrder = 0;
        $this->itemIsFeatured = false;
        $this->itemIsActive = true;
        $this->resetValidation();
    }

    public function clearItemImage(): void
    {
        $this->itemImage = null;
    }

    public function optimizedImageUrl(?string $url, int $width = 960, int $height = 640): ?string
    {
        return GalleryItem::transformUrl(
            $url,
            'c_fill,g_auto,f_auto,q_auto,w_' . $width . ',h_' . $height
        );
    }

    protected function activeSchoolId(): ?int
    {
        $schoolId = auth()->user()?->school_id;
        if (!$schoolId) {
            return null;
        }

        return (int) $schoolId;
    }

    protected function categoriesQuery()
    {
        $schoolId = $this->activeSchoolId();

        return GalleryCategory::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when(!$schoolId, fn ($query) => $query->whereRaw('1 = 0'));
    }

    protected function itemsQuery()
    {
        $schoolId = $this->activeSchoolId();

        return GalleryItem::query()
            ->when($schoolId, fn ($query) => $query->where('school_id', $schoolId))
            ->when(!$schoolId, fn ($query) => $query->whereRaw('1 = 0'))
            ->with([
                'category:id,name,color',
                'uploader:id,name',
            ]);
    }

    protected function emptyPaginator(): LengthAwarePaginator
    {
        return new LengthAwarePaginator([], 0, $this->perPage, 1, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    protected function generateUniqueCategorySlug(string $baseSlug, int $schoolId, ?int $ignoreId = null): string
    {
        $slug = $baseSlug;
        $counter = 2;

        while (
            GalleryCategory::query()
                ->where('school_id', $schoolId)
                ->when($ignoreId, fn ($query) => $query->where('id', '!=', $ignoreId))
                ->where('slug', $slug)
                ->exists()
        ) {
            $slug = $baseSlug . '-' . $counter;
            $counter++;
        }

        return $slug;
    }

    protected function uploadToCloudinary(UploadedFile $file, int $schoolId): string
    {
        $credentials = $this->cloudinaryCredentials();
        $timestamp = time();
        $folder = trim((string) config('services.cloudinary.folder', 'elites/gallery'), '/')
            . '/school-' . $schoolId;

        $uploadParams = [
            'api_key' => $credentials['api_key'],
            'folder' => $folder,
            'timestamp' => $timestamp,
            'use_filename' => 1,
            'unique_filename' => 1,
        ];

        $paramsToSign = $uploadParams;
        unset($paramsToSign['api_key']);
        ksort($paramsToSign);
        $signatureBase = collect($paramsToSign)
            ->map(fn ($value, $key) => $key . '=' . $value)
            ->implode('&');
        $signature = sha1($signatureBase . $credentials['api_secret']);

        $response = Http::timeout(30)
            ->attach(
                'file',
                file_get_contents($file->getRealPath()),
                $file->getClientOriginalName()
            )
            ->post(
                'https://api.cloudinary.com/v1_1/' . $credentials['cloud_name'] . '/image/upload',
                $uploadParams + ['signature' => $signature]
            );

        if (!$response->successful()) {
            throw new RuntimeException('Cloudinary upload failed: ' . $response->body());
        }

        $secureUrl = data_get($response->json(), 'secure_url');
        if (!is_string($secureUrl) || $secureUrl === '') {
            throw new RuntimeException('Cloudinary did not return a secure URL.');
        }

        return $secureUrl;
    }

    protected function cloudinaryCredentials(): array
    {
        $url = trim((string) config('services.cloudinary.url'));
        if ($url === '') {
            throw new RuntimeException('Missing CLOUDINARY_URL configuration.');
        }

        $parts = parse_url($url);
        if (!$parts || !isset($parts['host'], $parts['user'], $parts['pass'])) {
            throw new RuntimeException('Invalid CLOUDINARY_URL format.');
        }

        return [
            'cloud_name' => (string) $parts['host'],
            'api_key' => urldecode((string) $parts['user']),
            'api_secret' => urldecode((string) $parts['pass']),
        ];
    }

    public function render()
    {
        $schoolId = $this->activeSchoolId();
        $school = $schoolId ? School::query()->select('id', 'name')->find($schoolId) : null;

        $categories = collect();
        $items = $this->emptyPaginator();
        $stats = [
            'categories' => 0,
            'items' => 0,
            'featured' => 0,
            'active' => 0,
        ];

        if ($schoolId) {
            $categories = $this->categoriesQuery()
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get(['id', 'name', 'slug', 'color', 'description', 'is_active', 'sort_order']);

            $itemsQuery = $this->itemsQuery()
                ->when($this->search !== '', function ($query) {
                    $search = '%' . trim($this->search) . '%';
                    $query->where(function ($inner) use ($search) {
                        $inner->where('title', 'like', $search)
                            ->orWhere('caption', 'like', $search)
                            ->orWhere('media_url', 'like', $search);
                    });
                })
                ->when($this->categoryFilter !== 'all', fn ($query) => $query->where('gallery_category_id', (int) $this->categoryFilter))
                ->latest();

            $items = $itemsQuery->paginate($this->perPage);

            $statsBase = $this->itemsQuery()->getQuery();
            $stats = [
                'categories' => $categories->count(),
                'items' => (clone $statsBase)->count(),
                'featured' => (clone $statsBase)->where('is_featured', true)->count(),
                'active' => (clone $statsBase)->where('is_active', true)->count(),
            ];
        }

        return view('livewire.gallery.manage-gallery', [
            'school' => $school,
            'categories' => $categories,
            'items' => $items,
            'stats' => $stats,
        ])->layout('layouts.dashboard', [
            'breadcrumbs' => [
                ['href' => route('dashboard'), 'text' => 'Dashboard'],
                ['href' => route('gallery.manage'), 'text' => 'Gallery Manager', 'active' => true],
            ],
        ])->title('Gallery Manager');
    }
}
