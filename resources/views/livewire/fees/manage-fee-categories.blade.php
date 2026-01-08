<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.fees.partials.fee-categories.list-view')
    @elseif($mode === 'create')
        @include('livewire.fees.partials.fee-categories.create-form')
    @elseif($mode === 'edit')
        @include('livewire.fees.partials.fee-categories.edit-form')
    @endif
</div>