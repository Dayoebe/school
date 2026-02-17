<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.timetable.partials.list-view')
    @elseif($mode === 'create')
        @include('livewire.timetable.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.timetable.partials.edit-form')
    @elseif($mode === 'build')
        @include('livewire.timetable.partials.build-timetable')
    @elseif($mode === 'custom-items')
        @include('livewire.timetable.partials.custom-items')
    @endif
</div>