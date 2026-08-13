@extends('base::components.dashboard.layouts.master')

@section('title', __('settings::settings.edit_title'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('settings::settings.edit_title')"
            :breadcrumbs="[
                ['name' => __('settings::settings.title'), 'route' => 'dashboard.settings.index'],
                ['name' => $setting->key],
            ]"
        />

        <x-dashboard.form-page
            :action="route('dashboard.settings.update', $setting)"
            method="PUT"
            :cancel="route('dashboard.settings.index')"
        >
            <div class="row g-3">
                <x-dashboard.field
                    name="key"
                    :label="__('settings::settings.fields.key')"
                    :value="$setting->key"
                    col="col-12"
                    readonly
                />
                <x-dashboard.field
                    type="textarea"
                    name="value"
                    :label="__('settings::settings.fields.value')"
                    :value="$setting->value"
                    col="col-12"
                    rows="4"
                />
                <x-dashboard.field
                    type="textarea"
                    name="notes"
                    :label="__('settings::settings.fields.notes')"
                    :value="$setting->notes"
                    col="col-12"
                    rows="3"
                />
            </div>
        </x-dashboard.form-page>
    </div>
@endsection
