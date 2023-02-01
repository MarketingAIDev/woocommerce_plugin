<?php

/*
|--------------------------------------------------------------------------
| Broadcast Channels
|--------------------------------------------------------------------------
|
| Here you may register all of the event broadcasting channels that your
| application supports. The given channel authorization callbacks are
| used to check if an authenticated user can listen to the channel.
|
*/

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('user.{uid}.notifications', function ($user, $uid) {
    /** @var \Acelle\Model\User $user */
    return $user && $user->uid === $uid;
});

Broadcast::channel('chats', function ($user) {
    if($user) return ['uid'=>$user->uid];
    return [];
});