# Auth Module

This module now documents the active website auth surface only.

## Scope

- Website/API auth lives under `web/v1/*`.
- There is no separate mobile auth surface in the foundation scope.
- `first_name + last_name` are combined and stored as the user's `name`, and the API also exposes that value as `username`.

## Active Website Auth Endpoints

| Method | Path | Notes |
| --- | --- | --- |
| `POST` | `/web/v1/auth/sign-up` | Creates or updates a pending user, sends OTP |
| `POST` | `/web/v1/auth/verify` | Verifies `otp_token + code` and activates/login |
| `POST` | `/web/v1/auth/resend` | Resends OTP by `otp_token` |
| `POST` | `/web/v1/auth/sign-in` | Sends OTP for an existing active user |
| `POST` | `/web/v1/auth/sign-out` | Requires `auth:api` |

## Sign-up Request

```json
{
  "country_code": "+965",
  "first_name": "Sara",
  "last_name": "Ali",
  "phone": "50123456",
  "terms_accepted": true
}
```

Notes:

- `phone` is the primary identity field.
- `terms_accepted` is required.
- `name` is still accepted for compatibility, but new website integrations should send `first_name` and `last_name`.

## Sign-in Request

```json
{
  "country_code": "+965",
  "phone": "50123456"
}
```

## OTP Verification Request

```json
{
  "otp_token": "64_character_random_token_here",
  "code": "use_data.verification.otp_code_in_local"
}
```

Optional:

- `phone` may be sent as an identity echo, but the website flow can verify with `otp_token + code` only.
- In `local` and `testing`, the auth OTP is fixed to `1111` and the response includes `data.verification.otp_code` for frontend testing.
- Staging and production use a random OTP that is never returned by the API. Connect the chosen SMS/WhatsApp delivery provider before either environment is exposed.

## Response Shape Highlights

- `data.otp_token` is a secure random 64-character token.
- `data.verification.channel` is `phone`.
- `data.verification.otp_code` is returned only in `local` and `testing`.
- `data.user.username` mirrors the stored `name`.

## Password Endpoints

| Method | Path |
| --- | --- |
| `POST` | `/web/v1/password/forgot` |
| `POST` | `/web/v1/password/verify-otp` |
| `POST` | `/web/v1/password/reset` |
| `POST` | `/web/v1/password/update` |

## Profile Endpoints

| Method | Path |
| --- | --- |
| `GET` | `/web/v1/profile` |
| `PUT` / `PATCH` / `POST` | `/web/v1/profile` |
| `POST` | `/web/v1/profile/profile-image` |

Profiles are private to the authenticated user; the module has no public user-profile endpoint.
