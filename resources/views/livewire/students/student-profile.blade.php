<div>
    <h3 class="text-xl font-semibold mb-4 text-gray-700">My Profile</h3>

    @if($loading)
        <div class="text-center py-12">
            <i class="fas fa-spinner fa-spin text-3xl text-orange-500"></i>
            <p class="mt-2 text-gray-600">Loading profile data...</p>
        </div>
    @else
        <div class="bg-white rounded-lg shadow p-6 grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="md:col-span-2 flex flex-col items-center mb-6">
                <img src="{{ $profileData['profile_photo_url'] }}" alt="{{ $profileData['name'] }}" class="w-32 h-32 rounded-full object-cover border-4 border-blue-200 shadow-md">
                <h4 class="text-2xl font-bold text-gray-900 mt-4">{{ $profileData['name'] }}</h4>
                <p class="text-gray-600 text-sm">{{ $profileData['email'] }}</p>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Admission Number</p>
                    <p class="text-gray-800">{{ $profileData['admission_number'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Class</p>
                    <p class="text-gray-800">{{ $profileData['class'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Section</p>
                    <p class="text-gray-800">{{ $profileData['section'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Gender</p>
                    <p class="text-gray-800">{{ $profileData['gender'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Birthday</p>
                    <p class="text-gray-800">{{ $profileData['birthday'] }}</p>
                </div>
            </div>

            <div class="space-y-4">
                <div>
                    <p class="text-sm font-medium text-gray-500">Phone</p>
                    <p class="text-gray-800">{{ $profileData['phone'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Address</p>
                    <p class="text-gray-800">{{ $profileData['address'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Blood Group</p>
                    <p class="text-gray-800">{{ $profileData['blood_group'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Religion</p>
                    <p class="text-gray-800">{{ $profileData['religion'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Nationality</p>
                    <p class="text-gray-800">{{ $profileData['nationality'] }}</p>
                </div>
                <div>
                    <p class="text-sm font-medium text-gray-500">Admission Date</p>
                    <p class="text-gray-800">{{ $profileData['admission_date'] }}</p>
                </div>
            </div>
        </div>
    @endif
</div>
