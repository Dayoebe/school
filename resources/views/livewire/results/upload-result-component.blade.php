<div class="card">
    <div class="card-header">
        <h4 class="card-title">Upload Student Results</h4>
    </div>

    <div class="card-body" x-data="{
        selectedClass: '', 
        selectedSection: '', 
        selectedSubject: '', 
        scores: @entangle('scores')
    }">
        <x-display-validation-errors/>

        <!-- Input Fields -->
        <div class="md:grid grid-cols-3 gap-4 mb-6">
            <div>
                <label for="class_id" class="block">Class ID</label>
                <input type="text" id="class_id" x-model="selectedClass" class="border p-2 w-full" placeholder="Enter Class ID">
            </div>
            <div>
                <label for="section_id" class="block">Section ID</label>
                <input type="text" id="section_id" x-model="selectedSection" class="border p-2 w-full" placeholder="Enter Section ID">
            </div>
            <div>
                <label for="subject_id" class="block">Subject</label>
                <select id="subject_id" x-model="selectedSubject" class="border p-2 w-full">
                    <option value="">Select Subject</option>
                    @foreach($subjects as $subject)
                        <option value="{{ $subject->id }}">{{ $subject->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        @if($students)
            <!-- Table to Display Students' Scores -->
            <form wire:submit.prevent="save">
                <div class="table-responsive">
                    <div class="overflow-scroll beautify-scrollbar">
                        <table class="w-full border-collapse border border-gray-300 mb-4">
                            <thead class="bg-gray-200 text-left">
                                <tr>
                                    <th class="p-4 border">Student Name</th>
                                    <th class="p-4 border">Test Score</th>
                                    <th class="p-4 border">Exam Score</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($students as $student)
                                    <tr>
                                        <td class="p-4 border">{{ $student->user->name ?? 'N/A' }}</td>
                                        <td class="p-4 border">
                                            <input type="number" min="0" 
                                                   wire:model.lazy="scores.{{ $student->id }}.test_score" 
                                                   class="border p-2 w-full" />
                                        </td>
                                        <td class="p-4 border">
                                            <input type="number" min="0" 
                                                   wire:model.lazy="scores.{{ $student->id }}.exam_score" 
                                                   class="border p-2 w-full" />
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Submit Button -->
                <x-button label="Submit Results" type="submit" class="w-full mt-2"/>
            </form>
        @endif

        @if(session()->has('message'))
            <div class="mt-4 text-green-600 font-semibold">
                {{ session('message') }}
            </div>
        @endif

        <x-loading-spinner/>
    </div>
</div>
