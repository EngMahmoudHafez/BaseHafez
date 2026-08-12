<?php

namespace App\Modules\Auth\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Translation\PotentiallyTranslatedString;
use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberUtil;

class PhoneMatchesCountryCode implements ValidationRule
{
    public function __construct(
        private readonly ?string $countryCode,
    ) {}

    /**
     * Run the validation rule.
     * Validates that the phone number format matches the country code using region ISO (e.g. +20 → EG).
     *
     * @param  Closure(string): PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->countryCode || ! $value) {
            return;
        }

        $callingCode = (int) preg_replace('/\D/', '', $this->countryCode);
        $phoneUtil = PhoneNumberUtil::getInstance();
        $regions = $phoneUtil->getRegionCodesForCountryCode($callingCode);

        if (empty($regions)) {
            $fail(__('messages.Phone number does not match country code'));

            return;
        }

        $valid = false;
        foreach ($regions as $countryIso) {
            try {
                $numberProto = $phoneUtil->parse((string) $value, $countryIso);
                if ($phoneUtil->isValidNumber($numberProto)) {
                    $valid = true;
                    break;
                }
            } catch (NumberParseException) {
                continue;
            }
        }

        if (! $valid) {
            $fail(__('messages.Phone number does not match country code'));
        }
    }
}
