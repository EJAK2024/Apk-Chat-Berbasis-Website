@extends('layouts.app')
@section('content')
<div class="flex h-screen" x-data="chatApp()" x-init="init()">

    {{-- SIDEBAR --}}
    <div class="w-80 bg-white border-r flex flex-col">
        {{-- Header --}}
        <div class="p-4 bg-blue-600 text-white flex justify-between items-center">
            <span class="font-bold text-lg">💬 ChatApp</span>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm bg-white text-blue-600 px-3 py-1 rounded hover:bg-gray-100">
                    Logout
                </button>
            </form>
        </div>

        {{-- Info User --}}
        <div class="p-3 border-b bg-gray-50">
            <p class="text-sm font-semibold">{{ Auth::user()->name }}</p>
            <p class="text-xs text-green-500">● Online</p>
        </div>

        {{-- Tombol buat room --}}
        <div class="p-3 border-b flex gap-2">
            <button @click="showGroupModal=true"
                class="flex-1 bg-blue-500 text-white text-sm py-2 rounded hover:bg-blue-600">
                + Group
            </button>
            <button @click="showPrivateModal=true"
                class="flex-1 bg-green-500 text-white text-sm py-2 rounded hover:bg-green-600">
                + Private
            </button>
        </div>

        {{-- Daftar Room --}}
        <div class="overflow-y-auto flex-1">
            @foreach($rooms as $room)
            <div @click="loadRoom({{ $room->id }})"
                class="p-4 border-b cursor-pointer hover:bg-gray-50"
                :class="activeRoomId == {{ $room->id }} ? 'bg-blue-50 border-l-4 border-l-blue-500' : ''">
                <div class="flex justify-between">
                    <p class="font-semibold text-sm">
                        {{ $room->type === 'group' ? '👥' : '👤' }} {{ $room->name }}
                    </p>
                    <span class="text-xs text-gray-400">
                        {{ $room->type === 'group' ? 'Group' : 'Private' }}
                    </span>
                </div>
                @if($room->latestMessage)
                <p class="text-xs text-gray-500 truncate mt-1">{{ $room->latestMessage->body }}</p>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    {{-- AREA CHAT --}}
    <div class="flex-1 flex flex-col">
        <template x-if="!activeRoomId">
            <div class="flex-1 flex items-center justify-center text-gray-400">
                <div class="text-center">
                    <p class="text-5xl mb-4">💬</p>
                    <p>Pilih room untuk mulai chat</p>
                </div>
            </div>
        </template>

        <template x-if="activeRoomId">
            <div class="flex flex-col h-full">
                {{-- Header room --}}
                <div class="bg-white border-b p-4 flex justify-between items-center shadow-sm">
                    <div>
                        <p class="font-bold" x-text="activeRoom?.name"></p>
                        <p class="text-xs text-gray-500">
                            <span x-text="members.length"></span> anggota
                            <template x-for="m in members.filter(u => u.is_online)">
                                <span class="text-green-500"> · <span x-text="m.name"></span> ●</span>
                            </template>
                        </p>
                    </div>
                    <template x-if="activeRoom?.type === 'group'">
                        <button @click="showAddMember=true"
                            class="text-sm bg-blue-100 text-blue-600 px-3 py-1 rounded">
                            + Member
                        </button>
                    </template>
                </div>

                {{-- Pesan --}}
                <div class="flex-1 overflow-y-auto p-4 space-y-3" id="messages-container">
                    <template x-for="msg in messages" :key="msg.id">
                        <div :class="msg.user_id == {{ Auth::id() }} ? 'flex justify-end' : 'flex justify-start'">
                            <div :class="msg.user_id == {{ Auth::id() }}
                                ? 'bg-blue-500 text-white'
                                : 'bg-white text-gray-800 border'"
                                class="max-w-xs lg:max-w-md rounded-2xl px-4 py-2 shadow-sm">
                                <template x-if="msg.user_id != {{ Auth::id() }}">
                                    <p class="text-xs font-semibold text-blue-600 mb-1" x-text="msg.user?.name"></p>
                                </template>
                                <p class="text-sm" x-text="msg.body"></p>
                                <p class="text-xs mt-1 opacity-60" x-text="formatTime(msg.created_at)"></p>
                            </div>
                        </div>
                    </template>
                </div>

                {{-- Input pesan --}}
                <div class="bg-white border-t p-4">
                    <div class="flex gap-3">
                        <input x-model="newMessage"
                            @keyup.enter="sendMessage()"
                            type="text"
                            placeholder="Ketik pesan..."
                            class="flex-1 border rounded-full px-4 py-2 focus:outline-none focus:border-blue-400">
                        <button @click="sendMessage()"
                            :disabled="!newMessage.trim()"
                            class="bg-blue-500 text-white px-6 py-2 rounded-full hover:bg-blue-600 disabled:opacity-50">
                            Kirim
                        </button>
                    </div>
                </div>
            </div>
        </template>
    </div>

    {{-- MODAL: Buat Group --}}
    <div x-show="showGroupModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 class="font-bold text-lg mb-4">Buat Group Chat</h3>
            <input x-model="groupName" type="text" placeholder="Nama group"
                class="w-full border rounded p-3 mb-4">
            <div class="flex gap-2">
                <button @click="createGroup()"
                    class="flex-1 bg-blue-500 text-white py-2 rounded hover:bg-blue-600">
                    Buat
                </button>
                <button @click="showGroupModal=false"
                    class="flex-1 bg-gray-200 py-2 rounded hover:bg-gray-300">
                    Batal
                </button>
            </div>
        </div>
    </div>

    {{-- MODAL: Private Chat --}}
    <div x-show="showPrivateModal" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 class="font-bold text-lg mb-4">Mulai Private Chat</h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($users as $user)
                <div @click="createPrivate({{ $user->id }})"
                    class="flex items-center gap-3 p-3 rounded cursor-pointer hover:bg-gray-50 border">
                    <div class="w-8 h-8 bg-blue-400 rounded-full flex items-center justify-center text-white text-sm font-bold">
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
                class="w-full mt-4 bg-gray-200 py-2 rounded hover:bg-gray-300">
                Batal
            </button>
        </div>
    </div>

    {{-- MODAL: Add Member --}}
    <div x-show="showAddMember" class="fixed inset-0 bg-black bg-opacity-40 flex items-center justify-center z-50">
        <div class="bg-white rounded-xl p-6 w-96">
            <h3 class="font-bold text-lg mb-4">Tambah Member</h3>
            <div class="space-y-2 max-h-60 overflow-y-auto">
                @foreach($users as $user)
                <div @click="addMember({{ $user->id }})"
                    class="flex items-center gap-3 p-3 rounded cursor-pointer hover:bg-gray-50 border">
                    <div class="w-8 h-8 bg-green-400 rounded-full flex items-center justify-center text-white text-sm font-bold">
                        {{ substr($user->name, 0, 1) }}
                    </div>
                    <p class="font-medium text-sm">{{ $user->name }}</p>
                </div>
                @endforeach
            </div>
            <button @click="showAddMember=false"
                class="w-full mt-4 bg-gray-200 py-2 rounded hover:bg-gray-300">
                Tutup
            </button>
        </div>
    </div>
</div>
@endsection