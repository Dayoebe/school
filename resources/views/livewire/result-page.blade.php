<div> {{-- 👈 Wrap everything in one root div --}}
    @if($page === 'index')
        @include('pages.result.index-content')
    @elseif($page === 'upload')
        @include('pages.result.upload-content')
    @elseif($page === 'view')
        @include('pages.result.view-content')
    @endif
</div>
