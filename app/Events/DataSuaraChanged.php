<?php

namespace App\Events;

use App\Models\Tps;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DataSuaraChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $tpsId;
    public string $event;
    public string $desaId;
    public string $kecamatanId;

    public function __construct(Tps $tps, string $event)
    {
        $this->tpsId = $tps->id;
        $this->event = $event;
        $this->desaId = $tps->desa_id;
        $this->kecamatanId = $tps->desa?->kecamatan_id ?? '';
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("desa.tps.{$this->desaId}"),
            new PrivateChannel('admin.tps'),
        ];

        if ($this->kecamatanId) {
            $channels[] = new PrivateChannel("kecamatan.tps.{$this->kecamatanId}");
        }

        return $channels;
    }
}
