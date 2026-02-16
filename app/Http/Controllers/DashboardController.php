<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Support\Facades\Auth;

class DashboardController extends Controller
{
    public function index()
    {
        $lots = Lot::whereHas('bids', function ($query) {
            $query->where('user_id', Auth::id());
        })
            ->with(['bids' => function ($query) {
                $query->latest();
            }, 'winner'])
            ->latest()
            ->get();

        $lots->transform(function ($lot) {
            $lastBid = $lot->bids->first();

            if ($lot->status === 'closed') {
                $lot->user_status = ($lot->winner_id === Auth::id()) ? 'won' : 'lost';
            } else {
                if ($lastBid && $lastBid->user_id === Auth::id()) {
                    $lot->user_status = 'leading';
                } else {
                    $lot->user_status = 'outbid';
                }
            }

            return $lot;
        });

        return view('dashboard', compact('lots'));
    }
}
