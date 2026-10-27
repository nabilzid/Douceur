import { defineStore } from 'pinia';
import { ref } from 'vue';
import axios from 'axios';

export const useSettingsStore = defineStore('settings', () => {
    const delivery = ref({
        min_order_amount: 15,
        free_threshold: 30,
        default_fee: 5,
    });
    const loaded = ref(false);

    async function fetchDelivery() {
        try {
            const { data } = await axios.get('/api/settings/delivery');
            delivery.value = data;
        } catch {
            // garder les valeurs par défaut
        } finally {
            loaded.value = true;
        }
    }

    return { delivery, loaded, fetchDelivery };
});