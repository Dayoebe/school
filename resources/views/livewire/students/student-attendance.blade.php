<div>
    <h3 class="text-xl font-semibold mb-4 text-gray-700">My Attendance</h3>

    @if($loading)
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-green-500"></i>
            <p class="mt-2 text-gray-600">Loading attendance records...</p>
        </div>
    @else
        @if(count($attendanceRecords) > 0)
            <div class="overflow-x-auto bg-white rounded-lg shadow border border-gray-200">
                <table class="min-w-full divide-y divide-gray-200 text-sm">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Date</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Status</th>
                            <th class="px-4 py-2 text-left font-medium text-gray-500 uppercase tracking-wider">Reason</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        @foreach($attendanceRecords as $record)
                            <tr class="hover:bg-gray-50">
                                <td class="px-4 py-2">{{ $record['date'] }}</td>
                                <td class="px-4 py-2">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full
                                        @if($record['status'] === 'Present') bg-green-100 text-green-800
                                        @elseif($record['status'] === 'Absent') bg-red-100 text-red-800
                                        @elseif($record['status'] === 'Late') bg-yellow-100 text-yellow-800
                                        @else bg-gray-100 text-gray-800 @endif">
                                        {{ $record['status'] }}
                                    </span>
                                </td>
                                <td class="px-4 py-2">{{ $record['reason'] ?? 'N/A' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @else
            <div class="text-center py-12 bg-gray-50 rounded-lg">
                <i class="fas fa-calendar-times text-4xl text-gray-400 mb-3"></i>
                <h4 class="text-gray-700">No Attendance Records Found</h4>
                <p class="text-sm text-gray-500 mt-1">There are no attendance records available for the current academic period.</p>
            </div>
        @endif
    @endif
</div>
