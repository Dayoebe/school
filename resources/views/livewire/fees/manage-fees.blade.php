<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.fees.partials.fees.list-view')
    @elseif($mode === 'create')
        @include('livewire.fees.partials.fees.create-form')
    @elseif($mode === 'edit')
        @include('livewire.fees.partials.fees.edit-form')
    @endif
</div>