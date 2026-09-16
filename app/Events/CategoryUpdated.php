<?php

namespace App\Events;

use App\Models\Category;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class CategoryUpdated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $category;
    public string $action;

    public function __construct(Category $category, string $action = 'updated')
    {
        $this->category = $category;
        $this->action = $action;
    }

    /**
     * القناة العامة الخاصة بجميع الأقسام
     */
    public function broadcastOn(): array
    {
        return [
            new Channel('categories'),
        ];
    }

    /**
     * اسم الحدث الذي يستمع له React
     */
    public function broadcastAs(): string
    {
        return 'CategoryChanged';
    }
}