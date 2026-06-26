<?php

namespace App\Events;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActivityLogged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    /**
     * The activity data payload to broadcast.
     *
     * @var array<string, mixed>
     */
    public array $activity;

    /**
     * Create a new event instance.
     */
    public function __construct(Activity $activity)
    {
        // Eager load causer details
        $activity->load('causer');
        $causer = $activity->causer;

        $this->activity = [
            'id' => $activity->id,
            'log_name' => $activity->log_name,
            'description' => $activity->description, // Decrypted because of the model cast
            'event' => $activity->event,
            'causer' => $causer instanceof User ? [
                'name' => $causer->name,
                'email' => $causer->email,
                'role' => $causer->role,
            ] : null,
            'created_at' => $activity->created_at?->timezone('Asia/Jakarta')->format('d/m/Y H:i:s'),
        ];
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.activity-logs'),
        ];
    }
}
