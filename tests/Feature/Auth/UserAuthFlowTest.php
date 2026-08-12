<?php

use App\Modules\Auth\Enums\UserStatus;
use App\Modules\Auth\Models\AuthOtp;
use App\Modules\Auth\Models\Country;
use App\Modules\Auth\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

uses(RefreshDatabase::class);

test('legacy otp routes are not available for the website flow', function () {
    $this->postJson('/web/v1/otp/send', [])
        ->assertNotFound();
});

test('mobile auth routes are not available', function () {
    $this->postJson('/mobile/v1/auth/sign-in', [])
        ->assertNotFound();
});

test('profile subscriptions route is not available', function () {
    $this->getJson('/web/v1/profile/subscriptions')
        ->assertNotFound();
});

test('web signup accepts frontend friendly fields', function () {
    $country = Country::factory()->create(['dial_code' => '+965']);

    $response = $this->postJson('/web/v1/auth/sign-up', [
        'country_code' => $country->dial_code,
        'first_name' => 'Sara',
        'last_name' => 'Ali',
        'phone' => '50123456',
        'email' => 'user@example.com',
        'terms_accepted' => true,
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 201)
        ->assertJsonPath('data.user.name', 'Sara Ali')
        ->assertJsonPath('data.user.username', 'Sara Ali')
        ->assertJsonPath('data.user.whatsapp', '+96550123456')
        ->assertJsonPath('data.user.phone', '+96550123456')
        ->assertJsonPath('data.verification.identifier', '+96550123456')
        ->assertJsonPath('data.verification.channel', 'phone')
        ->assertJsonPath('data.verification.code_length', 4);

    expect(strlen($response->json('data.otp_token')))->toBe(64);
    expect($response->json('data.verification.otp_token'))->toBe($response->json('data.otp_token'));

    $this->assertDatabaseHas('users', [
        'country_id' => $country->id,
        'name' => 'Sara Ali',
        'whatsapp' => '+96550123456',
        'status' => UserStatus::Pending->value,
    ]);
});

test('web sign in accepts phone alias and returns verification metadata for the otp screen', function () {
    $country = Country::factory()->create(['dial_code' => '+965']);
    User::factory()->create([
        'country_id' => $country->id,
        'name' => 'User One',
        'whatsapp' => '+96550111222',
        'phone' => '+96550111222',
        'status' => UserStatus::Active,
    ]);

    $response = $this->postJson('/web/v1/auth/sign-in', [
        'country_code' => $country->dial_code,
        'phone' => '50111222',
    ]);

    $response
        ->assertCreated()
        ->assertJsonPath('status', 201)
        ->assertJsonPath('data.verification.identifier', '+96550111222')
        ->assertJsonPath('data.verification.channel', 'phone')
        ->assertJsonPath('data.verification.code_length', 4);

    expect(strlen($response->json('data.otp_token')))->toBe(64);
    expect($response->json('data.verification.otp_token'))->toBe($response->json('data.otp_token'));
});

test('authenticated user can fetch profile in frontend friendly shape', function () {
    $country = Country::factory()->create(['dial_code' => '+965']);
    $user = User::factory()->create([
        'country_id' => $country->id,
        'name' => 'Sara Ali',
        'phone' => '+96550123456',
        'whatsapp' => '+96550123456',
        'email' => 'sara@example.com',
        'status' => UserStatus::Active,
    ]);

    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/web/v1/profile')
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.name', 'Sara Ali')
        ->assertJsonPath('data.first_name', 'Sara')
        ->assertJsonPath('data.last_name', 'Ali')
        ->assertJsonPath('data.email', 'sara@example.com')
        ->assertJsonPath('data.phone', '+96550123456')
        ->assertJsonPath('data.country_code', '+965')
        ->assertJsonPath('data.status', UserStatus::Active->value);
});

test('authenticated user can update profile name and image', function () {
    Storage::fake('public');

    $country = Country::factory()->create(['dial_code' => '+965']);
    $user = User::factory()->create([
        'country_id' => $country->id,
        'name' => 'Old Name',
        'phone' => '+96550123456',
        'whatsapp' => '+96550123456',
        'email' => 'old@example.com',
        'status' => UserStatus::Active,
    ]);

    $token = JWTAuth::fromUser($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/web/v1/profile', [
            'first_name' => 'Sara',
            'last_name' => 'Ali',
            'email' => 'sara@example.com',
            'profile_image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $response
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.name', 'Sara Ali')
        ->assertJsonPath('data.first_name', 'Sara')
        ->assertJsonPath('data.last_name', 'Ali')
        ->assertJsonPath('data.email', 'sara@example.com');

    $updatedUser = $user->fresh();

    expect($updatedUser->name)->toBe('Sara Ali');
    expect($updatedUser->email)->toBe('sara@example.com');
    expect($updatedUser->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($updatedUser->avatar);
});

test('updating only first name keeps the existing last name', function () {
    $country = Country::factory()->create(['dial_code' => '+965']);
    $user = User::factory()->create([
        'country_id' => $country->id,
        'name' => 'Sara Ali',
        'phone' => '+96550123456',
        'whatsapp' => '+96550123456',
        'status' => UserStatus::Active,
    ]);

    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/web/v1/profile', [
            'first_name' => 'Maha',
        ])
        ->assertOk()
        ->assertJsonPath('data.name', 'Maha Ali')
        ->assertJsonPath('data.first_name', 'Maha')
        ->assertJsonPath('data.last_name', 'Ali');

    expect($user->fresh()->name)->toBe('Maha Ali');
});

test('authenticated user can upload a profile image and read it from the profile endpoint', function () {
    Storage::fake('public');

    $country = Country::factory()->create(['dial_code' => '+965']);
    $user = User::factory()->create([
        'country_id' => $country->id,
        'name' => 'Sara Ali',
        'phone' => '+96550123456',
        'whatsapp' => '+96550123456',
        'status' => UserStatus::Active,
    ]);

    $token = JWTAuth::fromUser($user);

    $uploadResponse = $this->withHeader('Authorization', 'Bearer ' . $token)
        ->post('/web/v1/profile/profile-image', [
            'profile_image' => UploadedFile::fake()->image('avatar.jpg'),
        ]);

    $uploadedUser = $user->fresh();
    $expectedUrl = asset('storage/' . $uploadedUser->avatar);

    $uploadResponse
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.path', $uploadedUser->avatar)
        ->assertJsonPath('data.url', $expectedUrl)
        ->assertJsonPath('data.profile_image_url', $expectedUrl);

    expect($uploadedUser->avatar)->not->toBeNull();
    Storage::disk('public')->assertExists($uploadedUser->avatar);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->getJson('/web/v1/profile')
        ->assertOk()
        ->assertJsonPath('data.avatar', $expectedUrl)
        ->assertJsonPath('data.profile_image_url', $expectedUrl);
});

test('otp verification can complete with the debug code returned in testing', function () {
    $country = Country::factory()->create(['dial_code' => '+966']);

    $signUpResponse = $this->postJson('/web/v1/auth/sign-up', [
        'country_code' => $country->dial_code,
        'first_name' => 'Lina',
        'last_name' => 'Saeed',
        'phone' => '512345678',
        'terms_accepted' => true,
    ])->assertCreated();

    $otp = AuthOtp::query()->latest('id')->firstOrFail();
    $otpCode = $signUpResponse->json('data.verification.otp_code');

    expect($otpCode)->toBeString();
    expect($otpCode)->toBe('1111');

    $verifyResponse = $this->postJson('/web/v1/auth/verify', [
        'otp_token' => $otp->token,
        'code' => $otpCode,
    ]);

    $verifyResponse
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.user.status', UserStatus::Active->value)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'whatsapp', 'status', 'token'],
                'token',
            ],
        ]);
});

test('authenticated user can sign out without changing account status', function () {
    $country = Country::factory()->create(['dial_code' => '+965']);
    $user = User::factory()->create([
        'country_id' => $country->id,
        'status' => UserStatus::Active,
        'last_login_at' => now()->subHour(),
    ]);
    $originalLastLoginAt = $user->last_login_at?->toISOString();
    $token = JWTAuth::fromUser($user);

    $this->withHeader('Authorization', 'Bearer ' . $token)
        ->postJson('/web/v1/auth/sign-out')
        ->assertOk()
        ->assertJsonPath('status', 200);

    $user->refresh();

    expect($user->status->value)->toBe(UserStatus::Active->value);
    expect($user->last_login_at?->toISOString())->toBe($originalLastLoginAt);
});

test('sign up requires accepting terms', function () {
    $country = Country::factory()->create(['dial_code' => '+20']);

    $this->postJson('/web/v1/auth/sign-up', [
        'country_id' => $country->id,
        'first_name' => 'User',
        'last_name' => 'One',
        'phone' => '01012345678',
    ])
        ->assertUnprocessable();

    $this->assertDatabaseMissing('users', [
        'country_id' => $country->id,
        'whatsapp' => '+201012345678',
    ]);
});

test('user can sign up and verify with the testing debug code', function () {
    $country = Country::factory()->create(['dial_code' => '+20']);

    $signUpResponse = $this->postJson('/web/v1/auth/sign-up', [
        'country_id' => $country->id,
        'first_name' => 'User',
        'last_name' => 'One',
        'phone' => '01012345678',
        'email' => 'user@example.com',
        'terms_accepted' => true,
    ]);

    $signUpResponse
        ->assertCreated()
        ->assertJsonPath('status', 201)
        ->assertJsonPath('data.user.status', UserStatus::Pending->value)
        ->assertJsonPath('data.user.username', 'User One')
        ->assertJsonPath('data.verification.otp_code', '1111')
        ->assertJsonMissingPath('data.user.otp')
        ->assertJsonMissingPath('data.otp')
        ->assertJsonStructure([
            'data' => [
                'verification' => ['otp_code'],
            ],
        ]);
    expect(strlen($signUpResponse->json('data.otp_token')))->toBe(64);

    $otp = AuthOtp::query()->latest('id')->firstOrFail();
    $otpCode = $signUpResponse->json('data.verification.otp_code');

    $verifyResponse = $this->postJson('/web/v1/auth/verify', [
        'otp_token' => $otp->token,
        'code' => $otpCode,
        'phone' => '+201012345678',
    ]);

    $verifyResponse
        ->assertOk()
        ->assertJsonPath('status', 200)
        ->assertJsonPath('data.user.status', UserStatus::Active->value)
        ->assertJsonStructure([
            'data' => [
                'user' => ['id', 'name', 'whatsapp', 'status', 'token'],
                'token',
            ],
        ]);
});

test('invalid otp is rejected without activating the user', function () {
    $country = Country::factory()->create(['dial_code' => '+20']);

    $this->postJson('/web/v1/auth/sign-up', [
        'country_id' => $country->id,
        'first_name' => 'User',
        'last_name' => 'Two',
        'phone' => '01022222222',
        'terms_accepted' => true,
    ])->assertCreated();

    $otp = AuthOtp::query()->latest('id')->firstOrFail();

    $this->postJson('/web/v1/auth/verify', [
        'otp_token' => $otp->token,
        'code' => '0000',
        'phone' => '+201022222222',
    ])
        ->assertBadRequest()
        ->assertJsonPath('status', 400);

    $this->assertDatabaseHas('users', [
        'whatsapp' => '+201022222222',
        'status' => UserStatus::Pending->value,
    ]);
});

test('blocked users cannot start sign in', function () {
    $country = Country::factory()->create(['dial_code' => '+20']);
    User::factory()->create([
        'country_id' => $country->id,
        'whatsapp' => '+201033333333',
        'status' => UserStatus::Blocked,
    ]);

    $this->postJson('/web/v1/auth/sign-in', [
        'country_id' => $country->id,
        'phone' => '01033333333',
    ])
        ->assertForbidden()
        ->assertJsonPath('status', 403);
});

test('pending users cannot start sign in until otp verification completes', function () {
    $country = Country::factory()->create(['dial_code' => '+20']);
    User::factory()->create([
        'country_id' => $country->id,
        'whatsapp' => '+201044444444',
        'status' => UserStatus::Pending,
    ]);

    $this->postJson('/web/v1/auth/sign-in', [
        'country_id' => $country->id,
        'phone' => '01044444444',
    ])
        ->assertForbidden()
        ->assertJsonPath('status', 403);
});
