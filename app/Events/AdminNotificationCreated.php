<?php

namespace App\Events;

use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class AdminNotificationCreated implements ShouldBroadcast
{
    use Dispatchable, SerializesModels;

    /**
     * @param array $payload {
     *   type: string,    // 'new_order' | 'new_custom_order' | 'custom_order_responded' | 'low_stock'
     *   title: string,
     *   message: string,
     *   url: string,
     *   icon: string,
     * }
     */
    public function __construct(public array $payload) {}

    public function broadcastOn(): array
    {
        return [new PrivateChannel('admin.notifications')];
    }

    public function broadcastAs(): string
    {
        return 'notification.created';
    }
}