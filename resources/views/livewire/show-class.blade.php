<div class="card">
    <div class="card-header">
        <h2 class="card-title">{{$class->name}}</h2>
    </div>
    <div class="card-body">
        <h3 class="text-center text-lg md:text-3xl font-bold my-5">Sections in class</h3>
        <livewire:datatable 
            :model="App\Models\MyClass::class" 
            uniqueId="section-list-table" 
            :filters="[['name' => 'find' , 'arguments' => [$class->id]], ['name' => 'sections']]" 
            :columns="[
                ['property' => 'name'] , 
                ['type' => 'dropdown', 'name' => 'actions','links' => [
                    ['href' => 'sections.edit', 'text' => 'Settings', 'icon' => 'fas fa-cog'],
                    ['href' => 'sections.show', 'text' => 'View', 'icon' => 'fas fa-eye'],
                ]],
                ['type' => 'delete', 'name' => 'Delete', 'action' => 'sections.destroy']
            ]"
        />
        
        <div class="flex justify-between items-center my-5">
            <h3 class="text-center text-lg md:text-3xl font-bold">Students in class</h3>
            @can('update', $class)
                <form action="{{ route('classes.assign-subjects', $class) }}" method="POST">
                    @csrf
                    <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-blue-600 to-indigo-700 text-white font-medium rounded-lg shadow hover:from-blue-700 hover:to-indigo-800 transition-all duration-300 transform hover:scale-105">
                        <i class="fas fa-link mr-2"></i> Assign Subjects to Students
                    </button>
                </form>
            @endcan
        </div>
        
        <livewire:datatable 
            :model="App\Models\User::class" 
            uniqueId="students-list-table" 
            :filters="[
                ['name' => 'where' , 'arguments' => ['school_id' , auth()->user()->school_id]], 
                ['name' => 'whereRelation' , 'arguments' => ['studentRecord','my_class_id' , $class->id]], 
                ['name' => 'with' , 'arguments' => ['studentRecord' ,'studentRecord.section', 'studentRecord.studentSubjects']]
            ]" 
            :columns="[
                ['property' => 'name'] , 
                ['property' => 'email'] , 
                ['property' => 'name', 'name' => 'section name' ,'relation' => 'studentRecord.section'] , 
                [
                    'property' => 'studentSubjects_count',
                    'name' => 'Subjects Count',
                    'relation' => 'studentRecord'
                ],
                ['type' => 'dropdown', 'name' => 'actions','links' => [
                    ['href' => 'students.edit', 'text' => 'Settings', 'icon' => 'fas fa-cog'],
                    ['href' => 'students.show', 'text' => 'View', 'icon' => 'fas fa-eye'],
                ]],
                ['type' => 'delete', 'name' => 'Delete', 'action' => 'students.destroy']
            ]"
        />
        
        <h3 class="text-center text-lg md:text-3xl font-bold my-5">Subjects in class</h3>
        <livewire:datatable 
            :model="App\Models\Subject::class" 
            uniqueId="subjects-list-table" 
            :filters="[
                ['name' => 'where' , 'arguments' => ['my_class_id' , $class->id]],
                ['name' => 'with' , 'arguments' => ['teachers']]
            ]" 
            :columns="[
                ['property' => 'name'] , 
                ['method' => 'count' , 'name' => 'No of teachers', 'relation' => 'teachers'] , 
                ['type' => 'dropdown', 'name' => 'actions','links' => [
                    ['href' => 'subjects.edit', 'text' => 'Settings', 'icon' => 'fas fa-cog'],
                ]],
                ['type' => 'delete', 'name' => 'Delete', 'action' => 'subjects.destroy']
            ]"
        />
    </div>
</div>