<?php

use App\Modules\Auth\Http\Requests\Dashboard\Auth\ResetPasswordRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\SendResetLinkEmailRequest;
use App\Modules\Auth\Http\Requests\Dashboard\Auth\UpdatePasswordRequest;
use App\Modules\Auth\Http\Services\Dashboard\Auth\AuthService;
use App\Modules\Auth\Http\Services\Dashboard\Auth\ManagerPasswordResetService;
use App\Modules\Auth\Models\Manager;
use App\Modules\Auth\Repositories\ManagerRepositoryInterface;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;

it('sends a reset link through the managers broker', function () {
    $service = new ManagerPasswordResetService;
    $request = dashboardRequest(SendResetLinkEmailRequest::class, [
        'email' => 'manager@example.com',
    ]);

    $broker = Mockery::mock();
    $broker->shouldReceive('sendResetLink')
        ->once()
        ->with(Mockery::any(), Mockery::type('callable'))
        ->andReturn(Password::RESET_LINK_SENT);

    Password::shouldReceive('broker')
        ->once()
        ->with('managers')
        ->andReturn($broker);

    $response = $service->sendResetLinkEmail($request);

    expect($response->isRedirection())->toBeTrue();
    expect($response->getSession()->get('status'))->toBe(__('auth.reset_link_sent'));
});

it('resets the manager password with the confirmed token flow', function () {
    $service = new ManagerPasswordResetService;

    $request = dashboardRequest(ResetPasswordRequest::class, [
        'token' => 'reset-token',
        'email' => 'manager@example.com',
        'password' => 'new-secret',
        'password_confirmation' => 'new-secret',
    ]);

    $broker = Mockery::mock();
    $broker->shouldReceive('reset')
        ->once()
        ->with(Mockery::any(), Mockery::type('callable'))
        ->andReturn(Password::PASSWORD_RESET);

    Password::shouldReceive('broker')
        ->once()
        ->with('managers')
        ->andReturn($broker);

    $response = $service->resetPassword($request);

    expect($response->isRedirection())->toBeTrue();
});

it('updates the authenticated manager password using the current password field', function () {
    $manager = new Manager([
        'password' => Hash::make('current-secret'),
    ]);
    $manager->id = 17;

    $managerRepository = Mockery::mock(ManagerRepositoryInterface::class);
    $managerRepository->shouldReceive('update')
        ->once()
        ->with(17, Mockery::on(fn (array $payload): bool => Hash::check('new-secret', $payload['password'] ?? '')))
        ->andReturnTrue();

    $service = new AuthService(
        $managerRepository,
        new ManagerPasswordResetService,
    );

    $request = UpdatePasswordRequest::create(
        '/dashboard/auth/update-password',
        'POST',
        [
            'current_password' => 'current-secret',
            'new_password' => 'new-secret',
            'new_password_confirmation' => 'new-secret',
        ],
    );

    $guard = Mockery::mock(Guard::class);
    $guard->shouldReceive('user')->once()->andReturn($manager);

    Auth::shouldReceive('guard')
        ->once()
        ->with('manager')
        ->andReturn($guard);

    $response = $service->updatePassword($request);

    expect($response->isRedirection())->toBeTrue();
    expect($response->getSession()->get('success'))->toBe(__('messages.updated_successfully'));
});

function dashboardRequest(string $requestClass, array $payload)
{
    $request = $requestClass::create('/dashboard/auth', 'POST', $payload);
    $request->setContainer(app());
    $request->setRedirector(app('redirect'));
    $request->validateResolved();

    return $request;
}
