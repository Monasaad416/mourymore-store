<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

#[Signature('app:setup-filament')]
#[Description('Fresh migrate, generate Shield permissions, and seed the admin user')]
class SetupFilament extends Command
{
    public function handle(): int
    {
        $defaultGuard = config('auth.defaults.guard', 'web');

        $this->call('migrate:fresh', ['--force' => true]);

        Role::firstOrCreate(['name' => 'admin', 'guard_name' => $defaultGuard]);
        Role::firstOrCreate(['name' => 'customer', 'guard_name' => $defaultGuard]);

        $this->call('shield:generate', [
            '--all' => true,
            '--panel' => 'admin',
            '--option' => 'policies_and_permissions',
            '--no-interaction' => true,
        ]);

        $this->call('permission:cache-reset');

        $adminRole = Role::findByName('admin', $defaultGuard);
        $adminRole->syncPermissions(
            Permission::query()->where('guard_name', $defaultGuard)->get(),
        );

        $user = User::query()->updateOrCreate(
            ['email' => 'admin@gmail.com'],
            [
                'name' => 'Admin',
                'password' => '123456',
                'email_verified_at' => now(),
            ],
        );

        $user->syncRoles(['admin']);

        $this->call('permission:cache-reset');

        $this->info('Setup completed successfully.');
        $this->line('Admin: admin@gmail.com / 123456');

        return self::SUCCESS;
    }
}
