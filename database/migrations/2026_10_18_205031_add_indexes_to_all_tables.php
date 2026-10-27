<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // USERS
        Schema::table('users', function (Blueprint $table) {
            $table->index('role');
            $table->index('is_active');
            $table->index('phone');
        });

        // ADDRESSES
        Schema::table('addresses', function (Blueprint $table) {
            $table->index('postal_code');
            $table->index('city');
            $table->index('type');
        });

        // MERCHANTS
        Schema::table('merchants', function (Blueprint $table) {
            $table->index('status');
            $table->index('is_active');
            $table->index('city');
        });

        // MEDIA
        Schema::table('media', function (Blueprint $table) {
            $table->index('is_primary');
            $table->index('sort_order');
        });

        // PRODUCT_TYPES
        Schema::table('product_types', function (Blueprint $table) {
            $table->index('sort_order');
        });

        // PRODUCTS
        Schema::table('products', function (Blueprint $table) {
            $table->index('price');
            $table->index('stock_quantity');
            $table->index('is_customizable');
            // is_active déjà indexé via composite (product_type_id, is_active) et (merchant_id, is_active)
        });

        // EVENT_PACKAGES
        Schema::table('event_packages', function (Blueprint $table) {
            $table->index('base_price');
            $table->index('is_active');
            $table->index('min_quantity');
        });

        // STOCK_MOVEMENTS
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->index('type');
            $table->index('created_at');
        });

        // STOCK_ALERTS
        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('last_triggered_at');
        });

        // CARTS
        Schema::table('carts', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('merchant_id');
        });

        // CART_ITEMS
        Schema::table('cart_items', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('event_package_id');
        });

        // ORDERS
        Schema::table('orders', function (Blueprint $table) {
            $table->index('status');
            $table->index('payment_status');
            $table->index('delivery_type');
            $table->index('created_at');
            $table->index('delivery_slot_id');
            $table->index('delivery_zone_id');
        });

        // ORDER_ITEMS
        Schema::table('order_items', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('event_package_id');
        });

        // ORDER_STATUS_HISTORIES
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->index('status');
            $table->index('created_at');
        });

        // CUSTOM_ORDERS
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('event_type');
        });

        // CUSTOM_ORDER_ITEMS
        Schema::table('custom_order_items', function (Blueprint $table) {
            $table->index('product_id');
            $table->index('event_package_id');
        });

        // PAYMENTS
        Schema::table('payments', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('custom_order_id');
            $table->index('user_id');
            $table->index('provider');
            $table->index('status');
            $table->index('paid_at');
        });

        // REFUNDS
        Schema::table('refunds', function (Blueprint $table) {
            $table->index('status');
            $table->index('refunded_at');
        });

        // INVOICES
        Schema::table('invoices', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('custom_order_id');
            $table->index('issued_at');
        });

        // COUPONS
        Schema::table('coupons', function (Blueprint $table) {
            $table->index('is_active');
            $table->index('starts_at');
            $table->index('expires_at');
        });

        // COUPON_USAGES
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->index('user_id');
        });

        // DELIVERY_ZONES
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->index('is_active');
        });

        // DELIVERY_SLOTS
        Schema::table('delivery_slots', function (Blueprint $table) {
            $table->index('delivery_zone_id');
        });

        // DELIVERY_ASSIGNMENTS
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->index('order_id');
            $table->index('driver_id');
            $table->index('status');
            $table->index('delivery_slot_id');
        });

        // REVIEWS
        Schema::table('reviews', function (Blueprint $table) {
            $table->index('user_id');
            $table->index('rating');
            $table->index('is_approved');
        });

        // CONTACT_MESSAGES
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->index('is_read');
            $table->index('created_at');
        });

        // NEWSLETTER_SUBSCRIBERS
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->index('confirmed_at');
        });

        // AUDIT_LOGS
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->index('event');
            $table->index('created_at');
        });

        // NOTIFICATIONS
        Schema::table('notifications', function (Blueprint $table) {
            $table->index('read_at');
        });
    }

    public function down(): void
    {
        // Suppression des index ajoutés
        Schema::table('users', function (Blueprint $table) {
            $table->dropIndex(['role']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['phone']);
        });
        Schema::table('addresses', function (Blueprint $table) {
            $table->dropIndex(['postal_code']);
            $table->dropIndex(['city']);
            $table->dropIndex(['type']);
        });
        Schema::table('merchants', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['city']);
        });
        Schema::table('media', function (Blueprint $table) {
            $table->dropIndex(['is_primary']);
            $table->dropIndex(['sort_order']);
        });
        Schema::table('product_types', function (Blueprint $table) {
            $table->dropIndex(['sort_order']);
        });
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['price']);
            $table->dropIndex(['stock_quantity']);
            $table->dropIndex(['is_customizable']);
        });
        Schema::table('event_packages', function (Blueprint $table) {
            $table->dropIndex(['base_price']);
            $table->dropIndex(['is_active']);
            $table->dropIndex(['min_quantity']);
        });
        Schema::table('stock_movements', function (Blueprint $table) {
            $table->dropIndex(['type']);
            $table->dropIndex(['created_at']);
        });
        Schema::table('stock_alerts', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['last_triggered_at']);
        });
        Schema::table('carts', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['merchant_id']);
        });
        Schema::table('cart_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['event_package_id']);
        });
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['payment_status']);
            $table->dropIndex(['delivery_type']);
            $table->dropIndex(['created_at']);
            $table->dropIndex(['delivery_slot_id']);
            $table->dropIndex(['delivery_zone_id']);
        });
        Schema::table('order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['event_package_id']);
        });
        Schema::table('order_status_histories', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['created_at']);
        });
        Schema::table('custom_orders', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['event_type']);
        });
        Schema::table('custom_order_items', function (Blueprint $table) {
            $table->dropIndex(['product_id']);
            $table->dropIndex(['event_package_id']);
        });
        Schema::table('payments', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['custom_order_id']);
            $table->dropIndex(['user_id']);
            $table->dropIndex(['provider']);
            $table->dropIndex(['status']);
            $table->dropIndex(['paid_at']);
        });
        Schema::table('refunds', function (Blueprint $table) {
            $table->dropIndex(['status']);
            $table->dropIndex(['refunded_at']);
        });
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['custom_order_id']);
            $table->dropIndex(['issued_at']);
        });
        Schema::table('coupons', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
            $table->dropIndex(['starts_at']);
            $table->dropIndex(['expires_at']);
        });
        Schema::table('coupon_usages', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
        });
        Schema::table('delivery_zones', function (Blueprint $table) {
            $table->dropIndex(['is_active']);
        });
        Schema::table('delivery_slots', function (Blueprint $table) {
            $table->dropIndex(['delivery_zone_id']);
        });
        Schema::table('delivery_assignments', function (Blueprint $table) {
            $table->dropIndex(['order_id']);
            $table->dropIndex(['driver_id']);
            $table->dropIndex(['status']);
            $table->dropIndex(['delivery_slot_id']);
        });
        Schema::table('reviews', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['rating']);
            $table->dropIndex(['is_approved']);
        });
        Schema::table('contact_messages', function (Blueprint $table) {
            $table->dropIndex(['is_read']);
            $table->dropIndex(['created_at']);
        });
        Schema::table('newsletter_subscribers', function (Blueprint $table) {
            $table->dropIndex(['confirmed_at']);
        });
        Schema::table('audit_logs', function (Blueprint $table) {
            $table->dropIndex(['user_id']);
            $table->dropIndex(['event']);
            $table->dropIndex(['created_at']);
        });
        Schema::table('notifications', function (Blueprint $table) {
            $table->dropIndex(['read_at']);
        });
    }
};