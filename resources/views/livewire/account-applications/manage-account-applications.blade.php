<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.account-applications.partials.list-view')
    @elseif($mode === 'view')
        @include('livewire.account-applications.partials.view-application')
    @elseif($mode === 'change-status')
        @include('livewire.account-applications.partials.change-status')
    @endif
</div>