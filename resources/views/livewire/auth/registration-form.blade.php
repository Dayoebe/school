@isset($roles)
    <form action="{{route('register')}}" method="POST" enctype="multipart/form-data" class="w-full space-y-3">
        <div class="w-full">
            <x-select id="role" name="role" label="Register as" class="capitalize">    
                    @foreach ($roles as $item)
                        <option value="{{$item['name']}}">{{$item['name']}}</option>
                    @endforeach
            </x-select>
            <x-select id="school" name="school" label="School" class="text-capitalize">    
                    @foreach ($schools as $item)
                        <option value="{{$item['id']}}" >{{$item['name']}} - {{$item['address']}}</option>
                    @endforeach
            </x-select>
            <livewire:create-user-fields/>
            @csrf
            <button type="submit" class="w-full rounded-lg bg-blue-600 py-3 text-white font-semibold hover:bg-blue-700 transition-colors">
                Register
            </button>
        </div>
    </form>
@else
   <p>Couldn't create user, Roles not found.</p> 
@endisset
