<?php

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

function apiUser(string $email = 'api-user@example.test', string $password = 'old-secret-password'): User
{
    $country = Country::factory()->create();

    return User::factory()->create([
        'country_id' => $country->id,
        'email' => $email,
        'password' => $password,
    ]);
}

test('a valid token can reach a protected api endpoint', function () {
    $user = apiUser();

    $this->withToken($user->token())
        ->getJson('/web/v1/profile')
        ->assertOk();
});

test('a token is rejected once the users token_version advances', function () {
    $user = apiUser();
    $token = $user->token();

    $this->withToken($token)->getJson('/web/v1/profile')->assertOk();

    $user->forceFill(['token_version' => $user->token_version + 1])->save();
    $this->app['auth']->forgetGuards(); // force the next request to re-resolve auth (fresh HTTP request in prod)

    $this->withToken($token)->getJson('/web/v1/profile')->assertUnauthorized();
});

test('a blocked users token is rejected on the next api call', function () {
    $user = apiUser();
    $token = $user->token();

    $this->withToken($token)->getJson('/web/v1/profile')->assertOk();

    $user->forceFill(['status' => UserStatus::Blocked])->save();
    $this->app['auth']->forgetGuards();

    $this->withToken($token)->getJson('/web/v1/profile')->assertForbidden();
});

test('changing the password via the api revokes the token used to change it', function () {
    $user = apiUser(password: 'old-secret-password');
    $token = $user->token();

    $this->withToken($token)
        ->postJson('/web/v1/password/update', [
            'current_password' => 'old-secret-password',
            'new_password' => 'updated-secret-password',
            'new_password_confirmation' => 'updated-secret-password',
        ])
        ->assertOk();

    expect(Hash::check('updated-secret-password', $user->fresh()->password))->toBeTrue();

    // The token that authorised the change no longer works.
    $this->app['auth']->forgetGuards();
    $this->withToken($token)->getJson('/web/v1/profile')->assertUnauthorized();
});

test('resetting the password bumps token_version so old tokens die', function () {
    $user = apiUser();
    $originalVersion = $user->token_version;

    // Drive the real reset flow: request OTP, verify it, reset.
    $forgot = $this->postJson('/web/v1/password/forgot', ['email' => $user->email])->assertOk();
    $verify = $this->postJson('/web/v1/password/verify-otp', [
        'email' => $user->email,
        'otp_token' => $forgot->json('data.otp_token'),
        'otp' => $forgot->json('data.verification.otp_code'),
    ])->assertOk();

    $this->postJson('/web/v1/password/reset', [
        'email' => $user->email,
        'reset_token' => $verify->json('data.reset_token'),
        'password' => 'a-brand-new-password',
        'password_confirmation' => 'a-brand-new-password',
    ])->assertOk();

    expect($user->fresh()->token_version)->toBeGreaterThan($originalVersion);
});
