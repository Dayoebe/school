<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.teachers.partials.list-view')
    @elseif($mode === 'create')
        @include('livewire.teachers.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.teachers.partials.edit-form')
    @endif
</div>