<?php

namespace App\Console\Commands;

use App\Models\Lot;
use App\Events\LotWon;
use Illuminate\Console\Command;

class CloseExpiredLots extends Command
{
    protected $signature = 'lots:close-expired';
    protected $description = 'Закрывает лоты, время которых истекло';

    public function handle()
    {
        $expiredLots = Lot::where('status', 'active')
            ->where('end_time', '<=', now())
            ->get();

        foreach ($expiredLots as $lot) {
            $lastBid = $lot->bids()->latest()->first();

            $lot->update([
                'status' => 'closed',
                'winner_id' => $lastBid ? $lastBid->user_id : null,
            ]);

            broadcast(new LotWon($lot, $lastBid ? $lastBid->user : null));

            $this->info("Лот {$lot->id} закрыт. Победитель: " . ($lastBid ? $lastBid->user->name : 'Нет'));
        }
    }
}
