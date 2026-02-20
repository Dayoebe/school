<?php

namespace App\Console\Commands;

use App\Livewire\Layouts\Menu;
use App\Models\School;
use App\Models\User;
use App\Support\PermissionMatrix;
use Illuminate\Console\Command;
use Illuminate\Routing\Route as IlluminateRoute;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;
use Throwable;

class AuditRoleAccess extends Command
{
    protected $signature = 'permissions:audit-access
        {--guard=web : Auth guard used for role checks}
        {--role=* : Limit the audit to one or more role names}
        {--sync : Sync role/permission matrix before running audit}
        {--keep-users : Keep temporary audit users for manual inspection}';

    protected $description = 'Audit menu visibility and route access for each role against PermissionMatrix';

    public function handle(): int
    {
        $guard = (string) $this->option('guard');

        if ($this->option('sync')) {
            $this->call('permissions:sync-matrix', ['--guard' => $guard]);
        }

        $roles = PermissionMatrix::roles();
        $requestedRoles = array_values(array_filter((array) $this->option('role')));
        $roleNames = $requestedRoles === []
            ? array_keys($roles)
            : array_values(array_filter(array_keys($roles), fn (string $role): bool => in_array($role, $requestedRoles, true)));

        if ($requestedRoles !== [] && $roleNames === []) {
            $this->error('None of the requested roles exist in PermissionMatrix.');
            return self::FAILURE;
        }

        $unknownRoles = array_values(array_diff($requestedRoles, $roleNames));
        foreach ($unknownRoles as $unknownRole) {
            $this->warn("Skipping unknown role in PermissionMatrix: {$unknownRole}");
        }

        $auditRoutes = $this->menuRouteNames();
        if ($auditRoutes === []) {
            $this->error('No menu routes found for auditing.');
            return self::FAILURE;
        }

        try {
            $schoolId = School::query()->value('id');
        } catch (Throwable $e) {
            report($e);
            $this->error('Database connection failed. Start MySQL and run the audit again.');
            return self::FAILURE;
        }
        $tempUsers = [];
        $originalUser = Auth::guard($guard)->user();
        $totalMismatches = 0;

        app(PermissionRegistrar::class)->forgetCachedPermissions();

        try {
            foreach ($roleNames as $roleName) {
                $matrixPermissions = PermissionMatrix::allPermissionsForRole($roleName);
                $dbRole = Role::query()
                    ->where('name', $roleName)
                    ->where('guard_name', $guard)
                    ->first();

                if (!$dbRole) {
                    $this->warn("Role not found in DB for guard [{$guard}]: {$roleName}. Run permissions:sync-matrix.");
                    $totalMismatches++;
                    continue;
                }

                $user = $this->createAuditUser($roleName, $schoolId);
                $tempUsers[] = $user;

                Auth::shouldUse($guard);
                Auth::guard($guard)->setUser($user);

                $menuComponent = app(Menu::class);
                $menuComponent->mount();
                $entries = $this->extractMenuEntries($menuComponent, $menuComponent->menu);

                $roleMismatches = [];

                foreach ($entries as $entry) {
                    $routeName = $entry['route'];
                    $routeAudit = $this->auditRouteAccessForUser($user, $routeName);
                    $hasVisibleTwin = $this->hasVisibleEntryForRoute($entries, $routeName);

                    if ($entry['visible'] && !$routeAudit['allowed']) {
                        $roleMismatches[] = "VISIBLE but denied: [{$routeName}] {$routeAudit['reason']}";
                        continue;
                    }

                    if (
                        !$entry['visible']
                        && $routeAudit['allowed']
                        && $this->entryHasAccessConstraint($entry)
                        && !$hasVisibleTwin
                        && !$entry['has_params']
                    ) {
                        $roleMismatches[] = "HIDDEN but allowed: [{$routeName}] menu and route rules are inconsistent";
                    }

                    if ($routeAudit['permission_checks'] !== []) {
                        $expectedByMatrix = $this->passesPermissionChecksByNames(
                            $routeAudit['permission_checks'],
                            $matrixPermissions
                        );

                        if ($expectedByMatrix !== $routeAudit['permission_allowed']) {
                            $expectedText = $expectedByMatrix ? 'allow' : 'deny';
                            $actualText = $routeAudit['permission_allowed'] ? 'allow' : 'deny';
                            $roleMismatches[] = "Matrix says {$expectedText}, runtime says {$actualText}: [{$routeName}]";
                        }
                    }
                }

                $this->line('');
                if ($roleMismatches === []) {
                    $this->info("Role [{$roleName}]: OK (" . count($entries) . ' menu routes audited)');
                    continue;
                }

                $this->error("Role [{$roleName}] mismatches: " . count($roleMismatches));
                foreach ($roleMismatches as $message) {
                    $this->line("  - {$message}");
                }

                $totalMismatches += count($roleMismatches);
            }
        } finally {
            $guardInstance = Auth::guard($guard);
            if ($originalUser) {
                $guardInstance->setUser($originalUser);
            } elseif (method_exists($guardInstance, 'logout')) {
                $guardInstance->logout();
            }

            if (!$this->option('keep-users')) {
                foreach ($tempUsers as $tempUser) {
                    try {
                        $tempUser->syncRoles([]);
                        $tempUser->forceDelete();
                    } catch (Throwable $e) {
                        report($e);
                    }
                }
            }
        }

        $this->line('');
        if ($totalMismatches > 0) {
            $this->error("Access audit finished with {$totalMismatches} mismatch(es).");
            return self::FAILURE;
        }

        $this->info('Access audit passed for all requested roles.');
        return self::SUCCESS;
    }

    protected function menuRouteNames(): array
    {
        $menu = app(Menu::class);
        $menu->mount();

        $entries = $this->extractMenuEntries($menu, $menu->menu);
        $names = array_values(array_unique(array_map(
            fn (array $entry): string => $entry['route'],
            $entries
        )));

        sort($names);
        return $names;
    }

    protected function createAuditUser(string $roleName, ?int $schoolId): User
    {
        $email = 'audit+' . Str::slug($roleName, '-') . '+' . Str::lower(Str::random(8)) . '@example.test';

        $user = User::query()->create([
            'name' => 'Access Audit ' . ucfirst(str_replace(['-', '_'], ' ', $roleName)),
            'email' => $email,
            'password' => Hash::make(Str::random(32)),
            'school_id' => $schoolId,
        ]);

        $user->assignRole($roleName);
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        return $user;
    }

    /**
     * @return array<int, array{
     *   route:string,
     *   text:string,
     *   visible:bool,
     *   has_params:bool,
     *   permissions:array,
     *   can:mixed,
     *   can_any:mixed
     * }>
     */
    protected function extractMenuEntries(Menu $menuComponent, array $items): array
    {
        $entries = [];

        foreach ($items as $item) {
            if (!is_array($item) || isset($item['header'])) {
                continue;
            }

            if (!empty($item['route']) && is_string($item['route'])) {
                $entries[] = [
                    'route' => $item['route'],
                    'text' => (string) ($item['text'] ?? $item['route']),
                    'visible' => $menuComponent->isVisible($item),
                    'has_params' => !empty($item['params']),
                    'permissions' => is_array($item['permissions'] ?? null) ? $item['permissions'] : [],
                    'can' => $item['can'] ?? null,
                    'can_any' => $item['can_any'] ?? null,
                ];
            }

            if (!empty($item['submenu']) && is_array($item['submenu'])) {
                $entries = array_merge($entries, $this->extractMenuEntries($menuComponent, $item['submenu']));
            }
        }

        return $entries;
    }

    protected function entryHasAccessConstraint(array $entry): bool
    {
        return !empty($entry['permissions'])
            || !empty($entry['can'])
            || !empty($entry['can_any']);
    }

    protected function hasVisibleEntryForRoute(array $entries, string $routeName): bool
    {
        foreach ($entries as $entry) {
            if (
                is_array($entry)
                && ($entry['route'] ?? null) === $routeName
                && !empty($entry['visible'])
            ) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array{
     *   allowed:bool,
     *   reason:string,
     *   permission_allowed:bool,
     *   permission_checks:array<int, array<int, string>>
     * }
     */
    protected function auditRouteAccessForUser(User $user, string $routeName): array
    {
        $route = Route::getRoutes()->getByName($routeName);
        if (!$route instanceof IlluminateRoute) {
            return [
                'allowed' => false,
                'reason' => 'route not found',
                'permission_allowed' => false,
                'permission_checks' => [],
            ];
        }

        $middleware = $route->gatherMiddleware();
        $permissionChecks = $this->extractPermissionChecks($middleware);
        $permissionAllowed = $this->passesPermissionChecksForUser($user, $permissionChecks);

        $canChecks = $this->extractCanChecks($middleware);
        $canAllowed = $this->passesCanChecksForUser($user, $canChecks);

        if (!$permissionAllowed) {
            return [
                'allowed' => false,
                'reason' => 'permission middleware denied',
                'permission_allowed' => false,
                'permission_checks' => $permissionChecks,
            ];
        }

        if (!$canAllowed) {
            return [
                'allowed' => false,
                'reason' => 'policy/ability middleware denied',
                'permission_allowed' => true,
                'permission_checks' => $permissionChecks,
            ];
        }

        return [
            'allowed' => true,
            'reason' => 'allowed',
            'permission_allowed' => true,
            'permission_checks' => $permissionChecks,
        ];
    }

    /**
     * @return array<int, array<int, string>>
     */
    protected function extractPermissionChecks(array $middleware): array
    {
        $checks = [];

        foreach ($middleware as $entry) {
            if (!is_string($entry) || !str_starts_with($entry, 'permission:')) {
                continue;
            }

            $raw = trim(substr($entry, strlen('permission:')));
            if ($raw === '') {
                continue;
            }

            $parts = explode(',', $raw);
            $permissionPart = trim((string) ($parts[0] ?? ''));
            if ($permissionPart === '') {
                continue;
            }

            $permissions = array_values(array_filter(array_map(
                fn (string $permission): string => trim($permission),
                explode('|', $permissionPart)
            )));

            if ($permissions !== []) {
                $checks[] = $permissions;
            }
        }

        return $checks;
    }

    protected function passesPermissionChecksForUser(User $user, array $checks): bool
    {
        foreach ($checks as $checkGroup) {
            $groupAllowed = false;

            foreach ($checkGroup as $permission) {
                if ($user->can($permission)) {
                    $groupAllowed = true;
                    break;
                }
            }

            if (!$groupAllowed) {
                return false;
            }
        }

        return true;
    }

    protected function passesPermissionChecksByNames(array $checks, array $rolePermissions): bool
    {
        foreach ($checks as $checkGroup) {
            $groupAllowed = false;

            foreach ($checkGroup as $permission) {
                if (in_array($permission, $rolePermissions, true)) {
                    $groupAllowed = true;
                    break;
                }
            }

            if (!$groupAllowed) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return array<int, array{ability:string, arguments:array<int, mixed>}>
     */
    protected function extractCanChecks(array $middleware): array
    {
        $checks = [];

        foreach ($middleware as $entry) {
            if (!is_string($entry) || !str_starts_with($entry, 'can:')) {
                continue;
            }

            $raw = trim(substr($entry, strlen('can:')));
            if ($raw === '') {
                continue;
            }

            $parts = array_values(array_filter(array_map('trim', explode(',', $raw)), fn (string $part): bool => $part !== ''));
            if ($parts === []) {
                continue;
            }

            $ability = array_shift($parts);
            if (!is_string($ability) || $ability === '') {
                continue;
            }

            $arguments = array_map(function (string $argument) {
                return class_exists($argument) ? $argument : $argument;
            }, $parts);

            $checks[] = [
                'ability' => $ability,
                'arguments' => $arguments,
            ];
        }

        return $checks;
    }

    protected function passesCanChecksForUser(User $user, array $checks): bool
    {
        foreach ($checks as $check) {
            try {
                $arguments = $check['arguments'] ?? [];
                $allowed = match (count($arguments)) {
                    0 => Gate::forUser($user)->check($check['ability']),
                    1 => Gate::forUser($user)->check($check['ability'], $arguments[0]),
                    default => Gate::forUser($user)->check($check['ability'], $arguments),
                };
            } catch (Throwable $e) {
                report($e);
                return false;
            }

            if (!$allowed) {
                return false;
            }
        }

        return true;
    }
}
