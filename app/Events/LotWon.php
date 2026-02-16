<?php

namespace App\Events;

use App\Models\Lot;
use App\Models\User;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow; // Важно!
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LotWon implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $lot;
    public $winner;

    public function __construct(Lot $lot, ?User $winner)
    {
        $this->lot = $lot;
        $this->winner = $winner;
    }

    public function broadcastOn(): array
    {
        return [new Channel('lots.' . $this->lot->id)];
    }

    public function broadcastAs(): string
    {
        return 'LotWon';
    }

    public function broadcastWith(): array
    {
        return [
            'lot_id' => $this->lot->id,
            'winner_name' => $this->winner ? $this->winner->name : 'Никто',
            'final_price' => $this->lot->current_price,
        ];
    }
}
