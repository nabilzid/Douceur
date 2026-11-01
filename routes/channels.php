<?php

use Illuminate\Support\Facades\Broadcast;

// Canal par défaut (déjà présent)
Broadcast::channel('App.Models.User.{id}', function ($user, $id) {
    return (int) $user->id === (int) $id;
});

// ✅ Canal privé pour les notifications admin/merchant
Broadcast::channel('admin.notifications', function ($user) {
    return in_array($user->role, ['admin', 'merchant']);
});