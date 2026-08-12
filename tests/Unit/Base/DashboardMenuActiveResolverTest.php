<?php

use App\Modules\Base\Support\DashboardMenuActiveResolver;

beforeEach(function () {
    $this->resolver = new DashboardMenuActiveResolver;
});

test('exact leaf route is active', function () {
    $item = (object) ['route' => 'users.index'];

    expect($this->resolver->itemIsActive($item, 'users.index'))->toBeTrue();
});

test('resource edit route activates its index item', function () {
    $item = (object) ['route' => 'users.index'];

    expect($this->resolver->itemIsActive($item, 'users.edit'))->toBeTrue();
});

test('parent branch is active when a descendant matches', function () {
    $item = (object) [
        'submenu' => [(object) ['route' => 'dashboard.notifications.index']],
    ];

    expect($this->resolver->branchIsActive($item, 'dashboard.notifications.show'))->toBeTrue();
});

test('unrelated route is not active', function () {
    $item = (object) ['route' => 'users.index'];

    expect($this->resolver->itemIsActive($item, 'roles.index'))->toBeFalse();
});
