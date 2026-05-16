<?php

namespace App\Events;

use App\Models\Message;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class MessageSent implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function broadcastAs(): string
    {
    return 'message.sent'; // nama lebih simpel, hindari namespace issue
    }
    public function __construct(public Message $message)
    {
        $this->message->load('user');
    }
    public function broadcastOn(): array
    {
    return [
        new \Illuminate\Broadcasting\Channel('room.' . $this->message->room_id),
    ];
    }
    public function broadcastWith(): array
{
    return [
        'id'         => $this->message->id,
        'body'       => $this->message->body,
        'user_id'    => $this->message->user_id,  // ← tambah ini
        'room_id'    => $this->message->room_id,  // ← tambah ini
        'user'       => $this->message->user,
        'created_at' => $this->message->created_at->toDateTimeString(),
    ];
}
}

