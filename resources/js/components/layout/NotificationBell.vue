<template>
    <div class="notif-wrapper" ref="wrapperRef">
        <button class="notif-btn" @click="toggle" aria-label="Notifications">
            <span class="notif-icon">🔔</span>
            <span v-if="unreadCount > 0" class="notif-badge">{{ unreadCount }}</span>
        </button>

        <Transition name="dropdown">
            <div v-if="open" class="notif-dropdown">
                <div class="notif-head">
                    <h3>Notifications</h3>
                    <button
                        v-if="unreadCount > 0"
                        class="mark-all"
                        @click="markAllRead"
                    >
                        Tout marquer comme lu
                    </button>
                </div>

                <div v-if="loading" class="notif-state">Chargement…</div>
                <div v-else-if="notifications.length === 0" class="notif-state">
                    Aucune notification.
                </div>

                <ul v-else class="notif-list">
                    <li
                        v-for="n in notifications"
                        :key="n.id"
                        :class="['notif-item', { unread: !n.read_at }]"
                        @click="handleClick(n)"
                    >
                        <span class="notif-item-icon">{{ n.data.icon || '🔔' }}</span>
                        <div class="notif-item-body">
                            <strong>{{ n.data.title }}</strong>
                            <p>{{ n.data.message }}</p>
                            <span class="notif-time">{{ timeAgo(n.created_at) }}</span>
                        </div>
                        <span v-if="!n.read_at" class="unread-dot"></span>
                    </li>
                </ul>
            </div>
        </Transition>
    </div>
</template>

<script setup>
import { computed, onBeforeUnmount, onMounted, ref } from 'vue';
import { useRouter } from 'vue-router';
import axios from 'axios';

const router = useRouter();
const notifications = ref([]);
const loading = ref(true);
const open = ref(false);
const wrapperRef = ref(null);

const unreadCount = computed(() =>
    notifications.value.filter((n) => !n.read_at).length
);
let pollInterval = null;
async function load() {
    try {
        const { data } = await axios.get('/api/notifications');
        notifications.value = data;
    } catch {
        notifications.value = [];
    } finally {
        loading.value = false;
    }
}

async function markAllRead() {
    try {
        await axios.put('/api/notifications/read-all');
        notifications.value.forEach((n) => {
            if (!n.read_at) n.read_at = new Date().toISOString();
        });
    } catch {}
}

async function handleClick(notification) {
    if (!notification.read_at) {
        try {
            await axios.put(`/api/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch {}
    }

    if (notification.data.url) {
        // ✅ Émettre un événement global
        window.dispatchEvent(new CustomEvent('notification-clicked', {
            detail: { url: notification.data.url }
        }));
        router.push(notification.data.url);
        open.value = false;
    }
}

function toggle() {
    open.value = !open.value;
    if (open.value) load();
}

function handleClickOutside(event) {
    if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
        open.value = false;
    }
}

function timeAgo(date) {
    const diff = Math.floor((Date.now() - new Date(date).getTime()) / 1000);
    if (diff < 60) return 'À l’instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    return `Il y a ${Math.floor(diff / 86400)} j`;
}


onMounted(() => {
    load();
    document.addEventListener('click', handleClickOutside);
    // Polling toutes les 30 secondes
    pollInterval = setInterval(load, 15*60*1000);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);
    if (pollInterval) clearInterval(pollInterval);
});
</script>

<style scoped>
.notif-wrapper {
    position: relative;
}

.notif-btn {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 44px;
    height: 44px;
    border-radius: 50%;
    background: white;
    box-shadow: var(--shadow-sm);
    transition: all 0.2s;
    color: var(--chocolate);
}

.notif-btn:hover {
    background: var(--cream);
    transform: translateY(-1px);
    box-shadow: var(--shadow-md);
}

.notif-icon {
    font-size: 1.3rem;
}

.notif-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: var(--raspberry);
    color: white;
    border-radius: 999px;
    padding: 0.1rem 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    border: 2px solid var(--cream);
}

/* Dropdown */
.notif-dropdown {
    position: absolute;
    top: calc(100% + 8px);
    right: 0;
    width: 380px;
    max-width: 90vw;
    max-height: 500px;
    overflow-y: auto;
    background: white;
    border-radius: var(--radius);
    box-shadow: var(--shadow-lg);
    border: 1px solid var(--cream-dark);
    z-index: 100;
}

.notif-head {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--cream-dark);
    position: sticky;
    top: 0;
    background: white;
    z-index: 1;
}

.notif-head h3 {
    font-size: 1rem;
    margin: 0;
    color: var(--chocolate);
}

.mark-all {
    font-size: 0.75rem;
    color: var(--caramel);
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    transition: background 0.2s;
}

.mark-all:hover {
    background: var(--cream);
}

.notif-state {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.notif-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.notif-item {
    display: flex;
    align-items: flex-start;
    gap: 0.75rem;
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--cream-dark);
    cursor: pointer;
    transition: background 0.15s;
    position: relative;
}

.notif-item:hover {
    background: var(--cream);
}

.notif-item.unread {
    background: rgba(201, 123, 63, 0.04);
}

.notif-item:last-child {
    border-bottom: none;
}

.notif-item-icon {
    font-size: 1.25rem;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--cream);
    border-radius: 50%;
    flex-shrink: 0;
}

.notif-item-body {
    flex: 1;
    min-width: 0;
}

.notif-item-body strong {
    display: block;
    font-size: 0.85rem;
    color: var(--chocolate);
    margin-bottom: 0.15rem;
}

.notif-item-body p {
    margin: 0;
    font-size: 0.8rem;
    color: var(--text-muted);
    line-height: 1.4;
}

.notif-time {
    font-size: 0.7rem;
    color: var(--text-muted);
    margin-top: 0.25rem;
    display: block;
}

.unread-dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--caramel);
    flex-shrink: 0;
    margin-top: 0.5rem;
}

/* Transition */
.dropdown-enter-active,
.dropdown-leave-active {
    transition: opacity 0.15s ease, transform 0.15s ease;
}

.dropdown-enter-from,
.dropdown-leave-to {
    opacity: 0;
    transform: translateY(-6px);
}

@media (max-width: 600px) {
    .notif-dropdown {
        width: 320px;
    }
}
</style>