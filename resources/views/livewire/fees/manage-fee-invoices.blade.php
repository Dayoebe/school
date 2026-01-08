<div class="space-y-6">
    @if($mode === 'list')
        @include('livewire.fees.partials.invoices.list-view')
    @elseif($mode === 'create')
        @include('livewire.fees.partials.invoices.create-form')
    @elseif($mode === 'edit')
        @include('livewire.fees.partials.invoices.edit-form')
    @endif
</div>