<?php

namespace App\Livewire\Users;

use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class ManageUserRoles extends Component
{
    use AuthorizesRequests;
    use WithPagination;

    public string $search = '';

    public string $roleFilter = 'all';

    public string $schoolFilter = 'all';

    public int $perPage = 15;

    /** @var array<int, string> */
    public array $selectedRoles = [];

    protected $queryString = [
        'search' => ['except' => ''],
        'roleFilter' => ['except' => 'all'],
        'schoolFilter' => ['except' => 'all'],
    ];

    public function mount(): void
    {
        abort_unless(auth()->user()?->can('manage user roles'), 403);
    }

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingRoleFilter(): void
    {
        $this->resetPage();
    }

    public function updatingSchoolFilter(): void
    {
        $this->resetPage();
    }

    public function updateUserRole(int $userId): void
    {
        abort_unless(auth()->user()?->can('manage user roles'), 403);

        $user = User::query()->with('roles:id,name')->findOrFail($userId);

        if (!$this->canManageAcrossSchools() && (int) $user->school_id !== (int) auth()->user()?->school_id) {
            $this->addError('selectedRoles.' . $userId, 'You can only manage users in your school.');

            return;
        }

        $newRole = $this->normalizeRole((string) ($this->selectedRoles[$userId] ?? ''));
        if ($newRole === '') {
            $this->addError('selectedRoles.' . $userId, 'Select a role before updating.');

            return;
        }

        $roleExists = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', $newRole === 'super-admin' ? ['super-admin', 'super_admin'] : [$newRole])
            ->exists();

        if (!$roleExists) {
            $this->addError('selectedRoles.' . $userId, 'Selected role is not available. Run permissions sync.');

            return;
        }

        $actor = auth()->user();
        $isActorSuperAdmin = $actor?->hasAnyRole(['super-admin', 'super_admin']) === true;
        $isTargetSuperAdmin = $user->hasAnyRole(['super-admin', 'super_admin']);

        if ($newRole === 'super-admin' && !$isActorSuperAdmin) {
            $this->addError('selectedRoles.' . $userId, 'Only super-admin can assign super-admin role.');

            return;
        }

        if ($isTargetSuperAdmin && !$isActorSuperAdmin) {
            $this->addError('selectedRoles.' . $userId, 'Only super-admin can modify another super-admin account.');

            return;
        }

        if ($isActorSuperAdmin && (int) $user->id === (int) $actor->id && $newRole !== 'super-admin') {
            $this->addError('selectedRoles.' . $userId, 'You cannot remove your own super-admin access.');

            return;
        }

        $user->syncRoles([$this->persistedRoleName($newRole)]);

        $this->selectedRoles[$userId] = $newRole;

        session()->flash('success', "{$user->name}'s role updated to " . $this->roleLabel($newRole) . '.');
    }

    protected function canManageAcrossSchools(): bool
    {
        return auth()->user()?->hasAnyRole(['super-admin', 'super_admin']) === true;
    }

    /**
     * @return array<string, string>
     */
    protected function roleOptions(): array
    {
        $roles = Role::query()
            ->where('guard_name', 'web')
            ->orderBy('name')
            ->pluck('name')
            ->all();

        $options = [];

        foreach ($roles as $role) {
            $normalized = $this->normalizeRole((string) $role);

            if ($normalized === '' || $normalized === 'applicant') {
                continue;
            }

            if (isset($options[$normalized])) {
                continue;
            }

            $options[$normalized] = $this->roleLabel($normalized);
        }

        $ordered = [];
        if (isset($options['super-admin'])) {
            $ordered['super-admin'] = $options['super-admin'];
            unset($options['super-admin']);
        }

        ksort($options);

        return array_merge($ordered, $options);
    }

    protected function normalizeRole(string $role): string
    {
        $normalized = trim($role);

        if ($normalized === 'super_admin') {
            return 'super-admin';
        }

        return $normalized;
    }

    protected function roleLabel(string $role): string
    {
        $role = $this->normalizeRole($role);

        if ($role === 'super-admin') {
            return 'Super Admin';
        }

        return ucwords(str_replace(['-', '_'], ' ', $role));
    }

    protected function persistedRoleName(string $normalizedRole): string
    {
        if ($normalizedRole !== 'super-admin') {
            return $normalizedRole;
        }

        $preferred = Role::query()
            ->where('guard_name', 'web')
            ->whereIn('name', ['super-admin', 'super_admin'])
            ->orderByRaw("CASE WHEN name = 'super-admin' THEN 0 ELSE 1 END")
            ->value('name');

        return is_string($preferred) && $preferred !== '' ? $preferred : 'super-admin';
    }

    public function render()
    {
        $query = User::query()
            ->with([
                'roles:id,name',
                'school:id,name,code',
            ]);

        if (!$this->canManageAcrossSchools()) {
            $query->where('school_id', auth()->user()?->school_id);
        } elseif ($this->schoolFilter !== 'all' && ctype_digit($this->schoolFilter)) {
            $query->where('school_id', (int) $this->schoolFilter);
        }

        if (trim($this->search) !== '') {
            $search = '%' . trim($this->search) . '%';
            $query->where(function ($inner) use ($search): void {
                $inner->where('name', 'like', $search)
                    ->orWhere('email', 'like', $search)
                    ->orWhere('phone', 'like', $search);
            });
        }

        if ($this->roleFilter !== 'all') {
            $filterRole = $this->normalizeRole($this->roleFilter);

            $query->whereHas('roles', function ($roleQuery) use ($filterRole): void {
                if ($filterRole === 'super-admin') {
                    $roleQuery->whereIn('name', ['super-admin', 'super_admin']);

                    return;
                }

                $roleQuery->where('name', $filterRole);
            });
        }

        $users = $query
            ->orderBy('name')
            ->paginate($this->perPage);

        foreach ($users as $user) {
            if (!array_key_exists($user->id, $this->selectedRoles)) {
                $currentRole = (string) ($user->roles->pluck('name')->first() ?? '');
                $this->selectedRoles[$user->id] = $this->normalizeRole($currentRole);
            }
        }

        $schools = $this->canManageAcrossSchools()
            ? School::query()->orderBy('name')->get(['id', 'name', 'code'])
            : collect();

        return view('livewire.users.manage-user-roles', [
            'users' => $users,
            'roleOptions' => $this->roleOptions(),
            'schools' => $schools,
            'canManageAcrossSchools' => $this->canManageAcrossSchools(),
        ])
            ->layout('layouts.dashboard', [
                'breadcrumbs' => [
                    ['href' => route('dashboard'), 'text' => 'Dashboard'],
                    ['href' => route('users.roles'), 'text' => 'Users & Roles', 'active' => true],
                ],
            ])
            ->title('Users & Roles');
    }
}
