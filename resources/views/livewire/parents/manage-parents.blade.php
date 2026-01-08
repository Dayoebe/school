<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.parents.partials.list-view')
    @elseif($mode === 'create')
        @include('livewire.parents.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.parents.partials.edit-form')
    @endif
</div>