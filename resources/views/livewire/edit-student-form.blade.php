<div class="card">
    <div class="card-header">
        <h3 class="card-title">Edit student form</h3>
    </div>
    <div class="card-body">
        <form action="{{ route('students.update', $student->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Editable user fields --}}
            @livewire('edit-user-fields', ['role' => 'Student', 'user' => $student])

            {{-- Class and Section selection (with prefilled data) --}}


            <livewire:create-student-record-fields :initial-my-class-id="$student->studentRecord->my_class_id" :initial-section-id="$student->studentRecord->section_id" :admission-number="$student->studentRecord->admission_number"
                :admission-date="$student->studentRecord->admission_date" />


            {{-- Submit button --}}
            <div class="col-12 my-4">
                <x-button label="Update Student" theme="primary" icon="fas fa-save" type="submit"
                    class="w-full md:w-1/3 mx-auto block" />
            </div>
        </form>
    </div>
</div>
