<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class RoomController extends Controller
{
    use AuthorizesRequests;

    // Halaman utama chat
    public function index()
    {
        /** @var \App\Models\User $user */
        $user = Auth::user();

        $rooms = $user->rooms()->with('latestMessage')->get();

        $users = User::where('id', '!=', $user->id)->get();

        return view('chat.index', compact('rooms', 'users'));
    }

    // Buat group room baru
    public function createGroup(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:100'
        ]);

        $room = Room::create([
            'name'       => $request->name,
            'type'       => 'group',
            'created_by' => Auth::id(),
        ]);

        $room->users()->attach(Auth::id());

        return response()->json($room);
    }

    // Buat atau ambil private room
    public function createPrivate(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $myId    = Auth::id();
        $otherId = $request->user_id;

        $room = Room::where('type', 'private')
            ->whereHas('users', fn($q) => $q->where('user_id', $myId))
            ->whereHas('users', fn($q) => $q->where('user_id', $otherId))
            ->first();

        if (!$room) {

            $otherUser = User::findOrFail($otherId);

            /** @var \App\Models\User $authUser */
            $authUser = Auth::user();

            $room = Room::create([
                'name'       => $authUser->name . ' & ' . $otherUser->name,
                'type'       => 'private',
                'created_by' => $myId,
            ]);

            $room->users()->attach([$myId, $otherId]);
        }

        return response()->json($room);
    }

    // Tambah member ke group
    public function addMember(Request $request, Room $room)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id'
        ]);

        $room->users()->syncWithoutDetaching([$request->user_id]);

        return response()->json([
            'message' => 'Member ditambahkan'
        ]);
    }

    // Ambil data room beserta messages
    public function show(Room $room)
    {
        $this->authorize('view', $room);

        $messages = $room->messages()
            ->with('user')
            ->latest()
            ->take(50)
            ->get()
            ->reverse();

        $members = $room->users()->get();

        return response()->json(compact(
            'room',
            'messages',
            'members'
        ));
    }
}