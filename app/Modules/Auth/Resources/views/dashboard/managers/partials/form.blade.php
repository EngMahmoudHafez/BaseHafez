@php
    $manager = $manager ?? null;
    $roleOptions = $roles
        ->mapWithKeys(fn ($role) => [$role->id => $role->display_name_en ?: $role->name])
        ->all();
    $countryOptions = $countries
        ->mapWithKeys(fn ($country) => [$country->id => $country->name])
        ->all();
    $statusOptions = collect($statuses)
        ->mapWithKeys(fn ($status) => [$status->value => __('dashboard.' . $status->value)])
        ->all();
@endphp

<div class="row g-3">
    <x-dashboard.field name="name" :label="__('dashboard.name')" :value="$manager?->name" maxlength="180" required />

    <x-dashboard.field
        name="role_id"
        type="select"
        :label="__('dashboard.role')"
        :options="$roleOptions"
        :value="$selectedRoleId"
        :placeholder="__('dashboard.select')"
        required
    />

    <x-dashboard.field
        name="email"
        type="email"
        :label="__('dashboard.email')"
        :value="$manager?->email"
        maxlength="180"
        required
    />

    <x-dashboard.field
        name="country_id"
        type="select"
        :label="__('dashboard.country')"
        :options="$countryOptions"
        :value="$manager?->country_id"
        :placeholder="__('dashboard.select')"
    />

    <x-dashboard.field name="phone" :label="__('dashboard.phone')" :value="$manager?->phone" maxlength="30" dir="ltr" />

    <x-dashboard.field
        name="whatsapp"
        :label="__('dashboard.whatsapp')"
        :value="$manager?->whatsapp"
        maxlength="30"
        dir="ltr"
    />

    <x-dashboard.field
        name="status"
        type="select"
        :label="__('dashboard.status')"
        :options="$statusOptions"
        :value="$manager?->status?->value ?? 'active'"
        required
    />

    <x-dashboard.field name="image" type="file" :label="__('dashboard.avatar')" accept=".jpg,.jpeg,.png,.webp" />

    @unless ($manager)
        <x-dashboard.field name="password" type="password" :label="__('dashboard.password')" minlength="8" required />

        <x-dashboard.field
            name="password_confirmation"
            type="password"
            :label="__('dashboard.password_confirmation')"
            minlength="8"
            required
        />
    @endunless

    @if ($manager?->avatar)
        <div class="col-12">
            <img
                class="rounded border"
                src="{{ Storage::disk('public')->url($manager->avatar) }}"
                alt="{{ $manager->name }}"
                width="96"
                height="96"
            />
        </div>
    @endif
</div>
