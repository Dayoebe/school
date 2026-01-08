<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.schools.partials.list-view', ['schools' => $schools, 'allSchools' => $allSchools])
    @elseif($mode === 'create')
        @include('livewire.schools.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.schools.partials.edit-form')
    @endif
</div>