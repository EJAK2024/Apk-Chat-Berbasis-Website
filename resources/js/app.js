import './bootstrap';

window.Alpine = Alpine;
Alpine.start();

// Fungsi chatApp untuk Alpine.js
window.chatApp = function () {
    return {
        // State
        activeRoomId: null,
        activeRoom: null,
        messages: [],
        members: [],
        newMessage: '',
        groupName: '',
        showGroupModal: false,
        showPrivateModal: false,
        showAddMember: false,
        echoChannel: null,

        init() {
            // Listen user presence global
            window.Echo.channel('presence')
                .listen('UserPresenceUpdated', (e) => {
                    this.members = this.members.map(m => {
                        if (m.id === e.user_id) m.is_online = e.status === 'online';
                        return m;
                    });
                });
        },

        async loadRoom(roomId) {
            // Unsubscribe dari channel lama
            if (this.echoChannel) {
                window.Echo.leave('room.' + this.activeRoomId);
            }

            this.activeRoomId = roomId;

            const res  = await fetch(`/rooms/${roomId}`);
            const data = await res.json();

            this.activeRoom = data.room;
            this.messages   = data.messages;
            this.members    = data.members;

            // Subscribe ke channel room baru
            this.echoChannel = window.Echo.join('room.' + roomId)
                .listen('MessageSent', (e) => {
                    this.messages.push(e);
                    this.$nextTick(() => this.scrollToBottom());
                });

            this.$nextTick(() => this.scrollToBottom());
        },

        async sendMessage() {
            if (!this.newMessage.trim() || !this.activeRoomId) return;

            const res = await fetch(`/rooms/${this.activeRoomId}/messages`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ body: this.newMessage }),
            });

            const message  = await res.json();
            this.messages.push(message);
            this.newMessage = '';
            this.$nextTick(() => this.scrollToBottom());
        },

        async createGroup() {
            if (!this.groupName.trim()) return;

            const res  = await fetch('/rooms/group', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ name: this.groupName }),
            });

            const room = await res.json();
            this.showGroupModal = false;
            this.groupName      = '';
            window.location.reload(); // Reload untuk update sidebar
        },

        async createPrivate(userId) {
            const res  = await fetch('/rooms/private', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ user_id: userId }),
            });

            const room = await res.json();
            this.showPrivateModal = false;
            window.location.reload();
        },

        async addMember(userId) {
            await fetch(`/rooms/${this.activeRoomId}/members`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ user_id: userId }),
            });
            this.showAddMember = false;
            alert('Member berhasil ditambahkan!');
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