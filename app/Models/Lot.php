<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Lot extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'starting_price',
        'current_price',
        'ends_at',
        'status',
        'user_id'
    ];

    protected $casts = [
        'ends_at' => 'datetime',
    ];

    public function bids()
    {
        return $this->hasMany(Bid::class);
    }


    public function winner()
    {
        return $this->belongsTo(\App\Models\User::class, 'winner_id');
    }

}
