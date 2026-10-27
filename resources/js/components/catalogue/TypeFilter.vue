<template>
    <div class="filters">
        <button
            :class="['filter-chip', { active: !modelValue }]"
            @click="$emit('update:modelValue', null)"
        >
            Tout
        </button>
        <button
            v-for="type in types"
            :key="type.id"
            :class="['filter-chip', { active: modelValue === type.slug }]"
            @click="$emit('update:modelValue', type.slug)"
        >
            <span class="chip-icon">{{ type.icon || '🍮' }}</span>
            {{ type.name }}
        </button>
    </div>
</template>

<script setup>
defineProps({
    types: { type: Array, default: () => [] },
    modelValue: { type: String, default: null },
});
defineEmits(['update:modelValue']);
</script>

<style scoped>
.filters {
    display: flex;
    flex-wrap: wrap;
    gap: 0.75rem;
    justify-content: center;
    margin-bottom: 3rem;
}

.filter-chip {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.6rem 1.2rem;
    border-radius: 999px;
    background: white;
    color: var(--chocolate-soft);
    border: 1.5px solid rgba(212, 165, 116, 0.3);
    font-weight: 500;
    font-size: 0.9rem;
    transition: all 0.25s ease;
}

.filter-chip:hover {
    border-color: var(--caramel-light);
    color: var(--caramel-dark);
    transform: translateY(-1px);
}

.filter-chip.active {
    background: var(--caramel);
    color: white;
    border-color: var(--caramel);
    box-shadow: 0 4px 12px rgba(201, 123, 63, 0.3);
}

.chip-icon {
    font-size: 1rem;
}
</style>