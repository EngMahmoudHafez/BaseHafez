<?php

namespace App\Modules\Auth\Http\Services\Dashboard\User;

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Http\Requests\Dashboard\User\UserRequest;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\User;
use App\Modules\Auth\Repositories\UserRepositoryInterface;
use App\Modules\Base\Concerns\HandlesResourceQuery;
use App\Modules\Base\Support\CsvCell;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\StreamedResponse;

class UserService
{
    use HandlesResourceQuery;

    public function __construct(private readonly UserRepositoryInterface $userRepository) {}

    public function index(Request $request): View
    {
        $users = $this->filteredUsers($request)
            ->with('country')
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('auth::dashboard.users.index', compact('users'));
    }

    public function create(): View
    {
        return view('auth::dashboard.users.create', $this->formOptions());
    }

    public function store(UserRequest $request): RedirectResponse
    {
        $user = $this->userRepository->create($this->userAttributes($request));

        if ($request->hasFile('avatar')) {
            $user->putImage($request->file('avatar'));
        }

        return redirect()->route('users.index')->with('success', __('messages.created_successfully'));
    }

    public function show(int $userId): View
    {
        $user = $this->userRepository->getById($userId, relations: ['country']);

        return view('auth::dashboard.users.show', compact('user'));
    }

    public function edit(int $userId): View
    {
        $user = $this->userRepository->getById($userId, relations: ['country']);

        return view('auth::dashboard.users.edit', [
            'user' => $user,
            ...$this->formOptions(),
        ]);
    }

    public function update(UserRequest $request, int $userId): RedirectResponse
    {
        $user = $this->userRepository->getById($userId);
        $this->userRepository->update($userId, $this->userAttributes($request));

        if ($request->hasFile('avatar')) {
            $user->putImage($request->file('avatar'));
        }

        return redirect()->route('users.index')->with('success', __('messages.updated_successfully'));
    }

    public function destroy(int $userId): RedirectResponse
    {
        $user = $this->userRepository->getById($userId);
        $user->deleteImage();
        $this->userRepository->delete($userId);

        return to_route('users.index')->with('success', __('messages.deleted_successfully'));
    }

    public function toggle(int $userId): RedirectResponse
    {
        $user = $this->userRepository->getById($userId);
        $status = $user->status === UserStatus::Active ? UserStatus::Blocked : UserStatus::Active;
        $this->userRepository->update($userId, ['status' => $status]);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function export(Request $request): StreamedResponse
    {
        $fileName = 'users_' . now()->format('Ymd_His') . '.csv';

        // Stream rows lazily from the DB (cursor) so an unfiltered export of a
        // large table does not buffer the whole result set in memory first.
        return response()->streamDownload(
            fn () => $this->writeCsv(
                $this->filteredUsers($request)->with('country')->latest()->cursor(),
            ),
            $fileName,
            ['Content-Type' => 'text/csv; charset=UTF-8'],
        );
    }

    /** @return Builder<User> */
    private function filteredUsers(Request $request): Builder
    {
        /** @var Builder<User> $query */
        $query = $this->userRepository->query();

        $this->applyDashboardFilters(
            $query,
            $request,
            searchable: ['name', 'email', 'phone', 'whatsapp'],
            filterable: ['status' => 'status'],
        );

        return $query;
    }

    /** @return array<string, mixed> */
    private function userAttributes(UserRequest $request): array
    {
        return $request->safe()->except('avatar');
    }

    /** @return array<string, mixed> */
    private function formOptions(): array
    {
        return [
            'countries' => Country::query()->where('is_active', true)->orderBy('name')->get(),
            'statuses' => UserStatus::cases(),
        ];
    }

    /** @param iterable<int, User> $users */
    private function writeCsv(iterable $users): void
    {
        $stream = fopen('php://output', 'wb');

        if ($stream === false) {
            throw new \RuntimeException('Unable to open the CSV output stream.');
        }

        fwrite($stream, "\xEF\xBB\xBF");
        fputcsv($stream, ['ID', 'Name', 'Email', 'Phone', 'WhatsApp', 'Status', 'Created At']);

        foreach ($users as $user) {
            fputcsv($stream, $this->csvRow($user));
        }

        fclose($stream);
    }

    /** @return list<int|string|null> */
    private function csvRow(User $user): array
    {
        return CsvCell::escapeRow([
            $user->id,
            $user->name,
            $user->email,
            $user->phone,
            $user->whatsapp,
            $user->status->value,
            $user->created_at?->toISOString(),
        ]);
    }
}
