<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.admins.partials.list-view', ['admins' => $admins])
    @elseif($mode === 'create')
        @include('livewire.admins.partials.create-form')
    @elseif($mode === 'edit')
        @include('livewire.admins.partials.edit-form')
    @endif
</div>