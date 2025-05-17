<div>
    @if($mode === 'index')
        @include('pages.result.index-content')
    @elseif($mode === 'upload')
        @include('pages.result.upload-content')
    @elseif($mode === 'view')
        @include('pages.result.view-content')
    @endif
</div>
