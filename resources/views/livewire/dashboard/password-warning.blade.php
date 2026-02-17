<!-- resources/views/livewire/password-warning.blade.php -->
@if($show)
<div class="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-4">
    <div class="flex justify-between">
        <div class="flex items-center">
            <span class="text-yellow-700">
                You're using the default password. Please change it immediately.
            </span>
        </div>
        <button wire:click="dismiss" class="text-yellow-700 hover:text-yellow-900">
            &times;
        </button>
    </div>
    <div class="mt-2">
        <a href="{{ route('password.change') }}" class="text-yellow-700 underline">
            Change Password Now
        </a>
    </div>
</div>
@endif