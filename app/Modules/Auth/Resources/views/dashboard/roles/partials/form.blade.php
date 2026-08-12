@php
    $role = $role ?? null;
    $selectedPermissions = array_map('intval', old('permissions', $selectedPermissions ?? []));
@endphp

<div class="row g-3">
    <x-dashboard.field
        name="display_name_ar"
        :label="__('dashboard.Arabic Name')"
        :value="$role?->display_name_ar"
        col="col-md-6 col-12"
        maxlength="100"
        required
    />

    <x-dashboard.field
        name="display_name_en"
        :label="__('dashboard.English Name')"
        :value="$role?->display_name_en"
        col="col-md-6 col-12"
        maxlength="100"
        required
    />

    <x-dashboard.field
        name="description"
        type="textarea"
        :label="__('dashboard.Description')"
        :value="$role?->description"
        col="col-12"
        rows="3"
        maxlength="255"
    />

    <div class="col-12">
        <h2 class="h6 mb-3">{{ __('dashboard.Permissions') }}</h2>
        @error('permissions')
            <div class="alert alert-danger">{{ $message }}</div>
        @enderror

        <div class="table-responsive rounded border">
            <table class="mb-0 table align-middle">
                <thead>
                    <tr>
                        <th scope="col">{{ __('dashboard.Module') }}</th>
                        @foreach ($permissionActions as $action)
                            <th class="text-center" scope="col">{{ __('dashboard.' . $action) }}</th>
                        @endforeach
                        <th scope="col">{{ __('dashboard.Other') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($permissionGroups as $group)
                        <tr>
                            <th scope="row">{{ $group['label'] }}</th>
                            @foreach ($permissionActions as $action)
                                @php $permission = $group['actions']->get($action); @endphp
                                <td class="text-center">
                                    @if ($permission)
                                        <input
                                            class="form-check-input"
                                            id="permission_{{ $permission->id }}"
                                            name="permissions[]"
                                            type="checkbox"
                                            value="{{ $permission->id }}"
                                            @checked(in_array($permission->id, $selectedPermissions, true))
                                        />
                                    @else
                                        <span class="text-body-secondary">—</span>
                                    @endif
                                </td>
                            @endforeach
                            <td>
                                @forelse ($group['other'] as $permission)
                                    <div class="form-check">
                                        <input
                                            class="form-check-input"
                                            id="permission_{{ $permission->id }}"
                                            name="permissions[]"
                                            type="checkbox"
                                            value="{{ $permission->id }}"
                                            @checked(in_array($permission->id, $selectedPermissions, true))
                                        />
                                        <label class="form-check-label" for="permission_{{ $permission->id }}">
                                            {{ $permission->name }}
                                        </label>
                                    </div>
                                @empty
                                    <span class="text-body-secondary">—</span>
                                @endforelse
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
