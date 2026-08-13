<?php

namespace App\Modules\Auth\Http\Services\Dashboard\Roles;

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Http\Requests\Dashboard\Role\RoleRequest;
use App\Modules\Auth\Models\Permission;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Repositories\PermissionRepositoryInterface;
use App\Modules\Auth\Repositories\RoleRepositoryInterface;
use App\Modules\Base\Concerns\HandlesResourceQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class RoleService
{
    use HandlesResourceQuery;

    private const PERMISSION_ACTIONS = ['create', 'read', 'update', 'delete'];

    public function __construct(
        private readonly RoleRepositoryInterface $roles,
        private readonly PermissionRepositoryInterface $permissions,
    ) {}

    public function index(Request $request): View
    {
        $query = $this->roles->query();

        $this->applyDashboardFilters(
            $query,
            $request,
            searchable: ['name', 'display_name_ar', 'display_name_en', 'description'],
            filterable: [],
        );

        return view('auth::dashboard.roles.list', [
            'roles' => $query->orderBy('id')->paginate(20)->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('auth::dashboard.roles.create', [
            ...$this->permissionFormData(),
        ]);
    }

    public function store(RoleRequest $request): RedirectResponse
    {
        DB::transaction(function () use ($request): void {
            $role = $this->roles->create($this->roleData($request));
            $role->permissions()->sync($request->validated('permissions', []));
        });

        return to_route('roles.index')->with('success', __('messages.created_successfully'));
    }

    public function show(int $id): View
    {
        return view('auth::dashboard.roles.show', [
            'role' => $this->role($id, ['permissions']),
        ]);
    }

    public function edit(int $id): View
    {
        $role = $this->role($id, ['permissions']);

        return view('auth::dashboard.roles.edit', [
            'role' => $role,
            'selectedPermissions' => $role->permissions->modelKeys(),
            ...$this->permissionFormData(),
        ]);
    }

    public function update(RoleRequest $request, int $id): RedirectResponse
    {
        $this->ensureNotCoreRole($id);

        DB::transaction(function () use ($request, $id): void {
            $this->roles->update($id, $this->roleData($request));
            $this->role($id)->permissions()->sync($request->validated('permissions', []));
        });

        return to_route('roles.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(int $id): RedirectResponse
    {
        $this->ensureNotCoreRole($id);
        $this->ensureRoleHasNoManagers($id);

        DB::transaction(function () use ($id): void {
            $this->role($id)->permissions()->detach();
            $this->roles->delete($id);
        });

        return to_route('roles.index')->with('success', __('messages.deleted_successfully'));
    }

    public function managers(int $id): RedirectResponse
    {
        return to_route('managers.index', ['role' => $this->role($id)->name]);
    }

    /** @return array<string, mixed> */
    private function roleData(RoleRequest $request): array
    {
        $data = $request->validated();

        return [
            'name' => Str::slug($data['display_name_en']),
            'guard_name' => 'manager',
            'display_name_ar' => $data['display_name_ar'],
            'display_name_en' => $data['display_name_en'],
            'description' => $data['description'] ?? null,
        ];
    }

    /** @param array<int, string> $relations */
    private function role(int $id, array $relations = []): Role
    {
        return $this->roles->getById($id, relations: $relations);
    }

    /**
     * The two core roles (super_admin, admin) grant full access and must never
     * be renamed, re-permissioned, or deleted from the dashboard.
     */
    private function ensureNotCoreRole(int $id): void
    {
        if (RoleName::isCoreName($this->role($id)->name)) {
            throw ValidationException::withMessages(['role' => __('auth.cannot_modify_core_role')]);
        }
    }

    /**
     * Deleting a role that still has managers would silently strip their
     * authorization (Laratrust detaches on delete). Block it with a clear error
     * instead — the operator should reassign those managers first.
     */
    private function ensureRoleHasNoManagers(int $id): void
    {
        if ($this->role($id)->managers()->exists()) {
            throw ValidationException::withMessages(['role' => __('auth.role_has_assigned_managers')]);
        }
    }

    /** @return array{permissionActions: array<int, string>, permissionGroups: Collection<int|string, array<string, mixed>>} */
    private function permissionFormData(): array
    {
        /** @var Collection<int, Permission> $all */
        $all = $this->permissions->getAll();

        // Collection's TValue is declared invariant, so the precise per-group
        // shape produced below is widened to the documented array<string, mixed>.
        /** @var Collection<int|string, array<string, mixed>> $groups */
        $groups = $all
            ->groupBy(fn ($permission): string => Str::beforeLast($permission->name, '-'))
            ->map(function (Collection $permissions, string $module): array {
                $byAction = $permissions->keyBy(fn ($permission): string => Str::afterLast($permission->name, '-'));

                return [
                    'label' => __('dashboard.permission_groups.' . str_replace('-', '_', $module)),
                    'actions' => $byAction->only(self::PERMISSION_ACTIONS),
                    'other' => $byAction->except(self::PERMISSION_ACTIONS)->values(),
                ];
            });

        return [
            'permissionActions' => self::PERMISSION_ACTIONS,
            'permissionGroups' => $groups,
        ];
    }
}
