<?php

namespace App\Events;

use App\Models\Pemilih;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class PemilihChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public string $pemilihId;

    public string $event;

    public string $kecamatanId;

    public string $desaId;

    /**
     * Create a new event instance.
     */
    public function __construct(Pemilih $pemilih, string $event)
    {
        $this->pemilihId = $pemilih->id;
        $this->event = $event;
        $this->kecamatanId = $pemilih->kecamatan_id;
        $this->desaId = $pemilih->desa_id;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return array<int, Channel>
     */
    public function broadcastOn(): array
    {
        return [
            new PrivateChannel('admin.pemilih'),
            new PrivateChannel("kecamatan.pemilih.{$this->kecamatanId}"),
            new PrivateChannel("desa.pemilih.{$this->desaId}"),
        ];
    }
}
