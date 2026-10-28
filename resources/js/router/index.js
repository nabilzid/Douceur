import { createRouter, createWebHistory } from 'vue-router';

const routes = [
    { path: '/', name: 'home', component: () => import('../pages/HomePage.vue') },
    { path: '/catalogue', name: 'catalogue', component: () => import('../pages/CataloguePage.vue') },
    { path: '/produit/:slug', name: 'product', component: () => import('../pages/ProductPage.vue') },
    { path: '/panier', name: 'cart', component: () => import('../pages/CartPage.vue') },
    { path: '/commande', name: 'checkout', component: () => import('../pages/CheckoutPage.vue') },
    { path: '/commande/succes', name: 'payment-success', component: () => import('../pages/PaymentSuccessPage.vue') },
    { path: '/commande/annule', name: 'payment-cancel', component: () => import('../pages/PaymentCancelPage.vue') },

    { path: '/connexion', name: 'login', component: () => import('../pages/LoginPage.vue') },
    { path: '/inscription', name: 'register', component: () => import('../pages/RegisterPage.vue') },

    { path: '/mon-compte', name: 'account', component: () => import('../pages/AccountPage.vue'), meta: { requiresAuth: true } },
    { path: '/mes-commandes', name: 'my-orders', component: () => import('../pages/MyOrdersPage.vue'), meta: { requiresAuth: true } },
    { path: '/mes-demandes', name: 'my-custom-orders', component: () => import('../pages/MyCustomOrdersPage.vue'), meta: { requiresAuth: true } },

    { path: '/evenements', name: 'events', component: () => import('../pages/EventPackagesPage.vue') },
    { path: '/evenements/:slug/demande', name: 'event-request', component: () => import('../pages/EventRequestPage.vue') },

    // ✅ Suivi de commande standard
    { path: '/suivi-commande', name: 'track-order', component: () => import('../pages/TrackOrderPage.vue') },

    // ✅ Lien signé email commande standard : /commande/CMD-xxx?expires=&signature=
    // Utilise le même composant que /suivi-commande, qui lit le :reference
    {
        path: '/commande/:reference',
        name: 'track-order-signed',
        component: () => import('../pages/TrackOrderPage.vue'),
    },

    // ✅ Suivi de demande sur mesure
    { path: '/suivi-demande', name: 'track-custom-order', component: () => import('../pages/TrackCustomOrderPage.vue') },

    // ✅ Lien signé email demande sur mesure : /demande/SUR-xxx?expires=&signature=
    // Utilise le même composant que /suivi-demande, qui lit le :reference
    {
        path: '/demande/:reference',
        name: 'track-custom-order-signed',
        component: () => import('../pages/TrackCustomOrderPage.vue'),
    },

    // ✅ Un seul groupe /admin
    {
        path: '/admin',
        component: () => import('../pages/admin/AdminLayout.vue'),
        meta: { requiresAuth: true, requiresMerchant: true },
        children: [
            { path: '', redirect: { name: 'admin-dashboard' } },
            { path: 'dashboard', name: 'admin-dashboard', component: () => import('../pages/admin/DashboardPage.vue') },
            { path: 'produits', name: 'admin-products', component: () => import('../pages/admin/ProductsPage.vue') },
            { path: 'commandes', name: 'admin-orders', component: () => import('../pages/admin/OrdersPage.vue') },
            { path: 'sur-mesure', name: 'admin-custom-orders', component: () => import('../pages/admin/CustomOrdersPage.vue') },
            { path: 'parametres', name: 'admin-settings', component: () => import('../pages/admin/SettingsPage.vue'), meta: { requiresAdmin: true } },
            { path: 'utilisateurs', name: 'admin-users', component: () => import('../pages/admin/UsersPage.vue'), meta: { requiresAdmin: true } },
        ],
    },

    { path: '/:pathMatch(.*)*', name: 'not-found', component: () => import('../pages/NotFoundPage.vue') },
];

const router = createRouter({
    history: createWebHistory(),
    routes,
    scrollBehavior() {
        return { top: 0 };
    },
});

router.beforeEach(async (to) => {
    const { useAuthStore } = await import('../stores/auth');
    const auth = useAuthStore();
    if (!auth.user) await auth.fetchUser();

    if (to.meta.requiresAuth && !auth.isAuthenticated) {
        return { name: 'login', query: { redirect: to.fullPath } };
    }

    if (to.meta.requiresMerchant && !['merchant', 'admin'].includes(auth.user?.role)) {
        return { name: 'home' };
    }

    if (to.meta.requiresAdmin && auth.user?.role !== 'admin') {
        return { name: 'admin-dashboard' };
    }
});

export default router;