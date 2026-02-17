<?php


namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class SetDefaultPassword extends Command
{
    protected $signature = 'password:default';
    protected $description = 'Set default password for all users';

    public function handle()
    {
        $count = User::count();
        $this->info("Updating {$count} users to default password...");
        
        User::chunk(200, function ($users) {
            foreach ($users as $user) {
                $user->update(['password' => Hash::make('12345678')]);
            }
        });
        
        $this->info('All users passwords set to 12345678');
        return 0;
    }
}