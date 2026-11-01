import { defineStore } from 'pinia';
import { computed, ref } from 'vue';
import axios from 'axios';
import { useSettingsStore } from './settings';

export const useCartStore = defineStore('cart', () => {
    const items = ref([]);
    const deliveryType = ref('delivery');
    const settings = useSettingsStore();

    const itemCount = computed(() =>
        items.value.reduce((sum, item) => sum + item.quantity, 0)
    );

    const subtotal = computed(() =>
        items.value.reduce((sum, item) => sum + item.price * item.quantity, 0)
    );

    const isDelivery = computed(() => deliveryType.value === 'delivery');

    const isDeliveryAvailable = computed(() =>
        subtotal.value >= settings.delivery.min_order_amount
    );

    const deliveryFee = computed(() => {
        if (!isDelivery.value) return 0;
        if (subtotal.value >= settings.delivery.free_threshold) return 0;
        return settings.delivery.default_fee;
    });

    const total = computed(() => subtotal.value + deliveryFee.value);

    // ✅ Détecte si un article dépasse le stock connu
    const hasStockIssue = computed(() =>
        items.value.some((i) => i.quantity > (i.stock_quantity ?? 0))
    );

    const canCheckout = computed(() => {
        if (items.value.length === 0) return false;
        if (hasStockIssue.value) return false;
        if (isDelivery.value && !isDeliveryAvailable.value) return false;
        return true;
    });

    const missingForDelivery = computed(() => {
        if (!isDelivery.value) return 0;
        return Math.max(0, settings.delivery.min_order_amount - subtotal.value);
    });

    const missingForFreeDelivery = computed(() => {
        if (!isDelivery.value) return 0;
        if (subtotal.value >= settings.delivery.free_threshold) return 0;
        return Math.max(0, settings.delivery.free_threshold - subtotal.value);
    });

    /**
     * Ajoute un produit en plafonnant au stock disponible.
     * @returns {number} 0 = ajout complet, 1+ = quantité plafonnée (info à afficher)
     */
    function addItem(product, quantity = 1) {
        const existing = items.value.find((i) => i.id === product.id);
        const maxStock = Number(product.stock_quantity ?? 0);

        if (maxStock <= 0) return quantity; // tout refusé

        if (existing) {
            const current = existing.quantity;
            const desired = current + quantity;

            if (desired > maxStock) {
                existing.quantity = maxStock;
                existing.stock_quantity = maxStock;
                // Retourne combien d'unités ont été refusées
                return desired - maxStock;
            }

            existing.quantity = desired;
            existing.stock_quantity = maxStock;
            return 0;
        }

        const accepted = Math.min(quantity, maxStock);
        items.value.push({
            id: product.id,
            name: product.name,
            slug: product.slug,
            price: parseFloat(product.price),
            image: product.image_path,
            type: product.type,
            stock_quantity: maxStock,
            quantity: accepted,
        });
        return quantity - accepted;
    }

    function removeItem(productId) {
        items.value = items.value.filter((i) => i.id !== productId);
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find((i) => i.id === productId);
        if (!item) return;

        if (quantity <= 0) {
            removeItem(productId);
            return;
        }

        item.quantity = Math.min(quantity, item.stock_quantity ?? 1);
    }

    /**
     * Recharge le stock réel depuis l'API et ajuste le panier.
     * CORRIGÉ : utilise un snapshot pour éviter le bug d'index après removeItem().
     * @returns {Promise<Array<{name, type, newQty?}>>} changements pour afficher un message
     */
    async function syncStock() {
        if (items.value.length === 0) return [];

        // ✅ Snapshot AVANT toute mutation
        const snapshot = items.value.map((i) => ({ id: i.id, slug: i.slug, name: i.name }));

        const results = await Promise.allSettled(
            snapshot.map((i) => axios.get(`/api/products/${i.slug}`))
        );

        const changes = [];

        results.forEach((result, index) => {
            const original = snapshot[index];
            const item = items.value.find((i) => i.id === original.id);
            if (!item) return; // déjà retiré

            if (result.status === 'rejected') {
                removeItem(item.id);
                changes.push({ name: original.name, type: 'removed' });
                return;
            }

            const realStock = Number(result.value.data.stock_quantity ?? 0);

            if (realStock === 0) {
                removeItem(item.id);
                changes.push({ name: original.name, type: 'out_of_stock' });
                return;
            }

            if (item.quantity > realStock) {
                item.quantity = realStock;
                item.stock_quantity = realStock;
                changes.push({ name: original.name, type: 'reduced', newQty: realStock });
            } else {
                item.stock_quantity = realStock;
            }
        });

        return changes;
    }

    function setDeliveryType(type) {
        deliveryType.value = type;
    }

    function clear() {
        items.value = [];
        deliveryType.value = 'delivery';
    }

    async function submitOrder(customer) {
        const payload = {
            customer_name: customer.name,
            customer_email: customer.email,
            customer_phone: customer.phone,
            delivery_type: deliveryType.value,
            delivery_address: customer.address || null,
            delivery_city: customer.city || null,
            delivery_postal_code: customer.postalCode || null,
            delivery_slot_id: customer.slotId || null,
            notes: customer.notes || null,
            items: items.value.map((i) => ({
                product_id: i.id,
                quantity: i.quantity,
            })),
        };

        const { data } = await axios.post('/api/orders', payload);
        return data.order;
    }

    return {
        items, deliveryType,
        itemCount, subtotal, deliveryFee, total,
        isDelivery, isDeliveryAvailable, canCheckout, hasStockIssue,
        missingForDelivery, missingForFreeDelivery,
        addItem, removeItem, updateQuantity, setDeliveryType, clear,
        syncStock, submitOrder,
    };
}, {
    persist: true,
});