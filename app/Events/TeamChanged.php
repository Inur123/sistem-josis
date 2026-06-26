<?php

namespace App\Events;

use App\Models\AnggotaTim;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TeamChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $memberId;

    public string $event;

    public ?string $kecamatanId;

    public ?string $desaId;

    public ?string $oldKecamatanId;

    public ?string $oldDesaId;

    /**
     * Create a new event instance.
     */
    public function __construct(
        AnggotaTim $member,
        string $event,
        ?string $oldKecamatanId = null,
        ?string $oldDesaId = null,
    ) {
        $this->memberId = $member->id;
        $this->event = $event;
        $this->kecamatanId = $member->kecamatan_id;
        $this->desaId = $member->desa_id;
        $this->oldKecamatanId = $oldKecamatanId;
        $this->oldDesaId = $oldDesaId;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel('admin.team'),
        ];

        foreach (array_filter(array_unique([$this->kecamatanId, $this->oldKecamatanId])) as $kecamatanId) {
            $channels[] = new PrivateChannel("kecamatan.team.{$kecamatanId}");
        }

        foreach (array_filter(array_unique([$this->desaId, $this->oldDesaId])) as $desaId) {
            $channels[] = new PrivateChannel("desa.team.{$desaId}");
        }

        return $channels;
    }
}
