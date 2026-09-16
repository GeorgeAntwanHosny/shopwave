<?php

use App\Models\User;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Log;

Broadcast::channel('user.{id}', function (User $user, $id) {
    Log::info('Broadcasting Auth Check (User Channel):', [
        'auth_user_id' => $user->id,
        'requested_id' => $id,
        'types' => [gettype($user->id), gettype($id)]
    ]);
    return (string) $user->id === (string) $id;
});

Broadcast::channel('vendor.{id}', function (User $user, $id) {
    if (!$user->vendor) {
        return false;
    }
    return (string) $user->vendor->id === (string) $id;
});
