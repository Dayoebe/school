<div class="card">
    <div class="card-header">
        <h4 class="card-title">Manage exam record</h4>
    </div>
    <div class="card-body">
        <x-display-validation-errors/>

        <x-loading-spinner wire:target="exam"/>
        <x-loading-spinner wire:target="class"/>
        <x-loading-spinner wire:target="subject"/>
        <x-loading-spinner wire:target="section"/>

        <form wire:submit.prevent="fetchExamRecords" class="md:grid gap-4 grid-cols-4 grid-rows-1 my-3 items-end">
            <div>
                <label for="exam-id" class="mb-2 block text-sm font-medium text-gray-700">Select exam</label>
                <select id="exam-id" name="exam_id" wire:model.live="exam" class="w-full rounded border border-gray-300 px-3 py-2">
                    <option value="">Select exam</option>
                    @foreach ($exams as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="class" class="mb-2 block text-sm font-medium text-gray-700">Select class</label>
                <select id="class" name="class" wire:model.live="class" class="w-full rounded border border-gray-300 px-3 py-2">
                    <option value="">Select class</option>
                    @foreach ($classes as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="subject" class="mb-2 block text-sm font-medium text-gray-700">Select subject</label>
                <select id="subject" name="subject" wire:model.live="subject" class="w-full rounded border border-gray-300 px-3 py-2">
                    <option value="">Select subject</option>
                    @foreach (($subjects ?? collect()) as $subjectOption)
                        <option value="{{$subjectOption->id}}">{{$subjectOption->name}}</option>
                    @endforeach
                </select>
            </div>

            <div>
                <label for="section" class="mb-2 block text-sm font-medium text-gray-700">Section</label>
                <select id="section" name="section" wire:model.live="section" class="w-full rounded border border-gray-300 px-3 py-2">
                    <option value="">Select section</option>
                    @foreach (($sections ?? collect()) as $item)
                        <option value="{{$item->id}}">{{$item->name}}</option>
                    @endforeach
                </select>
            </div>

            <button type="submit" class="btn btn-primary w-full">View records</button>
        </form>

        @if ($error)
            <div class="mb-4 rounded border border-amber-200 bg-amber-50 px-4 py-3 text-amber-900">
                {{$error}}
            </div>
        @endif

        <x-loading-spinner wire:target="fetchExamRecords"/>

        @if(isset($examSlots) && $examSlots && isset($students) && $students && $students->count() > 0)
            <div class="card" wire:loading.remove.delay wire:target="fetchExamRecords">
                <div class="card-header">
                    <h4 class="card-title">{{$examSelected->name}} exam records</h4>
                </div>
                <div class="card-body">
                    <div class="md:flex justify-between">
                        <p>Exam: {{$examSelected->name}}</p>
                        <p>Class: {{$classSelected->name}}</p>
                        @if ($sectionSelected)
                            <p>Section: {{$sectionSelected->name}}</p>
                        @endif
                        <p>Subject: {{$subjectSelected->name}}</p>
                    </div>

                    <div class="my-3">
                        <label for="search" class="mb-2 block text-sm font-medium text-gray-700">Search for student</label>
                        <input id="search" name="search" wire:model.live.debounce.500ms="search" type="text" class="w-full rounded border border-gray-300 px-3 py-2" placeholder="Search for student">
                    </div>

                    @foreach ($students as $student)
                        <div wire:key="student-record-{{$student->id}}">
                            <div class="relative bottom-20" id="student-{{$student->id}}"></div>
                            <form action="{{route('exam-records.store')}}#student-{{$student->id}}" class="md:grid grid-rows-1 grid-flow-col-dense gap-4 overflow-scroll beautify-scrollbar border-b items-center my-5 p-3" method="POST">
                                <p class="md:w-40 font-bold">{{ $students->perPage() * ($students->currentPage() - 1) + $loop->iteration }}. {{$student->name}}</p>
                                @foreach ($examSlots as $examSlot)
                                    @php
                                        $examRecord = $examRecords->where('user_id', $student->id)->where('subject_id', $subjectSelected->id)->where('exam_slot_id', $examSlot->id)->first();
                                        $studentMarks = $examRecord ? $examRecord['student_marks'] : '0';
                                    @endphp
                                    @can('create exam record')
                                        <input type="hidden" name="exam_records[{{$loop->index}}][exam_slot_id]" value="{{$examSlot->id}}">
                                        <div class="min-w-[10rem]">
                                            <label for="student-{{$student->id}}-slot-{{$examSlot->id}}" class="mb-2 block whitespace-nowrap text-sm font-medium text-gray-700">{{$examSlot->name}} ({{$examSlot->total_marks}})</label>
                                            <input id="student-{{$student->id}}-slot-{{$examSlot->id}}" name="exam_records[{{$loop->index}}][student_marks]" type="number" placeholder="Enter marks" value="{{$studentMarks}}" min="0" max="{{$examSlot->total_marks}}" class="w-full rounded border border-gray-300 px-3 py-2">
                                        </div>
                                    @else
                                        <p>{{$studentMarks}}</p>
                                    @endcan
                                @endforeach
                                <input type="hidden" name="subject_id" value="{{$subjectSelected->id}}">
                                <input type="hidden" name="user_id" value="{{$student->id}}">
                                <input type="hidden" name="section_id" value="{{$sectionSelected->id}}">
                                @csrf
                                @can('create exam record')
                                    <button type="submit" class="btn btn-primary w-full min-w-[12rem] place-self-end">Submit</button>
                                @endcan
                            </form>
                        </div>
                    @endforeach

                    {{$students->links('components.datatable-pagination-links-view')}}
                </div>
            </div>
        @elseif(isset($examSlots) && $examSlots && isset($students) && $students && $students->count() === 0)
            <div class="rounded border border-dashed border-gray-300 bg-gray-50 px-4 py-6 text-gray-600">
                No students were found for the selected class and section.
            </div>
        @endif
    </div>
</div>
