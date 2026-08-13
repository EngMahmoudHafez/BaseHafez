@extends('base::components.dashboard.layouts.master')

@section('title', __('settings::settings.title'))

@section('content')
    @php
        $canUpdate = (bool) auth('manager')->user()?->hasPermission('settings-update');
    @endphp

    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('settings::settings.title')"
            :description="__('settings::settings.description')"
            :breadcrumbs="[['name' => __('settings::settings.title')]]"
        />

        <x-dashboard.table :paginator="$settings">
            <x-slot:head>
                <th>{{ __('settings::settings.fields.key') }}</th>
                <th>{{ __('settings::settings.fields.value') }}</th>
                <th>{{ __('settings::settings.fields.notes') }}</th>
                <th class="text-end">{{ __('dashboard.actions') }}</th>
            </x-slot:head>

            @forelse ($settings as $setting)
                <tr>
                    <td><span class="fw-medium">{{ $setting->key }}</span></td>
                    <td>{{ \Illuminate\Support\Str::limit((string) $setting->value, 60) }}</td>
                    <td class="text-muted">{{ \Illuminate\Support\Str::limit((string) $setting->notes, 60) }}</td>
                    <td class="text-end">
                        <x-dashboard.actions>
                            @if ($canUpdate)
                                <x-dashboard.action-edit :href="route('dashboard.settings.edit', $setting)" />
                            @endif
                        </x-dashboard.actions>
                    </td>
                </tr>
            @empty
                <x-dashboard.table-empty :colspan="4">
                    <i class="{{ dashboard_icon_class('settings') }}"></i>
                    <p class="mb-0">{{ __('settings::settings.messages.empty') }}</p>
                </x-dashboard.table-empty>
            @endforelse
        </x-dashboard.table>
    </div>
@endsection
