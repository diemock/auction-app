<?php

namespace App\Http\Controllers;

use App\Models\Lot;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LotController extends Controller
{
    public function index()
    {
        $lots = Lot::latest()->get();
        return view('lots.index', compact('lots'));
    }

    public function show(Lot $lot)
    {
        return view('lots.show', compact('lot'));
    }

    public function create()
    {
        return view('lots.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'current_price' => 'required|numeric|min:1',
            'end_time' => 'required|date|after:now',
        ]);

        Lot::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'starting_price' => $validated['current_price'],
            'current_price' => $validated['current_price'],
            'ends_at' => $validated['end_time'],
            'status' => 'active',
            'user_id' => Auth::id(),
        ]);

        return redirect()->route('lots.index')->with('success', 'Лот успешно создан!');
    }
}
