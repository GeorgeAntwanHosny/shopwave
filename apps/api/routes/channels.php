<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{id}', function (User $user, $id) {
    return (string) $user->id === (string) $id;
});

Broadcast::channel('vendor.{id}', function (User $user, $id) {
    if (!$user->vendor) {
        return false;
    }
    return (string) $user->vendor->id === (string) $id;
});
