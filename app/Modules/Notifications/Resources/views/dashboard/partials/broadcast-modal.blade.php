<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-scrollable">
        <form class="modal-content" method="POST" action="{{ route('dashboard.notifications.broadcast') }}">
            @csrf
            <div class="modal-header">
                <h2 class="modal-title h5" id="broadcastModalLabel">
                    {{ __('notifications.dashboard.broadcast_notification') }}
                </h2>
                <button
                    type="button"
                    class="btn-close"
                    data-bs-dismiss="modal"
                    aria-label="{{ __('dashboard.close') }}"
                ></button>
            </div>
            <div class="modal-body">
                <div class="mb-4">
                    <label class="form-label d-block">{{ __('notifications.dashboard.select_target') }}</label>
                    <div class="d-flex flex-wrap gap-3">
                        @foreach (['all', 'users'] as $target)
                            <div class="form-check">
                                <input
                                    class="form-check-input"
                                    type="radio"
                                    name="target_type"
                                    id="target_{{ $target }}"
                                    value="{{ $target }}"
                                    @checked(old('target_type', 'all') === $target)
                                />
                                <label class="form-check-label" for="target_{{ $target }}">
                                    {{ __("notifications.target_types.{$target}") }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div id="notification-users-field" class="mb-4">
                    <label class="form-label" for="notification_user_ids">{{ __('notifications.fields.users') }}</label>
                    <select id="notification_user_ids" name="user_ids[]" class="form-select" multiple size="6">
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}" @selected(in_array($user->id, old('user_ids', [])))>
                                {{ $user->name }} — {{ $user->phone }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="notification_title_ar"
                        >{{ __('notifications.fields.title_ar') }}</label>
                        <input
                            id="notification_title_ar"
                            name="title_ar"
                            class="form-control"
                            value="{{ old('title_ar') }}"
                            required
                            maxlength="255"
                            dir="rtl"
                        />
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="notification_title_en"
                        >{{ __('notifications.fields.title_en') }}</label>
                        <input
                            id="notification_title_en"
                            name="title_en"
                            class="form-control"
                            value="{{ old('title_en') }}"
                            maxlength="255"
                            dir="ltr"
                        />
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="notification_body_ar"
                        >{{ __('notifications.fields.body_ar') }}</label>
                        <textarea
                            id="notification_body_ar"
                            name="body_ar"
                            class="form-control"
                            rows="5"
                            required
                            maxlength="5000"
                            dir="rtl"
                        >{{ old('body_ar') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="notification_body_en"
                        >{{ __('notifications.fields.body_en') }}</label>
                        <textarea
                            id="notification_body_en"
                            name="body_en"
                            class="form-control"
                            rows="5"
                            maxlength="5000"
                            dir="ltr"
                        >{{ old('body_en') }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="notification_type">{{ __('notifications.fields.type') }}</label>
                        <select id="notification_type" name="type" class="form-select">
                            @foreach ($types as $value => $label)
                                <option value="{{ $value }}" @selected(old('type') === $value)>{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label
                            class="form-label"
                            for="notification_action_url"
                        >{{ __('notifications.fields.action_url') }}</label>
                        <input
                            id="notification_action_url"
                            type="url"
                            name="action_url"
                            class="form-control"
                            value="{{ old('action_url') }}"
                            maxlength="500"
                            dir="ltr"
                        />
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-label-secondary" data-bs-dismiss="modal">
                    {{ __('dashboard.cancel') }}
                </button>
                <button type="submit" class="btn btn-primary">{{ __('notifications.actions.send') }}</button>
            </div>
        </form>
    </div>
</div>
