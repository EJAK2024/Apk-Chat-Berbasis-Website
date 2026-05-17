@extends('layouts.app')
@section('content')
<div style="display:flex; height:100vh; width:100vw; position:fixed; top:0; left:0;" x-data="chatApp" x-init="init()">
    {{-- SIDEBAR KIRI --}}
    <div class="w-80 bg-white border-r border-gray-200 flex flex-col shadow-sm">
        
        {{-- Header --}}    
        <div class="p-4 bg-blue-600 text-white flex justify-between items-center">
            <span class="font-bold text-xl">ChatApp</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-xs bg-white text-blue-600 px-3 py-1 rounded-full hover:bg-gray-100 font-semibold">
                    Logout
                </button>
            </form>
        </div>

        {{-- Info User --}}
        <div class="p-4 border-b bg-blue-50 flex items-center gap-3">
            <div class="w-10 h-10 bg-blue-500 rounded-full flex items-center justify-center text-white font-bold text-lg">
                {{ substr(Auth::user()->name, 0, 1) }}
            </div>
            <div>
                <p class="font-semibold text-sm text-gray-800">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-500 font-medium">● Online</p>
            </div>
        </div>

        {{-- Tombol Buat Room --}}
        <div class="p-3 border-b flex gap-2">
            <button @click="showGroupModal=true"
                class="flex-1 bg-blue-500 text-white text-sm py-2 rounded-lg hover:bg-blue-600 font-medium transition">
                + Group
            </button>
            <button @click="showPrivateModal=true"
                class="flex-1 bg-green-500 text-white text-sm py-2 rounded-lg hover:bg-green-600 font-medium transition">
                + Private
            </button>
        </div>

        {{-- Label --}}
        <div class="px-4 py-2 bg-gray-50 border-b">
            <p class="text-xs font-semibold text-gray-400 uppercase tracking-wider">Daftar Chat</p>
        </div>

        {{-- Daftar Room --}}
<div class="overflow-y-auto flex-1">
    <template x-if="rooms.length === 0">
        <div class="p-6 text-center text-gray-400">
            <p class="text-3xl mb-2">💬</p>
            <p class="text-sm">Belum ada chat</p>
            <p class="text-xs mt-1">Buat group atau mulai private chat</p>
        </div>
    </template>
    <template x-for="room in rooms" :key="room.id">
        <div @click="loadRoom(room.id)"
            class="p-4 border-b cursor-pointer hover:bg-gray-50 transition"
            :class="activeRoomId == room.id ? 'bg-blue-50 border-l-4 border-l-blue-500' : 'border-l-4 border-l-transparent'">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-bold text-sm flex-shrink-0"
                    :style="room.type === 'group' ? 'background:#6366f1' : 'background:#10b981'">
                    <span x-text="room.type === 'group' ? '👥' : '👤'"></span>
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex justify-between items-center">
                        <p class="font-semibold text-sm text-gray-800 truncate" x-text="room.name"></p>
                        <span class="text-xs text-gray-400 ml-1" x-text="room.type === 'group' ? 'Group' : 'DM'"></span>
                    </div>
                    <div class="flex justify-between items-center mt-0.5">
                        <p class="text-xs text-gray-500 truncate" x-text="room.latest_message || 'Belum ada pesan'"></p>
                        <p class="text-xs text-gray-400 ml-1 flex-shrink-0" x-text="room.latest_time || ''"></p>
                    </div>
                </div>
            </div>
        </div>
    </template>
</div>
</div>

    {{-- AREA CHAT KANAN --}}
    <div class="flex-1 flex flex-col">

        {{-- Kalau belum pilih room --}}
        <template x-if="!activeRoomId">
            <div class="flex-1 flex items-center justify-center text-gray-400 bg-gray-50">
                <div class="text-center">
                    <p class="text-6xl mb-4">💬</p>
                    <p class="text-xl font-semibold text-gray-500">Selamat datang di ChatApp!</p>
                    <p class="text-sm mt-2 text-gray-400">Pilih chat di sebelah kiri untuk mulai percakapan</p>
                </div>
            </div>
        </template>

        {{-- Kalau sudah pilih room --}}
        <template x-if="activeRoomId">
            <div class="flex flex-col h-full">

                {{-- Header Room --}}
                <div class="bg-white border-b px-6 py-4 flex justify-between items-center shadow-sm">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-indigo-500 rounded-full flex items-center justify-center text-white text-lg">
                            💬
                        </div>
                        <div>
                            <p class="font-bold text-gray-800" x-text="activeRoom ? activeRoom.name : ''"></p>
                            <p class="text-xs text-gray-500">
                                <span x-text="members.length"></span> anggota
                            </p>
                        </div>
                    </div>
                    <template x-if="activeRoom && activeRoom.type === 'group'">
                        <button @click="showAddMember=true"
                            class="text-sm bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 font-medium transition">
                            + Tambah Member
                        </button>
                    </template>
                </div>

                {{-- Area Pesan --}}
                <div class="flex-1 overflow-y-auto p-6 space-y-4 bg-gray-50" id="messages-container">
                    <template x-for="msg in messages" :key="msg.id">
                        <div :class="msg.user_id == {{ Auth::id() }} ? 'flex justify-end' : 'flex justify-start'">
                            <div class="flex items-end gap-2 max-w-md"
                                :class="msg.user_id == {{ Auth::id() }} ? 'flex-row-reverse' : 'flex-row'">
                                
                                {{-- Avatar --}}
                                <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-xs font-bold flex-shrink-0"
                                    :style="msg.user_id == {{ Auth::id() }} ? 'background:#3b82f6' : 'background:#6b7280'">
                                    <span x-text="msg.user ? msg.user.name.charAt(0).toUpperCase() : '?'"></span>
                                </div>

                                {{-- Bubble --}}
                                <div :class="msg.user_id == {{ Auth::id() }}
                                    ? 'bg-blue-500 text-white rounded-tl-2xl rounded-tr-sm rounded-bl-2xl rounded-br-2xl'
                                    : 'bg-white text-gray-800 border rounded-tl-sm rounded-tr-2xl rounded-bl-2xl rounded-br-2xl shadow-sm'"
                                    class="px-4 py-3">
                                    <template x-if="msg.user_id != {{ Auth::id() }}">
                                      <p class="text-xs font-semibold text-blue-500 mb-1" x-text="msg.user ? msg.user.name : ''"></p>
                                    </template>
                                    <p class="text-sm leading-relaxed" x-text="msg.body"></p>
                                    <p class="text-xs mt-1 opacity-60 text-right" x-text="formatTime(msg.created_at)"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input Pesan --}}
                <div class="bg-white border-t px-6 py-4">
                    <div class="flex gap-3 items-center">
                        <input x-model="newMessage"
                            @keyup.enter="sendMessage()"
                            type="text"
                            placeholder="Ketik pesan di sini..."
                            class="flex-1 border border-gray-300 rounded-full px-5 py-3 text-sm focus:outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100 transition">
                        <button @click="sendMessage()"
                            :disabled="!newMessage.trim()"
                            class="bg-blue-500 text-white px-6 py-3 rounded-full hover:bg-blue-600 disabled:opacity-40 disabled:cursor-not-allowed font-medium text-sm transition flex items-center gap-2">
                            Kirim ➤
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- MODAL: Buat Group --}}
    <div x-show="showGroupModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-96 shadow-xl">
            <h3 class="font-bold text-lg mb-1">Buat Group Chat</h3>
            <p class="text-sm text-gray-500 mb-4">Masukkan nama untuk group chat baru</p>
            <input x-model="groupName" type="text" placeholder="Nama group..."
                class="w-full border border-gray-300 rounded-lg p-3 mb-4 focus:outline-none focus:border-blue-400">
            <div class="flex gap-2">
                <button @click="createGroup()"
                    class="flex-1 bg-blue-500 text-white py-2 rounded-lg hover:bg-blue-600 font-medium">
                    Buat Group
                </button>
                <button @click="showGroupModal=false"
                    class="flex-1 bg-gray-100 text-gray-600 py-2 rounded-lg hover:bg-gray-200 font-medium">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Private Chat --}}
    <div x-show="showPrivateModal" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-96 shadow-xl">
            <h3 class="font-bold text-lg mb-1">Mulai Private Chat</h3>
            <p class="text-sm text-gray-500 mb-4">Pilih pengguna untuk memulai percakapan</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($users as $user)
                <div @click="createPrivate({{ $user->id }})"
                    class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-gray-50 border border-gray-100 transition">
                    <div class="w-10 h-10 bg-gradient-to-br from-blue-400 to-purple-500 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <div>
                        <p class="font-medium text-sm">{{ $user->name }}</p>
                        <p class="text-xs {{ $user->is_online ? 'text-green-500' : 'text-gray-400' }}">
                            {{ $user->is_online ? '● Online' : '○ Offline' }}
                        </p>
                    </div>
                </div>
                @endforeach
            </div>
            <button @click="showPrivateModal=false"
                class="w-full mt-4 bg-gray-100 text-gray-600 py-2 rounded-lg hover:bg-gray-200 font-medium">
                Tutup
            </button>
        </div>
    </div>

    {{-- MODAL: Add Member --}}
    <div x-show="showAddMember" x-cloak
        class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50">
        <div class="bg-white rounded-2xl p-6 w-96 shadow-xl">
            <h3 class="font-bold text-lg mb-1">Tambah Member</h3>
            <p class="text-sm text-gray-500 mb-4">Pilih pengguna untuk ditambahkan ke group</p>
            <div class="space-y-2 max-h-64 overflow-y-auto">
                @foreach($users as $user)
                <div @click="addMember({{ $user->id }})"
                    class="flex items-center gap-3 p-3 rounded-xl cursor-pointer hover:bg-gray-50 border border-gray-100 transition">
                    <div class="w-10 h-10 bg-gradient-to-br from-green-400 to-teal-500 rounded-full flex items-center justify-center text-white font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <p class="font-medium text-sm">{{ $user->name }}</p>
                </div>
                @endforeach
            </div>
            <button @click="showAddMember=false"
                class="w-full mt-4 bg-gray-100 text-gray-600 py-2 rounded-lg hover:bg-gray-200 font-medium">
                Tutup
            </button>
        </div>
    </div>

</div>
{{-- <script src="https://js.pusher.com/8.2.0/pusher.min.js"></script>
<script>
        window.Pusher = Pusher;  // Penting!

        window.Echo = new Echo({
            broadcaster: 'reverb',
            key: '{{ env("REVERB_APP_KEY") }}',
            wsHost: '{{ env("REVERB_HOST") }}',
            wsPort: {{ env("REVERB_PORT") }},
            wssPort: {{ env("REVERB_PORT") }},
            forceTLS: false,
            enabledTransports: ['ws', 'wss'],
        });

        console.log('✅ Echo + Reverb initialized');
    </script> --}}
<script>
function chatApp() {
    return {
        activeRoomId: null,
        activeRoom: null,
        messages: [],
        members: [],
        newMessage: '',
        groupName: '',
        showGroupModal: false,
        showPrivateModal: false,
        showAddMember: false,
        pollingInterval: null,
        rooms: JSON.parse('<?php echo addslashes(json_encode($rooms->map(fn($r) => [
            "id" => $r->id,
            "name" => $r->name,
            "type" => $r->type,
            "created_by" => $r->created_by,
            "latest_message" => $r->latestMessage?->body ?? "Belum ada pesan",
            "latest_time" => $r->latestMessage?->created_at?->format("H:i") ?? "",
        ]))); ?>'),

        init() {
    console.log('ChatApp initialized successfully!');
    
    // Subscribe ke semua room untuk update sidebar
    this.rooms.forEach(room => {
        const channel = window.ReverbPusher.subscribe('room.' + room.id);
        channel.bind('message.sent', (data) => {
            // Update sidebar
            const roomIndex = this.rooms.findIndex(r => r.id === data.room_id);
            if (roomIndex !== -1) {
                this.rooms[roomIndex].latest_message = data.body;
                const r = this.rooms.splice(roomIndex, 1)[0];
                this.rooms.unshift(r);
            }
            
            // Kalau room sedang aktif, tambah pesan ke chat
            if (this.activeRoomId === data.room_id) {
                const exists = this.messages.find(m => m.id === data.id);
                if (!exists) {
                    this.messages.push(data);
                    this.$nextTick(() => this.scrollToBottom());
                }
            }
        });
    });
},
            
        async loadRoom(roomId) {
            this.activeRoomId = roomId;
            const res = await fetch('/rooms/' + roomId);
            const data = await res.json();
            this.activeRoom = data.room;
            this.messages = data.messages;
            this.members = data.members;
            this.$nextTick(() => this.scrollToBottom());

            if (window.ReverbPusher) {
                window.ReverbPusher.unsubscribe('room.' + roomId);
                const channel = window.ReverbPusher.subscribe('room.' + roomId);
                console.log('Subscribed to channel: room.' + roomId);
                channel.bind('message.sent', (data) => {
                    console.log('MESSAGE RECEIVED:', data);
                    const exists = this.messages.find(m => m.id === data.id);
                    if (!exists) {
                        this.messages.push(data);
                        this.$nextTick(() => this.scrollToBottom());
                    }
                    this.updateSidebarPreview(data);
                });
            }
        },

        async sendMessage() {
            if (!this.newMessage.trim() || !this.activeRoomId) return;
            const res = await fetch('/rooms/' + this.activeRoomId + '/messages', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ body: this.newMessage }),
            });
            const message = await res.json();
            this.messages.push(message);
            this.newMessage = '';
            this.updateSidebarPreview(message);
            this.$nextTick(() => this.scrollToBottom());
        },

        async createGroup() {
            if (!this.groupName.trim()) return;
            await fetch('/rooms/group', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ name: this.groupName }),
            });
            this.showGroupModal = false;
            this.groupName = '';
            window.location.reload();
        },

        async createPrivate(userId) {
            await fetch('/rooms/private', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ user_id: userId }),
            });
            this.showPrivateModal = false;
            window.location.reload();
        },

        async addMember(userId) {
            await fetch('/rooms/' + this.activeRoomId + '/members', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
                body: JSON.stringify({ user_id: userId }),
            });
            this.showAddMember = false;
            alert('Member berhasil ditambahkan!');
        },

        async deleteRoom(roomId) {
            if (!confirm('Yakin ingin menghapus chat ini?')) return;
            const res = await fetch('/rooms/' + roomId, {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                },
            });
            const data = await res.json();
            if (res.ok) {
                this.activeRoomId = null;
                this.activeRoom = null;
                this.messages = [];
                this.rooms = this.rooms.filter(r => r.id !== roomId);
            } else {
                alert(data.error || 'Gagal menghapus room');
            }
        },

        updateSidebarPreview(message) {
            const roomId = message.room_id || this.activeRoomId;
            const roomIndex = this.rooms.findIndex(r => r.id === roomId);
    if (roomIndex !== -1) {
        this.rooms[roomIndex].latest_message = message.body;
        this.rooms[roomIndex].latest_time = message.created_at ? new Date(message.created_at).toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' }) : "";
        const room = this.rooms.splice(roomIndex, 1)[0];
        this.rooms.unshift(room);
    }
},
        scrollToBottom() {
            const container = document.getElementById('messages-container');
            if (container) container.scrollTop = container.scrollHeight;
        },

        formatTime(datetime) {
            return new Date(datetime).toLocaleTimeString('id-ID', {
                hour: '2-digit', minute: '2-digit'
            });
        }
    }
}

document.addEventListener('alpine:init', () => {
    Alpine.data('chatApp', () => chatApp());
});
</script>

<script defer src="https://unpkg.com/alpinejs@3.13.0/dist/cdn.min.js"></script>

@endsection