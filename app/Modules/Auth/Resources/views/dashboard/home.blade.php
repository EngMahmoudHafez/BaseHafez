@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.dashboard'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.welcome_admin', ['name' => $manager->name])"
            :description="__('dashboard.admin_subtitle')"
        />

        <div class="row g-4 mb-4">
            @foreach ($statCards as $card)
                <div class="col-sm-6 col-xl-3 col-12">
                    <div class="card dashboard-stat-card h-100">
                        <div class="card-body d-flex align-items-center gap-3">
                            <span class="dashboard-stat-card__icon bg-label-{{ $card['theme'] }}">
                                <i class="ti ti-{{ $card['icon'] }}"></i>
                            </span>
                            <div>
                                <p class="text-muted mb-1">{{ __($card['label']) }}</p>
                                <h3 class="mb-0">{{ number_format($card['value']) }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="card dashboard-card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ __('dashboard.latest_users') }}</h5>
            </div>
            <div class="table-responsive">
                <table class="mb-0 table align-middle">
                    <thead>
                        <tr>
                            <th>{{ __('dashboard.Name') }}</th>
                            <th>{{ __('dashboard.Email') }}</th>
                            <th>{{ __('dashboard.Status') }}</th>
                            <th>{{ __('dashboard.Created At') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($recentUsers as $user)
                            <tr>
                                <td>{{ $user->name }}</td>
                                <td>{{ $user->email ?: '—' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $user->status->value === 'active' ? 'success' : 'secondary' }}">
                                        {{ __('dashboard.' . $user->status->value) }}
                                    </span>
                                </td>
                                <td>{{ $user->created_at?->format('Y-m-d') ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="dashboard-empty-state">{{ __('dashboard.no_data') }}</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
