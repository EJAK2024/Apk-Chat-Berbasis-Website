<?php

namespace App\Http\Controllers;

use App\events\MessageSent;
use App\Models\Message;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MessageController extends Controller
{
    public function send(Request $request, Room $room)
    {
        $request->validate(['body' => 'required|string|max:2000']);

        if (!$room->users->contains(Auth::id())) {
            return response()->json(['error' => 'Anda tidak memiliki akses ke ruangan ini.'], 403);
        }

        $message = Message::create ([
            'room_id' => $room->id,
            'user_id' => Auth::id(),
            'body' => $request->body,
        ]);
        broadcast(new MessageSent($message))->toOthers();

        return response()->json($message->load('user'));
    }
}
