<?php

use App\Models\Room;    
use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('room.{roomId}', function ($user, $roomId) {
    $room = Room::find($roomId);
    return $room && $room->users->contains($user->id);
});
Broadcast::channel('presence', function ($user) {
    return ['id' => $user->id, 'name' => $user->name];
});
