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

    const canCheckout = computed(() => {
        if (items.value.length === 0) return false;
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



    function addItem(product, quantity = 1) {
        const existing = items.value.find((i) => i.id === product.id);
        if (existing) {
            existing.quantity += quantity;
        } else {
            items.value.push({
                id: product.id,
                name: product.name,
                slug: product.slug,
                price: parseFloat(product.price),
                image: product.image_path,
                type: product.type,
                stock_quantity: product.stock_quantity,
                quantity,
            });
        }
    }

    function removeItem(productId) {
        items.value = items.value.filter((i) => i.id !== productId);
    }

    function updateQuantity(productId, quantity) {
        const item = items.value.find((i) => i.id === productId);
        if (!item) return;
        if (quantity <= 0) {
            removeItem(productId);
        } else {
            item.quantity = Math.min(quantity, item.stock_quantity);
        }
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
        isDelivery, isDeliveryAvailable, canCheckout,
        missingForDelivery, missingForFreeDelivery,
        addItem, removeItem, updateQuantity, setDeliveryType, clear,
        submitOrder,
    };
}, {
    persist: true,
});