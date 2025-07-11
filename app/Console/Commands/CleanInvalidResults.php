<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Result;  
class CleanInvalidResults extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:clean-invalid-results';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    // In app/Console/Commands/CleanInvalidResults.php

public function handle()
{
    $invalidResults = Result::whereDoesntHave('student', function($q) {
        $q->whereHas('studentSubjects', function($q) {
            $q->whereColumn('subjects.id', 'results.subject_id');
        });
    })->get();

    $count = $invalidResults->count();
    
    $invalidResults->each->delete();
    
    $this->info("Deleted {$count} invalid results where subjects weren't assigned to students");
}
}
