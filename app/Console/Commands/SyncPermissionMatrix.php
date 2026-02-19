<?php

namespace App\Console\Commands;

use App\Support\PermissionMatrix;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class SyncPermissionMatrix extends Command
{
    protected $signature = 'permissions:sync-matrix {--guard=web}';

    protected $description = 'Sync Spatie roles and permissions from the application permission matrix';

    public function handle(): int
    {
        $guard = (string) $this->option('guard');
        $permissions = PermissionMatrix::permissions();
        $roles = PermissionMatrix::roles();

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        foreach ($permissions as $permissionName) {
            Permission::firstOrCreate([
                'name' => $permissionName,
                'guard_name' => $guard,
            ]);
        }

        $this->info('Permissions ensured: ' . count($permissions));

        foreach ($roles as $roleName => $rolePermissions) {
            $role = Role::firstOrCreate([
                'name' => $roleName,
                'guard_name' => $guard,
            ]);

            $permissionList = $rolePermissions === ['*']
                ? $permissions
                : $rolePermissions;

            $role->syncPermissions($permissionList);
            $this->line("Synced role: {$roleName} (" . count($permissionList) . ' permissions)');
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
        $this->info('Role/permission matrix sync complete.');

        return self::SUCCESS;
    }
}

