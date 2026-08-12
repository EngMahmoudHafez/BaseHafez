<?php

use App\Modules\Auth\Http\Services\Api\V1\Auth\AuthOtpService;
use App\Modules\Auth\Mail\OtpEmail;
use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

test('forgot password issues a hashed email otp', function () {
    Mail::fake();
    $user = createUser('member@example.com');

    $response = $this->postJson('/web/v1/password/forgot', [
        'email' => 'MEMBER@example.com',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.verification.otp_code', '1111');

    expect(strlen($response->json('data.otp_token')))->toBe(64);

    $otp = AuthOtp::query()->firstOrFail();

    expect($otp->user_id)->toBe($user->id);
    expect($otp->identifier)->toBe('member@example.com');
    expect($otp->channel)->toBe('email');
    expect($otp->purpose)->toBe('password-reset');
    $this->assertNotSame('1111', $otp->code_hash);
    expect(Hash::check('1111', $otp->code_hash))->toBeTrue();

    Mail::assertQueued(OtpEmail::class, fn (OtpEmail $mail): bool => $mail->hasTo('member@example.com'));
});

test('forgot password does not reveal whether an email exists', function () {
    Mail::fake();

    $response = $this->postJson('/web/v1/password/forgot', [
        'email' => 'missing@example.com',
    ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonMissingPath('data.verification');

    expect(strlen($response->json('data.otp_token')))->toBe(64);
    $this->assertDatabaseCount('auth_otps', 0);
    Mail::assertNothingQueued();
});

test('user can verify the email otp and reset the password once', function () {
    Mail::fake();
    $user = createUser('reset@example.com');

    $forgotResponse = $this->postJson('/web/v1/password/forgot', [
        'email' => $user->email,
    ])->assertOk();

    $otpToken = $forgotResponse->json('data.otp_token');
    $otpCode = $forgotResponse->json('data.verification.otp_code');

    $verifyResponse = $this->postJson('/web/v1/password/verify-otp', [
        'email' => $user->email,
        'otp_token' => $otpToken,
        'otp' => $otpCode,
    ]);

    $verifyResponse
        ->assertOk()
        ->assertJsonPath('status', 200);

    $resetToken = $verifyResponse->json('data.reset_token');
    expect(strlen($resetToken))->toBe(64);

    $this->postJson('/web/v1/password/verify-otp', [
        'email' => $user->email,
        'otp_token' => $otpToken,
        'otp' => $otpCode,
    ])->assertBadRequest();

    $this->postJson('/web/v1/password/reset', [
        'email' => $user->email,
        'reset_token' => $resetToken,
        'password' => 'new-secret-password',
        'password_confirmation' => 'new-secret-password',
    ])->assertOk();

    expect(Hash::check('new-secret-password', $user->fresh()->password))->toBeTrue();

    $this->postJson('/web/v1/password/reset', [
        'email' => $user->email,
        'reset_token' => $resetToken,
        'password' => 'another-secret-password',
        'password_confirmation' => 'another-secret-password',
    ])->assertBadRequest();
});

test('login otp cannot be used to reset a password', function () {
    $user = createUser('purpose@example.com');
    $otp = app(AuthOtpService::class)->issuePhone($user, 'login');

    $this->postJson('/web/v1/password/verify-otp', [
        'email' => $user->email,
        'otp_token' => $otp->token,
        'otp' => '1111',
    ])->assertBadRequest();

    expect($otp->fresh()->verified_at)->toBeNull();
});

test('password reset otp cannot be used to sign in', function () {
    Mail::fake();
    $user = createUser('email-purpose@example.com');

    $forgotResponse = $this->postJson('/web/v1/password/forgot', [
        'email' => $user->email,
    ])->assertOk();

    $this->postJson('/web/v1/auth/verify', [
        'otp_token' => $forgotResponse->json('data.otp_token'),
        'code' => $forgotResponse->json('data.verification.otp_code'),
    ])->assertBadRequest();

    expect(AuthOtp::query()->firstOrFail()->verified_at)->toBeNull();
});

test('authenticated user can update their password', function () {
    $user = createUser('update@example.com');
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/web/v1/password/update', [
            'current_password' => 'old-secret-password',
            'new_password' => 'updated-secret-password',
            'new_password_confirmation' => 'updated-secret-password',
        ])
        ->assertOk();

    expect(Hash::check('updated-secret-password', $user->fresh()->password))->toBeTrue();
});

test('deleting a user removes their pending otps', function () {
    $user = createUser('deleted@example.com');
    $otp = app(AuthOtpService::class)->issuePhone($user, 'login');

    $user->delete();

    $this->assertDatabaseMissing('auth_otps', ['id' => $otp->id]);
});

function createUser(string $email): User
{
    $country = Country::factory()->create();

    return User::factory()->create([
        'country_id' => $country->id,
        'email' => $email,
        'password' => 'old-secret-password',
    ]);
}
