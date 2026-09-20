<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class PromoteUserToAdmin extends Command
{
    protected $signature = 'admin:promote {email}';
    protected $description = 'Grants the admin role to a user by email';
    // to run  this command, example: php artisan admin:promote you@example.com
    public function handle(): int
    {
        $user = User::where('email', $this->argument('email'))->first();

        if (! $user) {
            $this->error("No user found with email {$this->argument('email')}");
            return self::FAILURE;
        }

        // guard_name is explicitly 'sanctum' — this API authenticates
        // purely via the sanctum guard, never 'web', and spatie's role
        // checks are guard-aware. Leaving this to the package default
        // is a known Sanctum + laravel-permission gotcha.
        Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'sanctum']);
        $user->assignRole('admin');

        $this->info("{$user->email} is now an admin.");
        return self::SUCCESS;
    }
}
