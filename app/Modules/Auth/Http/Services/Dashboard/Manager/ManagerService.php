<?php

namespace App\Modules\Auth\Http\Services\Dashboard\Manager;

use App\Modules\Auth\Enums\RoleName;
use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\StoreManagerRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\UpdateManagerPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Managers\UpdateManagerRequest;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Models\Role;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use App\Modules\Auth\Repositories\RoleRepositoryInterface;
use App\Modules\Base\Concerns\HandlesResourceQuery;
use App\Modules\Base\Support\CsvCell;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ManagerService
{
    use HandlesResourceQuery;

    public function __construct(
        private readonly ManagerRepositoryInterface $managers,
        private readonly RoleRepositoryInterface $roles,
    ) {}

    public function index(Request $request): View
    {
        $managers = $this->filteredQuery($request)
            ->with(['roles', 'country'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('auth::dashboard.managers.index', [
            'managers' => $managers,
            'roles' => $this->roles->query()->orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ]);
    }

    public function create(?int $selectedRoleId = null): View
    {
        return view('auth::dashboard.managers.create', [
            ...$this->formOptions(),
            'selectedRoleId' => $selectedRoleId,
        ]);
    }

    public function store(StoreManagerRequest $request): RedirectResponse
    {
        $attributes = $request->safe()->except(['role_id', 'image']);

        DB::transaction(function () use ($attributes, $request): void {
            $manager = $this->managers->create($attributes);
            $manager->roles()->sync([(int) $request->validated('role_id')]);

            if ($request->hasFile('image')) {
                $manager->putImage($request->file('image'));
            }
        });

        return to_route('managers.index')->with('success', __('messages.created_successfully'));
    }

    public function show(Manager $manager): View
    {
        $manager->load(['roles', 'country']);

        return view('auth::dashboard.managers.show', compact('manager'));
    }

    public function edit(Manager $manager): View
    {
        $manager->load('roles');

        return view('auth::dashboard.managers.edit', [
            'manager' => $manager,
            ...$this->formOptions(),
            'selectedRoleId' => $manager->roles->first()?->id,
        ]);
    }

    public function update(UpdateManagerRequest $request, Manager $manager): RedirectResponse
    {
        $this->ensureCanManage($manager);
        $this->ensureSuperAdminNotDemotedToLast($manager, (int) $request->validated('role_id'));

        $attributes = $request->safe()->except(['role_id', 'image']);

        DB::transaction(function () use ($attributes, $manager, $request): void {
            $this->managers->update($manager->id, $attributes);
            $manager->roles()->sync([(int) $request->validated('role_id')]);
        });

        if ($request->hasFile('image')) {
            $manager->putImage($request->file('image'));
        }

        return to_route('managers.index')->with('success', __('messages.updated_successfully'));
    }

    public function editPassword(Manager $manager): View
    {
        return view('auth::dashboard.managers.edit-password', compact('manager'));
    }

    public function updatePassword(UpdateManagerPasswordRequest $request, Manager $manager): RedirectResponse
    {
        $this->ensureCanManage($manager);

        $this->managers->update($manager->id, ['password' => $request->validated('password')]);

        return to_route('managers.index')->with('success', __('messages.updated_successfully'));
    }

    public function toggle(Manager $manager): RedirectResponse
    {
        $this->ensureNotCurrentManager($manager, __('auth.cannot_block_yourself'));
        $this->ensureCanManage($manager);

        if ($manager->isActiveAccount()) {
            $this->ensureNotLastSuperAdmin($manager);
        }

        $status = $manager->isActiveAccount() ? UserStatus::Blocked : UserStatus::Active;
        $this->managers->update($manager->id, ['status' => $status]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Manager $manager): RedirectResponse
    {
        $this->ensureNotCurrentManager($manager, __('auth.cannot_delete_yourself'));
        $this->ensureCanManage($manager);
        $this->ensureNotLastSuperAdmin($manager);
        $manager->deleteImage();

        DB::transaction(function () use ($manager): void {
            $manager->roles()->detach();
            $this->managers->delete($manager->id);
        });

        return to_route('managers.index')->with('success', __('messages.deleted_successfully'));
    }

    public function export(Request $request): StreamedResponse
    {
        // Stream rows lazily from the DB (cursor) so an unfiltered export of a
        // large table does not buffer the whole result set in memory first.
        return response()->streamDownload(
            fn () => $this->writeCsv(
                $this->filteredQuery($request)->with('roles')->latest()->cursor(),
            ),
            'managers_' . now()->format('Ymd_His') . '.csv',
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /** @return Builder<Manager> */
    private function filteredQuery(Request $request): Builder
    {
        /** @var Builder<Manager> $query */
        $query = $this->managers->query();

        $this->applyDashboardFilters(
            $query,
            $request,
            searchable: ['name', 'email', 'phone'],
            filterable: [
                'status' => 'status',
                'role' => 'roles.name',
            ],
        );

        return $query;
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'roles' => $this->roles->query()->orderBy('name')->get(),
            'countries' => Country::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ];
    }

    private function ensureNotCurrentManager(Manager $manager, string $message): void
    {
        if ((int) auth('manager')->id() === $manager->id) {
            throw ValidationException::withMessages(['manager' => $message]);
        }
    }

    /**
     * The acting manager may only administer a target strictly below their own
     * rank (super admins may administer anyone). Blocks horizontal/vertical
     * privilege escalation against higher- or equal-ranked managers.
     */
    private function ensureCanManage(Manager $manager): void
    {
        $actor = auth('manager')->user();

        if (! $actor instanceof Manager || ! $actor->canManage($manager)) {
            throw ValidationException::withMessages(['manager' => __('auth.cannot_manage_higher_manager')]);
        }
    }

    /**
     * Prevent demoting (via role change) the final active super admin — the one
     * write path that can otherwise reach zero super admins and lock everyone out.
     */
    private function ensureSuperAdminNotDemotedToLast(Manager $manager, int $newRoleId): void
    {
        if (! $manager->isSuperAdmin()) {
            return;
        }

        $staysSuperAdmin = Role::query()
            ->whereKey($newRoleId)
            ->where('name', RoleName::SuperAdmin->value)
            ->exists();

        if (! $staysSuperAdmin) {
            $this->ensureNotLastSuperAdmin($manager);
        }
    }

    /**
     * Prevent blocking or deleting the final active super admin, which would
     * lock every administrator out of the dashboard.
     */
    private function ensureNotLastSuperAdmin(Manager $manager): void
    {
        if (! $manager->isSuperAdmin()) {
            return;
        }

        $anotherActiveSuperAdminExists = Manager::query()
            ->whereKeyNot($manager->id)
            ->where('status', UserStatus::Active->value)
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', RoleName::SuperAdmin->value))
            ->exists();

        if (! $anotherActiveSuperAdminExists) {
            throw ValidationException::withMessages(['manager' => __('auth.cannot_remove_last_super_admin')]);
        }
    }

    /** @param iterable<int, Manager> $managers */
    private function writeCsv(iterable $managers): void
    {
        $stream = fopen('php://output', 'wb');

        if ($stream === false) {
            throw new \RuntimeException('Unable to open the CSV output stream.');
        }

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['ID', 'Name', 'Email', 'Phone', 'Role', 'Status', 'Created At']);

        foreach ($managers as $manager) {
            fputcsv($stream, CsvCell::escapeRow([
                $manager->id,
                $manager->name,
                $manager->email,
                $manager->phone,
                $manager->roles->pluck('name')->implode(', '),
                $manager->status->value,
                $manager->created_at?->toISOString(),
            ]));
        }

        fclose($stream);
    }
}
