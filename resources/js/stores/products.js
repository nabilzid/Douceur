import { defineStore } from 'pinia';
import { computed, ref } from 'vue';

export const useProductsStore = defineStore('products', () => {
    const products = ref([]);
    const types = ref([]);
    const loading = ref(false);
    const error = ref(null);

    async function fetchProducts() {
        loading.value = true;
        error.value = null;
        try {
            const res = await fetch('/api/products');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            products.value = await res.json();
        } catch (e) {
            error.value = e.message;
        } finally {
            loading.value = false;
        }
    }

    async function fetchTypes() {
        try {
            const res = await fetch('/api/product-types');
            if (!res.ok) throw new Error(`HTTP ${res.status}`);
            types.value = await res.json();
        } catch (e) {
            console.error('Erreur types:', e);
        }
    }

    const byType = computed(() => (slug) => {
        if (!slug) return products.value;
        return products.value.filter((p) => p.type?.slug === slug);
    });

    return { products, types, loading, error, fetchProducts, fetchTypes, byType };
});