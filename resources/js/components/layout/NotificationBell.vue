<template>
    <div ref="wrapperRef" class="notif-wrapper">
        <button
            class="notif-btn"
            type="button"
            aria-label="Notifications"
            :aria-expanded="open"
            @click="toggle"
        >
            <span class="notif-icon" aria-hidden="true">🔔</span>
            <span
                v-if="unreadCount > 0"
                class="notif-badge"
                :aria-label="`${unreadCount} notification(s) non lue(s)`"
            >
                {{ unreadCount > 99 ? '99+' : unreadCount }}
            </span>
        </button>

        <Transition name="dropdown">
            <div v-if="open" class="notif-dropdown" role="region" aria-label="Notifications">
                <div class="notif-head">
                    <h3>Notifications</h3>

                    <div class="notif-head-actions">
                        <button
                            type="button"
                            class="sound-toggle"
                            :aria-pressed="soundEnabled"
                            :aria-label="soundEnabled ? 'Couper le son' : 'Activer le son'"
                            :title="soundEnabled ? 'Couper le son' : 'Activer le son'"
                            @click.stop="toggleSound"
                        >
                            {{ soundEnabled ? '🔊' : '🔇' }}
                        </button>

                        <button
                            v-if="unreadCount > 0"
                            type="button"
                            class="mark-all"
                            @click="markAllRead"
                        >
                            Tout marquer comme lu
                        </button>
                    </div>
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
                        <span class="notif-item-icon" aria-hidden="true">
                            {{ n.data?.icon || '🔔' }}
                        </span>
                        <div class="notif-item-body">
                            <strong>{{ n.data?.title }}</strong>
                            <p>{{ n.data?.message }}</p>
                            <span class="notif-time">{{ timeAgo(n.created_at) }}</span>
                        </div>
                        <span v-if="!n.read_at" class="unread-dot" aria-hidden="true"></span>
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
import { useEcho } from '@laravel/echo-vue';

/* ─────────────────────────────────────────────────────────────
   Constantes
   ───────────────────────────────────────────────────────────── */

/** Nom du canal Reverb pour les notifications admin. */
const CHANNEL = 'admin.notifications';

/** Nom de l'événement broadcasté par Laravel. */
const ECHO_EVENT = '.notification.created';

/** Nombre maximum de notifications conservées en mémoire. */
const MAX_NOTIFICATIONS = 50;

/** Intervalle de répétition du bip (ms). */
const SOUND_REPEAT_INTERVAL = 10_000;

/** Volume du bip (0.0 à 1.0). */
const SOUND_VOLUME = 0.25;

/** Clé localStorage pour mémoriser l'état du son. */
const SOUND_ENABLED_KEY = 'notif-sound-enabled';

/**
 * Mapping type métier → CustomEvent DOM à émettre.
 */
const EVENT_BY_TYPE = {
    new_order: 'new-order-received',
    new_order_pending: 'new-order-received',
    order_paid: 'order-paid',
    order_cancelled: 'order-cancelled',
    new_custom_order: 'new-custom-order-received',
    custom_order_paid: 'custom-order-paid',
    custom_order_completed: 'custom-order-completed',
    stock_updated: 'stock-updated',   // ← AJOUT
    product_updated: 'product-updated',   // ← AJOUT
};

/* ─────────────────────────────────────────────────────────────
   État
   ───────────────────────────────────────────────────────────── */

const router = useRouter();

const notifications = ref([]);
const loading = ref(true);
const open = ref(false);
const wrapperRef = ref(null);

const soundEnabled = ref(
    typeof localStorage !== 'undefined'
        ? localStorage.getItem(SOUND_ENABLED_KEY) !== 'off'
        : true
);

/* ─────────────────────────────────────────────────────────────
   Computed
   ───────────────────────────────────────────────────────────── */

const unreadCount = computed(
    () => notifications.value.filter((n) => !n.read_at).length
);

/* ─────────────────────────────────────────────────────────────
   Helpers
   ───────────────────────────────────────────────────────────── */

function normalizeNotification(n) {
    return {
        ...n,
        data: n.data ?? {},
        read_at: n.read_at ?? null,
        created_at: n.created_at ?? new Date().toISOString(),
    };
}

function isRealtimeId(id) {
    return String(id).startsWith('realtime-');
}

function timeAgo(date) {
    const diff = Math.floor((Date.now() - new Date(date).getTime()) / 1000);
    if (diff < 60) return 'À l’instant';
    if (diff < 3600) return `Il y a ${Math.floor(diff / 60)} min`;
    if (diff < 86400) return `Il y a ${Math.floor(diff / 3600)} h`;
    return `Il y a ${Math.floor(diff / 86400)} j`;
}

/* ─────────────────────────────────────────────────────────────
   Audio — bip Uber Eats style (Web Audio API)
   ───────────────────────────────────────────────────────────── */

let audioContext = null;
let soundRepeatTimer = null;

function getAudioContext() {
    if (typeof window === 'undefined') return null;
    if (!audioContext) {
        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return null;
        audioContext = new Ctx();
    }
    if (audioContext.state === 'suspended') {
        audioContext.resume().catch(() => {});
    }
    return audioContext;
}

/**
 * Bip 3 tons montants — durée ≈ 0.6 s.
 */
function playBeep() {
    if (!soundEnabled.value) return;

    const ctx = getAudioContext();
    if (!ctx) return;

    const now = ctx.currentTime;

    const tones = [
        { freq: 800, start: 0.00, end: 0.18 },
        { freq: 1000, start: 0.20, end: 0.38 },
        { freq: 1200, start: 0.40, end: 0.60 },
    ];

    for (const t of tones) {
        const osc = ctx.createOscillator();
        const gain = ctx.createGain();

        osc.type = 'sine';
        osc.frequency.setValueAtTime(t.freq, now + t.start);

        gain.gain.setValueAtTime(0, now + t.start);
        gain.gain.linearRampToValueAtTime(SOUND_VOLUME, now + t.start + 0.02);
        gain.gain.linearRampToValueAtTime(0, now + t.end);

        osc.connect(gain);
        gain.connect(ctx.destination);
        osc.start(now + t.start);
        osc.stop(now + t.end);
    }
}

function startSoundLoop() {
    if (!soundEnabled.value) return;
    if (soundRepeatTimer) return;

    playBeep();

    soundRepeatTimer = setInterval(() => {
        if (unreadCount.value === 0) {
            stopSoundLoop();
            return;
        }
        playBeep();
    }, SOUND_REPEAT_INTERVAL);
}

function stopSoundLoop() {
    if (soundRepeatTimer) {
        clearInterval(soundRepeatTimer);
        soundRepeatTimer = null;
    }
}

function toggleSound() {
    soundEnabled.value = !soundEnabled.value;

    if (typeof localStorage !== 'undefined') {
        localStorage.setItem(
            SOUND_ENABLED_KEY,
            soundEnabled.value ? 'on' : 'off'
        );
    }

    if (soundEnabled.value && unreadCount.value > 0) {
        startSoundLoop();
    } else if (!soundEnabled.value) {
        stopSoundLoop();
    }
}

/* ─────────────────────────────────────────────────────────────
   Chargement API
   ───────────────────────────────────────────────────────────── */

async function load() {
    try {
        const { data } = await axios.get('/api/notifications');
        const list = Array.isArray(data)
            ? data
            : Array.isArray(data?.data)
                ? data.data
                : [];
        notifications.value = list.map(normalizeNotification);
    } catch {
        // silencieux
    } finally {
        loading.value = false;
    }
}

/* ─────────────────────────────────────────────────────────────
   Mutations locales
   ───────────────────────────────────────────────────────────── */

function addNotification(notification) {
    const normalized = normalizeNotification(notification);

    const exists = notifications.value.some(
        (n) => String(n.id) === String(normalized.id)
    );
    if (exists) return;

    notifications.value = [normalized, ...notifications.value].slice(
        0,
        MAX_NOTIFICATIONS
    );
}

async function markAllRead() {
    try {
        await axios.put('/api/notifications/read-all');
        const now = new Date().toISOString();
        notifications.value = notifications.value.map((n) => ({
            ...n,
            read_at: n.read_at ?? now,
        }));

        // Toutes lues → arrêt immédiat du bip
        stopSoundLoop();
    } catch {
        // silencieux
    }
}

/* ─────────────────────────────────────────────────────────────
   Interactions
   ───────────────────────────────────────────────────────────── */

async function handleClick(notification) {
    if (isRealtimeId(notification.id)) {
        await load();

        const real = notifications.value.find(
            (n) =>
                !isRealtimeId(n.id) &&
                n.data?.title === notification.data?.title &&
                !n.read_at
        );

        if (real) {
            try {
                await axios.put(`/api/notifications/${real.id}/read`);
                real.read_at = new Date().toISOString();
            } catch {
                /* silencieux */
            }
        }
    } else if (!notification.read_at) {
        try {
            await axios.put(`/api/notifications/${notification.id}/read`);
            notification.read_at = new Date().toISOString();
        } catch {
            /* silencieux */
        }
    }

    // Arrêt du bip si plus rien de non lu
    if (unreadCount.value === 0) {
        stopSoundLoop();
    }

    // Redirection
    if (notification.data?.url) {
        window.dispatchEvent(
            new CustomEvent('notification-clicked', {
                detail: { url: notification.data.url },
            })
        );
        router.push(notification.data.url);
        open.value = false;
    }
}

function toggle() {
    open.value = !open.value;
    if (open.value && notifications.value.length === 0) {
        load();
    }
}

function handleClickOutside(event) {
    if (wrapperRef.value && !wrapperRef.value.contains(event.target)) {
        open.value = false;
    }
}

/* ─────────────────────────────────────────────────────────────
   Temps réel — Reverb
   ───────────────────────────────────────────────────────────── */

function parseRealtimePayload(rawPayload) {
    const payload = rawPayload?.payload ?? rawPayload ?? {};

    const notification = {
        id: payload.id ?? `realtime-${Date.now()}`,
        data: payload.data ?? payload,
        read_at: null,
        created_at: payload.created_at ?? new Date().toISOString(),
    };

    const type = payload.type ?? payload.data?.type;

    return { notification, type };
}

useEcho(CHANNEL, ECHO_EVENT, (rawPayload) => {
    const { notification, type } = parseRealtimePayload(rawPayload);

    addNotification(notification);

    // 🔊 Bip temps réel + relance la boucle si nécessaire
    if (unreadCount.value > 0) {
        if (soundRepeatTimer) {
            playBeep();
        } else {
            startSoundLoop();
        }
    }

    const eventName = type ? EVENT_BY_TYPE[type] : null;
    if (eventName) {
        window.dispatchEvent(
            new CustomEvent(eventName, {
                detail: { notification, type },
            })
        );
    }
}).listen();

/* ─────────────────────────────────────────────────────────────
   Cycle de vie
   ───────────────────────────────────────────────────────────── */

onMounted(() => {
    load();
    document.addEventListener('click', handleClickOutside);

    // Débloque l'AudioContext au premier geste utilisateur
    const unlockAudio = () => {
        getAudioContext();
        window.removeEventListener('pointerdown', unlockAudio);
        window.removeEventListener('keydown', unlockAudio);
    };
    window.addEventListener('pointerdown', unlockAudio);
    window.addEventListener('keydown', unlockAudio);

    // Si des non-lues existent déjà (rechargement), on relance après le load
    setTimeout(() => {
        if (unreadCount.value > 0 && soundEnabled.value) {
            startSoundLoop();
        }
    }, 1500);
});

onBeforeUnmount(() => {
    document.removeEventListener('click', handleClickOutside);

    stopSoundLoop();
    if (audioContext && audioContext.state !== 'closed') {
        audioContext.close().catch(() => {});
        audioContext = null;
    }
});
</script>

<style scoped>
.notif-wrapper { position: relative; }

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
.notif-icon { font-size: 1.3rem; }

.notif-badge {
    position: absolute;
    top: -2px;
    right: -2px;
    background: #e63950;
    color: white;
    border-radius: 999px;
    padding: 0.1rem 0.4rem;
    font-size: 0.7rem;
    font-weight: 700;
    min-width: 18px;
    text-align: center;
    border: 2px solid #fdf6ec;
}

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
.notif-head h3 { font-size: 1rem; margin: 0; color: var(--chocolate); }

.notif-head-actions {
    display: flex;
    align-items: center;
    gap: 0.5rem;
}

.sound-toggle {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    border-radius: 50%;
    font-size: 1rem;
    background: transparent;
    transition: background 0.15s;
    cursor: pointer;
}
.sound-toggle:hover { background: var(--cream); }
.sound-toggle[aria-pressed="false"] { opacity: 0.6; }

.mark-all {
    font-size: 0.75rem;
    color: var(--caramel);
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: var(--radius-sm);
    transition: background 0.2s;
}
.mark-all:hover { background: var(--cream); }

.notif-state {
    padding: 2rem 1rem;
    text-align: center;
    color: var(--text-muted);
    font-size: 0.9rem;
}

.notif-list { list-style: none; padding: 0; margin: 0; }

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
.notif-item:hover { background: var(--cream); }
.notif-item.unread {
    background: rgba(201, 123, 63, 0.20);
    border-left: 3px solid var(--caramel);
}
.notif-item.unread:hover {
    background: rgba(201, 123, 63, 0.28);
}
.notif-item:last-child { border-bottom: none; }

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
.notif-item-body { flex: 1; min-width: 0; }
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
    .notif-dropdown { width: 320px; }
}
</style>