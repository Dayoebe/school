<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h3 class="card-title mb-0">Manage exam {{$exam->id}}</h3>
        <div class="d-flex flex-wrap gap-2">
            @can('read exam slot')
                <a href="{{ route('exam-slots.index', $exam) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-calendar-alt"></i>
                    Manage slots
                </a>
            @endcan
            @can('read exam paper')
                <a href="{{ route('exam-papers.index', $exam) }}" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-file-lines"></i>
                    Uploaded exams
                </a>
            @endcan
            @can('create exam paper')
                <a href="{{ route('exam-papers.create', $exam) }}" class="btn btn-primary btn-sm">
                    <i class="fas fa-upload"></i>
                    Upload exam
                </a>
            @endcan
        </div>
    </div>
    <div class="card-body">
        <form action="{{route('exams.update',$exam)}}" autocomplete="off" method="POST" class="md:w-1/2">
            <x-display-validation-errors/>
            <x-input id="name" name="name" label="Exam Name" placeholder="Enter term name"  value="{{$exam->name}}"/>
            <x-textarea id="description" name="description" label="Description" placeholder="Enter description" >{{$exam->description}}</x-adminlte-textarea>
            <x-input id="start_date" type="date" name="start_date" label="Start date" required  value="{{$exam->start_date}}"/>
            <x-input type="date" id="stop_date" name="stop_date" label="Stop date" required value="{{$exam->stop_date}}"/>
            <x-select id="semster" name="semester_id" label="Select term"  wire:loading.attr="disabled" wire:target="semester">
                @foreach ($semesters as $semester)
                    <option value="{{$semester['id']}}" @selected($semester->id == $exam->semester_id )> {{$semester['name']}}</option>
                @endforeach
            </x-select>
            @csrf
            @method('PUT')
                <x-button label="Edit" theme="primary" icon="fas fa-pen" type="submit" class="md:w-1/2 w-full"/>
        </form>
    </div>
</div>
