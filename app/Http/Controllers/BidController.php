<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Events\BidPlaced;

class BidController extends Controller
{
    public function store(Request $request, Lot $lot)
    {
       if ($lot->ends_at && $lot->ends_at->isPast()) {
           return response()->json(['error'=>'Lot has ended.'], 403);
       }

       $request->validate([
           'amount'=>'required|numeric|gt:' . $lot->current_price,
       ]);

       $bid = DB::transaction(function () use ($request, $lot) {
           $lot->current_price = $request->amount;

           if ($lot->ends_at && now()->diffInSeconds($lot->ends_at, false) < 30) {
               $lot->ends_at = now()-addMinute();
           }

           $lot->save();

           return $lot->bids()->create([
               'amount' => $request->amount,
               'user_id' => auth()->id() ?? 1,
           ]);
       });

       $bid->load('user');

       broadcast(new BidPlaced($bid, $lot));

       return response()->json(['success'=>'Bid placed.'], 200);


    }
}
