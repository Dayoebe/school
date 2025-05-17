@if ($mode === 'upload')
    <div class="bg-white p-6 rounded-lg shadow-md">
        <h2 class="text-xl font-bold mb-4">Upload Results for {{ $studentRecord->student->user->name }}</h2>

        <form wire:submit.prevent="saveResults">
            <table class="w-full table-auto">
                <thead>
                    <tr class="bg-gray-100">
                        <th class="p-2 border">Subject</th>
                        <th class="p-2 border">Test</th>
                        <th class="p-2 border">Exam</th>
                        <th class="p-2 border">Comment</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($subjects as $subject)
                        <tr>
                            <td class="p-2 border">{{ $subject->name }}</td>
                            <td class="p-2 border">
                                <input type="number" wire:model.defer="results.{{ $subject->id }}.test_score" class="w-full p-1 border rounded">
                            </td>
                            <td class="p-2 border">
                                <input type="number" wire:model.defer="results.{{ $subject->id }}.exam_score" class="w-full p-1 border rounded">
                            </td>
                            <td class="p-2 border">
                                <textarea wire:model.defer="results.{{ $subject->id }}.comment" class="w-full border rounded p-1"></textarea>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>

            <div class="mt-4 text-right">
                <button type="submit" class="bg-blue-600 text-white px-6 py-2 rounded hover:bg-blue-700">Save Results</button>
                <button type="button" wire:click="$set('mode', 'index')" class="ml-2 px-4 py-2 bg-gray-400 text-white rounded hover:bg-gray-500">Back</button>
            </div>
        </form>
    </div>

    @php return; @endphp
@endif
