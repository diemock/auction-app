<?php

namespace App\Events;

use App\Models\Bid;
use App\Models\Lot;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class BidPlaced implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $bid;
    public $lot;

    public function __construct(Bid $bid, Lot $lot){
        $this->bid = $bid;
        $this->lot = $lot;
    }

    public function broadcastOn(): array
    {
        return [
            new Channel('lots.' . $this->lot->id),
        ];
    }

    public function broadcastWith(): array{
        return [
            'amount'=>$this->bid->amount,
            'user_name'=>$this->bid->user->name,
            'created_at' => $this->bid->created_at->format('H:i:s'),
            'end_time'=>$this->lot->end_time?$this->lot->end_time->timestamp*1000:null,
            'current_price'=>$this->bid->lot->current_price,
        ];
    }
    public function broadcastAs(): string
    {
        return 'BidPlaced';
    }
}
