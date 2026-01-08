<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.subjects.partials.list-view')
    @elseif($mode === 'create')
        @include('livewire.subjects.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.subjects.partials.edit-form')
    @endif
</div>