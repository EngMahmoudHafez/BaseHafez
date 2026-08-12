<?php

namespace App\Modules\Auth\Http\Controllers\Api\V1\Auth;

use App\Http\Controllers\Controller;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\ResendOtpRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\SignInRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\SignUpRequest;
use App\Modules\Auth\Http\Requests\Api\V1\Auth\VerifyOtpRequest;
use App\Modules\Auth\Http\Services\Api\V1\Auth\AuthService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * @group Authentication
 *
 * APIs for user authentication including sign-up, sign-in, and sign-out
 */
class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $auth,
    ) {}

    /**
     * Sign Up
     *
     * Register a new website user with phone-based OTP verification.
     *
     * @bodyParam first_name string required The user's first name. Example: Ahmed
     * @bodyParam last_name string required The user's last name. Example: Mohamed
     * @bodyParam phone string required The user's phone number. Example: 01234567890
     * @bodyParam email string optional The user's email address. Example: ahmed@example.com
     * @bodyParam terms_accepted boolean required Must be accepted. Example: true
     *
     * @response 201 {
     *   "status": 201,
     *   "message": "تم الإنشاء بنجاح",
     *   "data": {
     *     "name": "Ahmed Mohamed",
     *     "email": "ahmed@example.com",
     *     "phone": "01234567890",
     *     "otp_token": "abc123",
     *     "otp_verified": false,
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     *   }
     * }
     * @response 400 {
     *   "status": 400,
     *   "message": "رقم الهاتف موجود بالفعل"
     * }
     */
    public function signUp(SignUpRequest $request): JsonResponse
    {
        return $this->auth->signUp($request);
    }

    /**
     * Verify OTP
     *
     * Verify the OTP code sent for the website auth flow.
     *
     * @bodyParam otp_token string required The OTP token received from sign-up or sign-in. Example: abc123...
     * @bodyParam code string required The OTP code. Example: 1111
     * @bodyParam phone string optional Optional identity echo for clients that want it. Example: 01234567890
     *
     * @response 200 {
     *   "status": 200,
     *   "message": "تم التحقق بنجاح",
     *   "data": {
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc...",
     *     "user": {
     *       "id": 1,
     *       "name": "Ahmed Mohamed",
     *       "email": "ahmed@example.com",
     *       "phone": "01234567890",
     *       "phone_verified_at": "2025-11-11T10:00:00.000000Z"
     *     }
     *   }
     * }
     */
    public function verifySignUpOtp(VerifyOtpRequest $request): JsonResponse
    {
        return $this->auth->verifySignUpOtp($request);
    }

    /**
     * Resend Sign Up OTP
     *
     * Resend the OTP code to the phone number.
     *
     * @bodyParam otp_token string required The OTP token received from sign-up. Example: abc123...
     *
     * @response 200 {
     *   "status": 200,
     *   "message": "تم إرسال رمز التحقق بنجاح",
     *   "data": {
     *     "otp_token": "xyz789...",
     *     "expires_in_minutes": 5
     *   }
     * }
     */
    public function resendSignUpOtp(ResendOtpRequest $request): JsonResponse
    {
        return $this->auth->resendSignUpOtp($request);
    }

    /**
     * Sign In
     *
     * Sign in with phone. An OTP will be sent for verification.
     *
     * @bodyParam phone string required The phone number. Example: 01234567890
     *
     * @response 201 {
     *   "status": 201,
     *   "message": "تم تسجيل الدخول بنجاح",
     *   "data": {
     *     "name": "Ahmed Mohamed",
     *     "email": "ahmed@example.com",
     *     "phone": "01234567890",
     *     "otp_token": "abc123",
     *     "otp_verified": false,
     *     "token": "eyJ0eXAiOiJKV1QiLCJhbGc..."
     *   }
     * }
     */
    public function signIn(SignInRequest $request): JsonResponse
    {
        return $this->auth->signIn($request);
    }

    /**
     * Sign Out
     *
     * Sign out the authenticated user.
     *
     * @authenticated
     *
     * @response 200 {
     *   "status": 200,
     *   "message": "تم تسجيل الخروج بنجاح"
     * }
     */
    public function signOut(Request $request): JsonResponse
    {
        return $this->auth->signOut($request);
    }
}
