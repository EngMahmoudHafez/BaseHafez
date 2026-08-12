@php
    $editing = isset($user);
    $countryOptions = $countries
        ->mapWithKeys(fn ($country) => [$country->id => $country->name . ' (' . $country->dial_code . ')'])
        ->all();
    $statusOptions = collect($statuses)
        ->mapWithKeys(fn ($status) => [$status->value => __('dashboard.' . $status->value)])
        ->all();
@endphp

<div class="row g-3">
    <x-dashboard.field
        name="name"
        :label="__('dashboard.Name')"
        :value="$user->name ?? null"
        col="col-lg-6 col-12"
        required
    />

    <x-dashboard.field
        name="email"
        type="email"
        :label="__('dashboard.Email')"
        :value="$user->email ?? null"
        col="col-lg-6 col-12"
    />

    <x-dashboard.field
        name="phone"
        type="tel"
        :label="__('dashboard.Phone')"
        :value="$user->phone ?? null"
        placeholder="+201001234567"
        col="col-lg-6 col-12"
    />

    <x-dashboard.field
        name="whatsapp"
        type="tel"
        :label="__('dashboard.whatsapp')"
        :value="$user->whatsapp ?? null"
        placeholder="+201001234567"
        col="col-lg-6 col-12"
        required
    />

    <x-dashboard.field
        name="country_id"
        type="select"
        :label="__('dashboard.country')"
        :options="$countryOptions"
        :value="$user->country_id ?? null"
        :placeholder="__('dashboard.select')"
        col="col-lg-6 col-12"
    />

    <x-dashboard.field
        name="status"
        type="select"
        :label="__('dashboard.Status')"
        :options="$statusOptions"
        :value="$user->status->value ?? 'active'"
        col="col-lg-6 col-12"
        required
    />

    <x-dashboard.field
        name="avatar"
        type="file"
        :label="__('dashboard.avatar')"
        col="col-lg-6 col-12"
        accept="image/jpeg,image/png,image/webp"
    />
</div>
