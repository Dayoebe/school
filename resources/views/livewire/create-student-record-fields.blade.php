<div class="md:grid grid-cols-12 gap-2">
    <h4 class="text-bold text-xl md:text-3xl font-bold col-span-12 text-center my-2">Class information</h4>

    <!-- Class (Required) -->
    <x-select id="class-id" name="my_class_id" label="Choose a class *" group-class="col-span-6" wire:model.live="myClass">
        <option value="">Select</option>
        @foreach ($myClasses as $item)
            <option value="{{ $item['id'] }}" @selected(old('my_class_id', $myClass) == $item['id'])>
                {{ $item['name'] }}
            </option>
        @endforeach
    </x-select>

    <!-- Section (Optional) -->
    <x-select id="section-id" name="section_id" label="Choose a section" group-class="col-span-6" wire:model.live="section">
        <option value="">Select (For Senior Class)</option> <!-- Updated label -->
        @foreach ($sections as $item)
            <option value="{{ $item['id'] }}" @selected(old('section_id', $section) == $item['id'])>
                {{ $item['name'] }}
            </option>
        @endforeach
    </x-select>

    <!-- Admission Number (Optional) -->
    <x-input id="admission-number" name="admission_number" label="Admission number" :value="$admissionNumber" group-class="col-span-6" />

    <!-- Admission Date (Optional) -->
    <x-input type="date" name="admission_date" id="admission_date" label="Date of admission" :value="$admissionDate" autocomplete="on" group-class="col-span-6" />
</div>