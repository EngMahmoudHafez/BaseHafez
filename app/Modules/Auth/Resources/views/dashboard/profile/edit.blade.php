@extends('base::components.dashboard.layouts.master')

@section('title', __('dashboard.profile'))

@section('content')
    <div class="dashboard-page">
        <x-dashboard.page-header
            :title="__('dashboard.profile')"
            :description="__('dashboard.profile_description')"
            :breadcrumbs="[['name' => __('dashboard.profile')]]"
        />

        <div class="row g-4">
            <div class="col-xl-7 col-12">
                <section class="card dashboard-card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-4">{{ __('dashboard.contact') }}</h2>
                        <form method="POST" action="{{ route('settings.update') }}" enctype="multipart/form-data">
                            @csrf
                            @method('PUT')

                            <div class="row g-3">
                                <x-dashboard.field
                                    name="name"
                                    :label="__('dashboard.name')"
                                    :value="$manager->name"
                                    col="col-md-6 col-12"
                                    required
                                />
                                <x-dashboard.field
                                    name="email"
                                    type="email"
                                    :label="__('dashboard.email')"
                                    :value="$manager->email"
                                    col="col-md-6 col-12"
                                    required
                                />
                                <x-dashboard.field
                                    name="phone"
                                    :label="__('dashboard.phone')"
                                    :value="$manager->phone"
                                    col="col-md-6 col-12"
                                />
                                <x-dashboard.field
                                    name="image"
                                    type="file"
                                    :label="__('dashboard.avatar')"
                                    col="col-md-6 col-12"
                                    accept="image/png,image/jpeg,image/webp"
                                />
                            </div>

                            <x-dashboard.button type="submit" variant="primary" class="mt-4">
                                {{ __('dashboard.save') }}
                            </x-dashboard.button>
                        </form>
                    </div>
                </section>
            </div>

            <div class="col-xl-5 col-12">
                <section class="card dashboard-card h-100">
                    <div class="card-body">
                        <h2 class="h5 mb-4">{{ __('dashboard.edit_password') }}</h2>
                        <form method="POST" action="{{ route('update-password') }}">
                            @csrf

                            <div class="row g-3">
                                <x-dashboard.field
                                    name="current_password"
                                    type="password"
                                    :label="__('dashboard.current_password')"
                                    col="col-12"
                                    class="js-password"
                                    autocomplete="current-password"
                                    required
                                />
                                <x-dashboard.field
                                    name="new_password"
                                    type="password"
                                    :label="__('dashboard.new_password')"
                                    col="col-12"
                                    class="js-password"
                                    autocomplete="new-password"
                                    required
                                />
                                <x-dashboard.field
                                    name="new_password_confirmation"
                                    type="password"
                                    :label="__('dashboard.password_confirmation')"
                                    col="col-12"
                                    class="js-password"
                                    autocomplete="new-password"
                                    required
                                />
                            </div>

                            <div class="form-check mt-3 mb-4">
                                <input class="form-check-input" id="show_password" type="checkbox" />
                                <label
                                    class="form-check-label"
                                    for="show_password"
                                >{{ __('dashboard.show_password') }}</label>
                            </div>

                            <x-dashboard.button type="submit" variant="primary">
                                {{ __('dashboard.update') }}
                            </x-dashboard.button>
                        </form>
                    </div>
                </section>
            </div>
        </div>
    </div>
@endsection

@section('page-script')
    <script>
        document.getElementById('show_password')?.addEventListener('change', (event) => {
            document.querySelectorAll('.js-password').forEach((input) => {
                input.type = event.currentTarget.checked ? 'text' : 'password';
            });
        });
    </script>
@endsection
