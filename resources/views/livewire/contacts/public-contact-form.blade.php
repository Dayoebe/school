<div class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-6">
    @if (session()->has('error'))
        <div class="mb-4 rounded-xl border border-red-200 bg-red-50 px-4 py-3 text-sm font-semibold text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if (!$contactTableReady)
        <div class="rounded-2xl border border-amber-200 bg-amber-50 p-6">
            <h3 class="text-lg font-black text-amber-900">Contact Setup Required</h3>
            <p class="mt-2 text-sm text-amber-800">
                Contact messages table is missing. Run migrations, then refresh this page.
            </p>
        </div>
    @elseif ($submitted)
        <div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-6 text-center">
            <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-emerald-600 text-white">
                <i class="fas fa-check text-xl"></i>
            </div>
            <h3 class="mt-4 text-xl font-black text-emerald-800">Message Sent</h3>
            <p class="mt-2 text-sm text-emerald-700">Thanks for contacting us. The team will respond shortly.</p>
            <button wire:click="sendAnother" type="button"
                class="mt-5 inline-flex items-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700">
                <i class="fas fa-rotate-right"></i>
                <span>Send Another Message</span>
            </button>
        </div>
    @else
        <form wire:submit.prevent="submit" class="space-y-5">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2">
                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">School <span class="text-red-600">*</span></label>
                    <select wire:model="school_id" class="w-full rounded-xl border border-slate-300 bg-white px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                        <option value="">Select School</option>
                        @foreach($schools as $school)
                            <option value="{{ $school['id'] }}">{{ $school['name'] }}</option>
                        @endforeach
                    </select>
                    @error('school_id') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Full Name <span class="text-red-600">*</span></label>
                    <input wire:model="full_name" type="text" placeholder="Enter your full name"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    @error('full_name') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Email <span class="text-red-600">*</span></label>
                    <input wire:model="email" type="email" placeholder="Enter your email"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    @error('email') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div>
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Phone</label>
                    <input wire:model="phone" type="text" placeholder="Optional"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    @error('phone') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Subject <span class="text-red-600">*</span></label>
                    <input wire:model="subject" type="text" placeholder="What do you need help with?"
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200">
                    @error('subject') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>

                <div class="md:col-span-2">
                    <label class="mb-1 block text-xs font-bold uppercase tracking-wider text-slate-600">Message <span class="text-red-600">*</span></label>
                    <textarea wire:model="message" rows="5" placeholder="Write your message..."
                        class="w-full rounded-xl border border-slate-300 px-3 py-3 text-sm focus:border-red-500 focus:outline-none focus:ring-2 focus:ring-red-200"></textarea>
                    @error('message') <p class="mt-1 text-xs text-red-600">{{ $message }}</p> @enderror
                </div>
            </div>

            <div class="border-t border-slate-200 pt-4">
                <button type="submit"
                    class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-red-600 px-5 py-3 text-sm font-bold text-white transition hover:bg-red-700 disabled:cursor-not-allowed disabled:bg-slate-400"
                    wire:loading.attr="disabled">
                    <i class="fas fa-paper-plane" wire:loading.remove wire:target="submit"></i>
                    <i class="fas fa-spinner fa-spin" wire:loading wire:target="submit"></i>
                    <span wire:loading.remove wire:target="submit">Send Message</span>
                    <span wire:loading wire:target="submit">Sending...</span>
                </button>
            </div>
        </form>
    @endif
</div>
